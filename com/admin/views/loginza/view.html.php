<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LoginzaViewLoginza extends JView
{

    public function display($tpl = null)
    {
        // Initialise variables.
        $params = JComponentHelper::getParams('com_loginza');
        $this->secretkey = $params->get('secretkey');
        $this->debug = $params->get('debug', 0);
        $this->providers = array(
            'google',
            'yandex',
            'mailruapi',
            'mailru',
            'vkontakte',
            'facebook',
            'twitter',
            'loginza',
            'myopenid',
            'webmoney',
            'rambler',
            'flickr',
            'lastfm',
            'verisign',
            'aol',
            //'steam',
            'openid'
        );

        $this->img_url = JURI::base().'components/com_loginza/img/';
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        $this->addToolbar();

        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('LOGINZA_CONTROL_PANEL'), 'generic.png');
        JToolBarHelper::preferences('com_loginza');
    }

    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('LOGINZA_CONTROL_PANEL_LONG'));
        $document->addStyleSheet(JURI::root() . 'administrator/components/com_loginza/assets/css/style.css');
    }
}