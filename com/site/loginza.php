<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$version = new JVersion();
if ($version->RELEASE != '1.5') { //для 1.6 и 1.7
    // import joomla controller library
    jimport('joomla.application.component.controller');
    // Get an instance of the controller
    $controller = JController::getInstance('Loginza');
}
else { //для 1.5
    // Require the base controller
    require_once (JPATH_COMPONENT . DS . 'controller.php');
    $database = &JFactory::getDBO();
    //Create the controller
    $controller = new LoginzaController($database);
}

<<<<<<< HEAD
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
=======
//Входим!
$controller->auth();
>>>>>>> abb3e6bc69f546afdfe8705196df6f141fea5cf7
?>