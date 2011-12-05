<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.application.application');
jimport('joomla.user.helper');
jimport('joomla.user.user');

class LoginzaController extends JController
{
    var $json           = null;
    var $conf           = null;
    var $nameProvider   = null;
    var $passwd         = null;
    var $id             = null;
    var $pluginEnable   = null;
    var $providers      = null;

    function __construct(&$db)
	{
        $this->conf = $this->conf();
        $this->providers = $this->getProviders();
		$this->json = $this->request();
        $this->nameProvider = $this->getNameProvider();
        $this->passwd = $this->generatePasswd();
        $this->id = $this->getId();
        $this->pluginEnable = $this->pluginEnable();
        parent::__construct();
	}

    /*
     * Принимаем ответ от Логинзы
     */
    private function request()
    {
        $json = array();
        $app = JFactory::getApplication();
        $token = $_POST['token'];
        $sieg = md5($token . $this->conf->secretkey);
        $file = "http://loginza.ru/api/authinfo?token=" . $token;

        if(!$this->conf->debug){
            $file .= "&id=" . $this->conf->widgetid . "&sig=" . $sieg;
        }

        $file = file($file);

        if ($file){
            $json = json_decode($file[0], true);
            if(isset($json['error_type']) && $json['error_type'] != null){
                $app->redirect('/', $json['error_message'], 'error');
            }
        }
        return $json;
    }

    /*
     * Авторизация
     */
    public function auth()
    {
        $db = JFactory::getDBO();

        $query = 'SELECT * FROM `#__users`';

        if($this->pluginEnable){
            $query .= ' WHERE `loginza_id` = \'' . $this->id . ':' . $this->nameProvider . '\' LIMIT 1';
        }
        else {
            $query .= ' WHERE `username`   = \'' . $this->id . ':' . $this->nameProvider . '\' LIMIT 1';
        }
        $db->setQuery($query);
        $db->query();
        if ($db->getNumRows()) {
            //Авторизуемся
            $this->login();
        } else {
            //Регистрируемся
            $this->register();
            $this->login();
        }
    }


    /*
     * Авторизация пользователя
     */
    private function login()
    {
        $return = JRequest::getVar('return', '');
        $return = (!empty($return)) ? base64_decode(JRequest::getVar('return', '')) : JURI::base();

        $options = array();
        $options['remember'] = JRequest::getBool('remember', false);
        $options['return'] = $return;

        $credentials = array();
        $credentials['username'] = $this->id . ':' . $this->nameProvider;
        $credentials['password'] = $this->passwd;

        $app = JFactory::getApplication();
        $app->login($credentials, $options);
        $app->redirect($return);
    }

    /*
     * Регистрация пользователя
     */
    private function register()
    {
        $json = $this->json;
        $usersParams = JComponentHelper::getParams('com_users');
        $params = JComponentHelper::getParams('com_loginza');
        $user = JFactory::getUser(0);
        $data = array();
        $db = JFactory::getDBO();

        $groupId = $usersParams->get('new_usertype', 2);
        $useractivation = $usersParams->get('useractivation');

        $comBuilder = $params->get('com_builder', 0);

        //e-mail
        if (empty($json['email'])) {
            $email = $this->id . $this->providers[$this->nameProvider]['email'];
        }
        else{
            $email = $json['email'];
        }

        jimport('joomla.mail.helper');

        if (JMailHelper::isEmailAddress($email)) {
            $query = 'SELECT * FROM `#__users`';
            $query .= ' WHERE `email` = ' . $db->Quote($email) . ' LIMIT 1';
            $db->setQuery($query);
            $db->query();
            if ($db->getNumRows()) {
                //Replace email with a fake name to avoid joomla user conflict
                $email = uniqid() . '_.' . $email;
            }
        }
        //name
        $name = @$json['name']['full_name'];
        if ($name == null) {
            $name = @$json['name']['first_name'] . ' ' . @$json['name']['last_name'];
        }

        //name2
        if ($name == null or $name == ' ') {
            $name = $this->id;
        }

        $data['groups'] = array($groupId);
        $data['name'] = $name;
        $version = new JVersion();
        if ($version->RELEASE != '1.5') { //для 1.6 и 1.7
            if($this->pluginEnable){
                if(!empty($json['nickname'])){
                    $nickname = $json['nickname'];
                }
                elseif(!empty($json['name']['first_name'])){
                    $nickname = $json['name']['first_name'];
                }
                elseif(!empty($json['name']['full_name'])){
                    $nickname = $json['name']['full_name'];
                }
                else{
                    $nickname = $this->id . ':' . $this->nameProvider;
                }

		        $query = 'SELECT * FROM `#__users`';
                $query .= ' WHERE `username` = ' . $db->Quote($nickname) . ' LIMIT 1';
                $db->setQuery((string) $query);
                $db->query();
                if ($db->getNumRows()) {
                    //Replace email with a fake name to avoid joomla user conflict
                    $nickname = $nickname.uniqid();
                }

                $data['username'] = $nickname;
            }
            else{
                $data['username'] = $this->id . ':' . $this->nameProvider;
            }
        }
        else { //для 1.5
            $data['username'] = $this->id . ':' . $this->nameProvider;

            $acl =& JFactory::getACL();

            $usertype = $usersParams->get( 'new_usertype' );
			if(!$usertype){
				$usertype = 'Registered';
			}
            //$data['usertype'] = $usertype;
            $data['gid'] = $acl->get_group_id( '', $usertype, 'ARO' );
        }


        $data['loginza_id'] = $this->id . ':' . $this->nameProvider;
        $data['email'] = $data['email1'] = $data['email2'] = $email;
        $data['password'] = $this->passwd;
        $data['password2'] = $this->passwd;
        if($this->conf->activation == 'joomla'){
             if ($useractivation == 1 || $useractivation == 2) {
                $data['block'] = 1;
                $data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
            } else {
                $data['block'] = 0;
            }
        }
        else{
            $data['block'] = 0;
        }

//var_dump($json); var_dump($data); die;
        if (!$user->bind($data)) {
            JError::raiseWarning('', JText::_($user->getError()));
            return false;
        }

        if (!$user->save()) {
            JError::raiseWarning('', JText::_($user->getError()));
            return false;
        }
        //заплатка для ком билдера
        if($comBuilder){
            $query = 'SELECT `id` FROM `#__users`';
            if($this->pluginEnable){
                $query .= ' WHERE `loginza_id` = \'' . $this->id . ':' . $this->nameProvider . '\' LIMIT 1';
            }
            else {
                $query .= ' WHERE `username`   = \'' . $this->id . ':' . $this->nameProvider . '\' LIMIT 1';
            }
            $db->setQuery($query);
            $id = $db->LoadResult();
            if((int)$id > 0){
                $query = "INSERT INTO `#__comprofiler` (`id`, `user_id`) VALUES ('$id', '$id')";
                $db->setQuery($query);
                $db->query();
            }
        }
    }
    
    /*
     * Генерируем пароль
     */
    private function generatePasswd()
    {
        return $this->json['identity'] . $this->conf->secretkey;
    }

    /*
     * вычисляем имя провайдера
     */
    private function getNameProvider()
    {
        $json = $this->json;
        foreach($this->providers as $provider => $data){
            if (strpos($json['provider'], $data['provider']) !== false) {
                return $provider;
            }
        }
        return '';
    }

    /*
     * Вычисляем идентификатор пользователя
     */
    private function getId()
    {
        $nameProvider = $this->nameProvider;
        $json = $this->json;
        preg_match($this->providers[$nameProvider]['string'], $json['identity'], $matches);
        $id = JString::strtolower($matches[2]);

        return $id;
    }
    
    /*
     * Массив провайдеров
     * содержит для каждого провайдера
     * значение поля 'provider', возвращаемое сервером,
     * регулярное выражение для вычисления ида пользователя,
     * email для подстановки в случае его отсутствия в ответе.
     */
    public function getProviders(){
        $providers = array(
            'google'        => array(
                                    'provider' => 'www.google.com',
                                    'string' => '/^(https:\/\/www.google.com\/accounts\/o8\/id\?id=)?([^\/]+)/',
                                    'email' => '@google.com'
                                ),
            'yandex'        => array(
                                    'provider' => 'http://openid.yandex.ru/server/',
                                    'string' => '/^(http:\/\/openid.yandex.ru\/)?([^\/]+)/',
                                    'email' => '@yandex.ru'
                                ),
            'mailru'        => array(
                                    'provider' => 'http://mail.ru/',
                                    'string' => '/^(http:\/\/my.mail.ru\/mail\/)?([^\/]+)/',
                                    'email' => '@mail.ru'
                                ),
            'vkontakte'     => array(
                                    'provider' => 'http://vkontakte.ru/',
                                    'string' => '/^(http:\/\/vkontakte.ru\/)?([^\/]+)/',
                                    'email' => '@vkontakte.ru'
                                ),
            'facebook'      => array(
                                    'provider' => 'http://www.facebook.com/',
                                    'string' => '/^(http:\/\/www.facebook.com\/profile\.php\?id=)?([^\/]+)/',
                                    'email' => '@facebook.com'
                                ),
            'loginza'       => array(
                                    'provider' => 'https://loginza.ru/server/',
                                    'string' => '/^(http:\/\/)?([^\/]+)?(\.loginza\.ru)/',
                                    'email' => '@loginza.ru'
                                ),
            'myopenid'      => array(
                                    'provider' => 'http://www.myopenid.com/server',
                                    'string' => '/^(http:\/\/?([^\/]+).myopenid.com)/',
                                    'email' => '@myopenid.com'
                                ),
            'webmoney'      => array(
                                    'provider' => 'wmkeeper.com',
                                    'string' => '/^(https:\/\/)?([^\/]+)?(.wmkeeper.com)/',
                                    'email' => '@webmoney.com'
                                ),
            'rambler'       => array(
                                    'provider' => 'http://id.rambler.ru/script/openid.cgi',
                                    'string' => '/^(https:\/\/id\.rambler\.ru\/users\/)?([^\/]+)/',
                                    'email' => '@rambler.ru'
                                ),
            'flickr'        => array(
                                    'provider' => 'https://open.login.yahooapis.com/openid/op/auth',
                                    'string' => '/^(https:\/\/me\.yahoo\.com\/a\/)?([^\/]+)/',
                                    'email' => '@yahoo.com'
                                ),
            'twitter'       => array(
                                    'provider' => 'http://twitter.com/',
                                    'string' => '/^(http:\/\/twitter.com\/)?([^\/]+)/',
                                    'email' => '@twitter.com'
                                ),
            'linkedin'      => array(
                                    'provider' => 'http://www.linkedin.com/',
                                    'string' => '/^(http:\/\/www.linkedin.com\/profile\/view\?id=)?([^\/]+)/',
                                    'email' => '@linkedin.com'
                                ),
            'odnoklassniki' => array(
                                    'provider' => 'http://odnoklassniki.ru/',
                                    'string' => '/^(http:\/\/odnoklassniki.ru\/profile\/)?([^\/]+)/',
                                    'email' => '@odnoklassniki.ru'
                                ),
            'livejournal'   => array(
                                    'provider' => 'www.livejournal.com',
                                    'string' => '/^(http:\/\/?([^\/]+).livejournal.com)/',
                                    'email' => '@livejournal.com'
                                ),
            'lastfm'        => array(
                                    'provider' => 'http://www.last.fm/',
                                    'string' => '/^(http:\/\/www.last.fm\/user\/)?([^\/]+)/',
                                    'email' => '@last.fm'
                                ),
            'mailruapi'     => array(
                                    'provider' => 'http://openid.mail.ru/login',
                                    'string' => '/^(http:\/\/openid.mail.ru\/mail\/)?([^\/]+)/',
                                    'email' => '@mail.ru'
                                ),
            'steam'         => array(
                                    'provider' => 'https://steamcommunity.com/',
                                    'string' => '/^(http:\/\/steamcommunity.com\/openid\/id\/)?([^\/]+)/',
                                    'email' => '@steamcommunity.com'
                                ),
            'aol'           => array(
                                    'provider' => 'https://api.screenname.aol.com/auth/openidServer',
                                    'string' => '/^(http:\/\/openid.aol.com\/)?([^\/]+)/',
                                    'email' => '@aol.com'
                                ),
//            'openid'        => array(
//                                    'provider' => '',
//                                    'string' => '',
//                                    'email' => '@openid.com'
//                                ),
//            'verisign'      => array(
//                                    'provider' => '',
//                                    'string' => '',
//                                    'email' => '@verisign.com'
//                                )
        );
        return $providers;
    }

    /*
     * Конфигурация компонента
     */
    private function conf(){
        $conf = null;
        $params = &JComponentHelper::getParams('com_loginza');
        $conf->secretkey = $params->get("secretkey", '');
        $conf->widgetid = $params->get("widgetid", '');
        $conf->debug = $params->get("debug", 0);
        $conf->activation = $params->get("activation", 'enable');
        return $conf;
    }

    /*
     * Проверяет установлен и опубликован плагин логинзы
     */
    private function pluginEnable(){
        return JPluginHelper::isEnabled('authentication', 'loginza');
    }
}

?>
