<?php
$options = MG::getSetting('back-ring-option');
$options = unserialize(stripslashes($options));
if (!isset($options['header']) && !empty($options)) {
  $options['header'] = 'Обратный звонок';
  MG::setOption(array('option' => 'back-ring-option', 'value' => addslashes(serialize($options))));
}