<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access'); // no direct access
?>

<form action="/index.php" method="post" id="form-login">
<?php if ($params->get('greeting')) : ?>
	<div class="loginza-hello">
	<?php
	echo JText::sprintf($greeting_template, $username);
    ?>
	</div>
<?php endif; ?>
	<div class="loginza-logout-button">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'LOGEXIT' ); ?>" />
	</div>

	<input type="hidden" name="option" value="<?php echo $formOpt; ?>" />
	<input type="hidden" name="task" value="<?php echo $formTask; ?>" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>