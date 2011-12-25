<?php
/**
 * @version        1.0.4 from Arkadiy Sedelnikov
 * @copyright    Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later;
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<div class="login<?php echo $this->pageclass_sfx?>">

    <h1>
        <?php echo JText::_('COM_LOGINZA_COMPARISON'); ?>
    </h1>

    <div class="login-description">
        <?php echo JText::sprintf('COM_LOGINZA_COMPARISON_DESC', $this->email); ?>
    </div>

    <form action="<?php echo JRoute::_('index.php?option=com_loginza&task=comp_email'); ?>" method="post">
        <fieldset>
            <div class="login-fields">
                <label id="username-lbl" for="username" class=" required">
                    <?php echo JText::_('COM_LOGINZA_USERNAME_LABEL'); ?>
                    <span class="star">&nbsp;*</span>
                </label>
                <input type="text" name="username" id="username" value="" class="validate-username required" size="25">
            </div>
            <div class="login-fields">
                <label id="password-lbl" for="password" class=" required">
                    <?php echo JText::_('COM_LOGINZA_PASS'); ?>
                    <span class="star">&nbsp;*</span>
                </label>
                <input type="password" name="password" id="password" value=""
                       class="validate-password required" size="25">
            </div>
            <button type="submit" class="button"><?php echo JText::_('COM_LOGINZA_JOIN'); ?></button>
            <input type="hidden" name="return"
                   value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>"/>
            <input type="hidden" name="user_id" value="<?php echo $this->id; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </form>

    <?php echo JText::_('COM_LOGINZA_LOST_PASS'); ?>

    <form id="user-registration" action="/index.php?option=com_users&amp;task=reset.request" method="post"
          class="form-validate">
        <p><?php echo JText::_('COM_LOGINZA_LOST_PASS_DESC'); ?></p>
        <fieldset>
            <input type="text" name="jform[email]" id="jform_email" value="<?php echo $this->email ?>" disabled="disabled" class="validate-username required invalid" size="30" aria-required="true" required="required" aria-invalid="true" />
        </fieldset>
        <button type="submit" class="validate"><?php echo JText::_('COM_LOGINZA_SUBMIT'); ?></button>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>