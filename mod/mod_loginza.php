<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// include the helper file
require_once(dirname(__FILE__).DS.'helper.php');

$paramsCom =    JComponentHelper::getParams('com_loginza');
$user =         JFactory::getUser();
$document =     JFactory::getDocument();

$mode =         $params->get("mode", 'html');
$invite =       $params->get("invite");
$key =          $paramsCom->get("secretkey");
$debug =        $paramsCom->get("debug");
$loginForm =   $params->get("login_form", 1);

$type =         modLoginzaHelper::getType();
$return	=       modLoginzaHelper::getReturnURL($params, $type);
$username =     ($params->get('name')) ? $user->get('username') : $user->get('name');

$greeting_template = $params->get('greeting_template');

if (empty($greeting_template)) {
	$greeting_template = JText::_( 'LOGHELLO' ).', %s!';
}

if($type == 'login'){
    $document->addScript('http://loginza.ru/js/widget.js');
}

$document->addStyleSheet(JURI::root() . "/modules/mod_loginza/tmpl/css/style.css");

$loginza_url = 'https://loginza.ru/api/widget?token_url='.urlencode(JRoute::_( JURI::base().'index.php?option=com_loginza&return='.$return, true, $params->get('usesecure')));

$img_url = JURI::base().'modules/mod_loginza/tmpl/img/';

    $providersArray = array(
       'google',
       'yandex',
       'mailru',
       'vkontakte',
       'odnoklassniki',
       'facebook',
       'loginza',
       'twitter',
       'linkedin',
       'livejournal',
       'myopenid',
       'webmoney',
       'rambler',
       'flickr',
       'lastfm',
       'mailruapi',
       'steam',
       'aol',
//       'openid',
//       'verisign'
    );

$providers =     $params->get("providers", array('all'));

$version = new JVersion();
if ($version->RELEASE != '1.5') { //для 1.6 и 1.7
    if($providers[0] == 'all'){
        $providers = $providersArray;
    }
    $formTask = 'user.'.$type;
    $formOpt = 'com_users';
    $formViewReg = 'registration';
    $formPassWord = 'password';
}
else { //для 1.5
    if(!is_array($providers) && ($providers == 'all' || empty($providers))){
        $providers = $providersArray;
    }
    elseif(!is_array($providers)){
        $providers = array($providers);
    }
    elseif(is_array($providers) && $providers[0] == 'all'){
        $providers = $providersArray;
    }
    $formTask = $type;
    $formOpt = 'com_user';
    $formViewReg = 'register';
    $formPassWord = 'passwd';
}


$providersSet = '&providers_set=' . implode(",", $providers);

if(empty($key) && $debug != 1)
    $layout = 'empty_key';
else if($type != 'login')
    $layout = 'logout';
else
    $layout = $mode;

// include the template for display
require JModuleHelper::getLayoutPath('mod_loginza', $layout);
?>
