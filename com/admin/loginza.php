<?php
/**
 * @version        1.0.4 from Arkadiy Sedelnikov
 * @copyright    Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

$version = new JVersion();
if ($version->RELEASE != '1.5') { //для 1.6 и 1.7
    // Access check.
    if (!JFactory::getUser()->authorise('core.manage', 'com_loginza')) {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }

    // Include dependancies
    jimport('joomla.application.component.controller');

    // Execute the task.
    $controller = JController::getInstance('Loginza');
    $controller->execute(JRequest::getCmd('task'));
    $controller->redirect();
}
else { //для 1.5
    // Require the base controller
    require_once (JPATH_COMPONENT . DS . 'controller.php');

    //Create the controller
    $controller = new LoginzaController();
    // Perform the Request task
    $controller->execute(JRequest::getCmd('task'));
    $controller->redirect();
}