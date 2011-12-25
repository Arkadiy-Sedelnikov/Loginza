<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access'); // no direct access
?>

<<<<<<< HEAD
<a class="loginza" href="<?php echo $loginza_url . $providersSet; ?>&amp;task=auth">
=======
<a class="loginza" href="<?php echo $loginza_url . $providersSet; ?>">
>>>>>>> abb3e6bc69f546afdfe8705196df6f141fea5cf7
    <img src="<?php echo $img_url?>button.gif" alt="<?php echo JText::_( 'LOGENTER' ); ?>"/>
</a>


<?php if($invite == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'invite');
endif;?>

<?php if($loginForm == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'login');
endif;?>
