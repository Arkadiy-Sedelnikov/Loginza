<?php
/**
 * @version		1.0.4 from Arkadiy Sedelnikov
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later;
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php if($this->debug):?>
	<div style="color: #fff; font-size: 10pt; font-weight: bold; padding: 15px; border: 1px solid #bf0707; background: #ff7171;">
		<?php echo JText::_( 'ATTENTION_DEBUG' ); ?>
	</div>
<?php elseif($this->secretkey == '123' or $this->secretkey == null):?>
	<div style="color: #fff; font-size: 10pt; font-weight: bold; padding: 15px; border: 1px solid #bf0707; background: #ff7171;">
		<?php echo JText::_( 'ATTENTIONKEY' ); ?>
	</div>
<?php endif; ?>

<div style="padding: 15px; font-size: 9pt;">
<p><?php echo JText::_( 'DESC' ); ?></p>

<div style="text-align: center;"><?php echo JText::_( 'PROVIDERSAUTH' ); ?><br><br>
 <?php
    $i = 0;
    foreach ($this->providers as $provider){
        $i++;
        echo JHTML::_('image', $this->img_url . $provider.'_ico.gif', $provider).' '. $provider . ', ';
        if($i == 4){
            echo '<br />';
            $i = 0;
        }
    }
    ?>
 ...</div>
</div>


