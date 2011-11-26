<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access'); // no direct access
?>

<form action="/index.php" method="post" id="form-login">
	<p id="loginza-username">
		<label for="modloginza-username"><?php echo JText::_('LOGINZA_USERNAME') ?></label>
		<input id="modloginza-username" type="text" name="username" class="inputbox"  size="18" />
	</p>
	<p id="loginza-password">
		<label for="loginza-passwd"><?php echo JText::_('LOGINZA_PASSWORD') ?></label>
		<input id="loginza-passwd" type="password" name="<?php echo $formPassWord ?>" class="inputbox" size="18"  />
	</p>
	<p id="loginza-remember">
		<label for="modloginza-remember"><?php echo JText::_('LOGINZA_REMEMBER_ME') ?></label>
		<input id="modloginza-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
	</p>
	<div class="loginza-logout-button">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'LOGINZA_LOGENTER' ); ?>" />
	</div>

	<input type="hidden" name="option" value="<?php echo $formOpt; ?>" />
	<input type="hidden" name="task" value="<?php echo $formTask; ?>" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
    <ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option='.$formOpt.'&view=reset'); ?>">
			<?php echo JText::_('LOGINZA_FORGOT_YOUR_PASSWORD'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option='.$formOpt.'&view=remind'); ?>">
			<?php echo JText::_('LOGINZA_FORGOT_YOUR_USERNAME'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option='.$formOpt.'&view='.$formViewReg); ?>">
				<?php echo JText::_('LOGINZA_REGISTER'); ?></a>
		</li>
	</ul>
 
