<?php
defined('JPATH_BASE') or die();

class JElementList extends JElement
{
  var  $_name = 'List';

  function fetchElement($name, $value, &$node, $control_name)
  {
    $ctrl = $control_name . '[' . $name . ']';
    $attribs = '';

    if ($v = $node->attributes('class')) {
      $attribs .= ' class="'.$v.'"';
    } else {
      $attribs .= ' class="inputbox"';
    }

    if ($v = $node->attributes('size')) {
      $attribs .= ' size="'.$v.'"';
    }

    if ($m = $node->attributes('multiple')) {
      $attribs .= ' multiple="multiple"';
      $ctrl .= '[]';
    }

    $options = array ();
    foreach ($node->children() as $option)
    {
      $val  = $option->attributes('value');
      $text  = $option->data();
      $options[] = JHTML::_('select.option', $val, JText::_($text));
    }

    return JHTML::_('select.genericlist',  $options, $ctrl, trim($attribs), 'value', 'text', $value, $control_name.$name);
  }
}