<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:

function print_days(&$field) {
  $days = [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ];

  foreach ($days as $option) {
    echo "<option ";
    if ($option == $field)
      echo 'selected="selected" ';
    echo "value=\"$option\">$option</option>\n";
  }
}
