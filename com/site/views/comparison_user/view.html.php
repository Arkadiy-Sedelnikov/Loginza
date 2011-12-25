<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 */
class LoginzaViewComparison_user extends JView
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
        $this->params = JComponentHelper::getParams('com_users');
        $this->user		= JFactory::getUser();
        $this->form		= $this->get('Form');
        $this->email = JRequest::getVar('email', '');
        $this->id = JRequest::getInt('id', 0);

		// Display the view
		parent::display($tpl);
	}
}
