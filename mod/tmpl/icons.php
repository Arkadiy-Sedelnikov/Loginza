<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die('Restricted access'); // no direct access
?>

<div>
	<p><?php echo JText::_( 'LOGENTERUSESERVICE' ); ?></p>
	<a class="loginza" href="<?php echo $loginza_url . $providersSet; ?>">
        <img src="<?php echo $img_url?>icons.png" alt="<?php echo JText::_( 'LOGENTER' ); ?>"/>
    </a>
</div>

<?php if($invite == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'invite');
endif;?>

<?php if($loginForm == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'login');
endif;?>

 
