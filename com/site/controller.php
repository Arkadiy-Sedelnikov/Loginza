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
    var $providers      = null;
    var $user_id        = null;
    var $user_pass      = null;
    var $confirm_email  = null;
    var $email          = null;
    var $username       = null;

    function __construct(&$db)
	{
        $view = JRequest::getVar('view', '');
        $task = JRequest::getVar('task', '');

        $this->conf = $this->conf();

        if($view != 'comparison_user' && $task != 'join_email'){
            $this->json = $this->request();
        }
        
        $this->providers = $this->getProviders();
        $this->nameProvider = $this->getNameProvider();
        $this->passwd = $this->generatePasswd();
        $this->id = $this->getId();
        parent::__construct();
	}

    /*
     * Принимаем ответ от Логинзы
     */
    private function request()
    {
        $json = array();
        $token = $_POST['token'];
        $sieg = md5($token . $this->conf->secretkey);
        $file = "http://loginza.ru/api/authinfo?token=" . $token;

        if(!$this->conf->debug){
            $file .= "&id=" . $this->conf->widgetid . "&sig=" . $sieg;
        }

        $file = file($file);

        if ($file){
            $json = json_decode($file[0], true);
        }
        return $json;
    }

    /*
     * Авторизация
     */
    public function auth()
    {
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();

        $query  = 'SELECT `lu`.*, `u`.`id` AS uid, `u`.`username` ';
        $query .= 'FROM `#__loginza_users` AS lu ';
        $query .= 'LEFT JOIN `#__users` AS u ON `u`.`id` = `lu`.`user_id` ';
        $query .= 'WHERE `lu`.`loginza_id` = ' . $db->quote($this->id . ':' . $this->nameProvider) . ' LIMIT 1';
        $db->setQuery($query);
        $user = $db->loadObject();
        if(is_object($user) && $user->user_id == $user->uid){
            //если все в порядке и записи о пользователе есть в обеих таблицах
            $this->user_id = $user->user_id;
            $juser = JUser::getInstance($this->user_id);
            $this->email = $juser->email;
            $this->confirm_email = $user->confirmed;
            $this->user_pass = $user->loginza_pass;
            $this->username = $user->username;
        }
        else if(is_object($user) && $user->user_id != $user->uid){
            //если удалили пользователя с идом догинзы из таблицы пользователей, то удаляем его строки из таблицы логинзы
            $query  = 'DELETE FROM `#__loginza_users` WHERE  `user_id` = ' . $user->user_id;
            $db->setQuery($query);
            $db->query();
            //Регистрируемся
            $this->register();
        }
        else {
            //Регистрируемся
            $this->register();
        }
        //Авторизуемся
        $this->login();
    }


    /*
     * Авторизация пользователя
     */
    private function login()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();

        $return = JRequest::getVar('return', '');
        $return = (!empty($return)) ? base64_decode(JRequest::getVar('return', '')) : JURI::base();

        if($this->user_pass != $this->passwd){
            $app->redirect($return, JText::_('COM_LOGINZA_PASS_ERR'));
        }

        if(!$this->confirm_email && $this->conf->confirm_email){
            $app->redirect(JRoute::_('index.php?option=com_loginza&view=comparison_user&email='.$this->email.'&id='.$this->user_id,false));
        }

        $user_id = $this->user_id;
        if ($user_id>0) {
            $user = JUser::getInstance($user_id);
            $session = &JFactory::getSession();
            $user->guest = 0;
            $user->aid = 1;
            $session->set('user', $user);
            $app->checkSession();

            // Update the user related fields for the Joomla sessions table.
          	$db->setQuery(
          		'UPDATE `#__session`' .
          		' SET `guest` = '.$db->quote($user->guest).',' .
          		'	`username` = '.$db->quote($this->username).',' .
          		'	`userid` = '.(int) $user_id .
          		' WHERE `session_id` = '.$db->quote($session->getId())
          	);
          	$db->query();

            $user->setLastVisit();
        }
        $app->redirect($return);
    }

    /*
     * Регистрация пользователя
     */
    private function register()
    {
        jimport('joomla.mail.helper');
        $json = $this->json;
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $user = JFactory::getUser(0);
        $usersParams = JComponentHelper::getParams('com_users');
        $params = JComponentHelper::getParams('com_loginza');
        $data = array();

        $groupId = $usersParams->get('new_usertype', 2);
        $useractivation = $usersParams->get('useractivation');
        $comBuilder = $params->get('com_builder', 0);

        if(isset($json['error_type']) && $json['error_type'] != null){
            $app->redirect('/', $json['error_message'], 'error');
        }

        //e-mail
        if (empty($json['email'])) {
            $email = $this->id . $this->providers[$this->nameProvider]['email'];
        }
        else{
            $email = $json['email'];
        }

        $this->email = $email;
        $isOldUser = 0;
        $user_from_mail = null;

        if (JMailHelper::isEmailAddress($email)) {
            $query = 'SELECT * FROM `#__users`';
            $query .= ' WHERE `email` = ' . $db->Quote($email) . ' LIMIT 1';
            $db->setQuery($query);
            $user_from_mail = $db->loadObjectList();

            if (count($user_from_mail)>0) {
                $isOldUser = 1;
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

        $this->username = $data['username'] = $nickname;

        $version = new JVersion();
        if ($version->RELEASE == '1.5') { //для 1.5
            $acl =& JFactory::getACL();
            $usertype = $usersParams->get( 'new_usertype' );
			if(!$usertype){
				$usertype = 'Registered';
			}
            //$data['usertype'] = $usertype;
            $data['gid'] = $acl->get_group_id( '', $usertype, 'ARO' );
        }


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

        if(!$isOldUser){
            if (!$user->bind($data)) {
                JError::raiseWarning('', JText::_($user->getError()));
                return false;
            }

            if (!$user->save()) {
                JError::raiseWarning('', JText::_($user->getError()));
                return false;
            }
        }

        $user_id = $this->user_id = (!$isOldUser) ? $user->id : $user_from_mail[0]->id;
        $this->confirm_email = ($isOldUser && $this->conf->confirm_email) ? 0 : 1;
        $this->user_pass = $this->passwd;

        $loginzaData = array();
        $loginzaData['id'] = null;
        $loginzaData['user_id'] = $user_id;
        $loginzaData['loginza_id'] = $this->id . ':' . $this->nameProvider;
        $loginzaData['loginza_pass'] = $this->passwd;
        $loginzaData['confirmed'] = $this->confirm_email;

        JTable::addIncludePath(JPATH_COMPONENT . '/tables');
        $loginzaUser = &JTable::getInstance('loginza_users', 'loginzaTable');

        if (!$loginzaUser->bind($loginzaData)) {
            JError::raiseWarning('', JText::_($loginzaUser->getError()));
            return false;
        }
         if (!$loginzaUser->store()) {
            JError::raiseWarning('', JText::_($loginzaUser->getError()));
            return false;
        }

        //заплатка для ком билдера
        if($comBuilder){
            if((int)$this->user_id > 0){
                $query = "INSERT INTO `#__comprofiler` (`id`, `user_id`) VALUES ('$user_id', '$user_id')";
                $db->setQuery($query);
                $db->query();
            }
        }
        return true;
    }

    public function join_email(){
        
        JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

        $app = JFactory::getApplication();

        // Populate the data array:
        $data = array();
        $user_id = JRequest::getInt('user_id', 0);
        $data['return'] = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
        $data['username'] = JRequest::getVar('username', '', 'method', 'username');
        $data['password'] = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);

        // Set the return URL if empty.
        if (empty($data['return'])) {
        	$data['return'] = 'index.php?option=com_users&view=profile';
        }

        // Get the log in options.
        $options = array();
        $options['remember'] = JRequest::getBool('remember', false);
        $options['return'] = $data['return'];

        // Get the log in credentials.
        $credentials = array();
        $credentials['username'] = $data['username'];
        $credentials['password'] = $data['password'];

        // Perform the log in.
        $error = $app->login($credentials, $options);

        // Check if the log in succeeded.
        if (!JError::isError($error)) {
        	$app->setUserState('users.login.form.data', array());

            JTable::addIncludePath(JPATH_COMPONENT . '/tables');
            $loginzaUser = &JTable::getInstance('loginza_users', 'loginzaTable');
            $loginzaUser->confirm_email($user_id);

        	$app->redirect(JRoute::_($data['return'], false));
        } else {
        	$data['remember'] = (int)$options['remember'];
        	$app->setUserState('users.login.form.data', $data);
        	$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
        }
    }
    
    /*
     * Генерируем пароль
     */
    private function generatePasswd()
    {
        return md5($this->json['identity'] . $this->conf->secretkey);
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
        if(empty($json['identity'])){
            return false;
        }
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
    public function getProviders()
    {
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
                                    'provider' => 'http://vk.com/',
                                    'string' => '/^(http:\/\/vk.com\/)?([^\/]+)/',
                                    'email' => '@vk.com'
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
    private function conf()
    {
        $conf = null;
        $params = &JComponentHelper::getParams('com_loginza');
        $conf->secretkey = $params->get("secretkey", '');
        $conf->widgetid = $params->get("widgetid", '');
        $conf->debug = $params->get("debug", 0);
        $conf->activation = $params->get("activation", 'enable');
        $conf->confirm_email = $params->get("confirm_email", 0);
        return $conf;
    }
}