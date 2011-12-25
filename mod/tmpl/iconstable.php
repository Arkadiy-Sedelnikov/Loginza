<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access'); // no direct access
?>

<div class="loginza_logenter">
            <?php echo JText::_( 'LOGENTERUSESERVICE' ); ?>
</div>

<div class="loginza_providers">
    <?php foreach ($providers as $provider) : ?>
        <div class="loginza_provider">
<<<<<<< HEAD
        	<a class="loginza" href="<?php echo $loginza_url ?>&amp;provider=<?php echo $provider; ?>&amp;task=auth">
=======
        	<a class="loginza" href="<?php echo $loginza_url ?>&amp;provider=<?php echo $provider; ?>">
>>>>>>> abb3e6bc69f546afdfe8705196df6f141fea5cf7
                <div class="loginza_provider_img loginza_prov_<?php echo $provider;?>" title="<?php echo $provider; ?>"></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="loginza_clear"></div>


<?php if($invite == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'invite');
endif;?>

<?php if($loginForm == '1'):
        // include the template for display
        require JModuleHelper::getLayoutPath('mod_loginza', 'login');
endif;?>
