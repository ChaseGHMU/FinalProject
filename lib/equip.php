<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("database.php");
require_once("status.php");

$equip_cache = [];

function get_equip() {
  global $equip_cache, $db;

  if (!($query = $db->query("SELECT * FROM equipment_types ORDER BY model_number;"))) {
    error("Unable to retrieve the list of equipment types.");
    return;
  }
  while ($row = $query->fetch_assoc()) {
    $equip_cache[] = $row;
  }
}

function print_equip(&$field, $default) {
  global $equip_cache;

  if (isset($field))
    $sel = $field;
  else
    $sel = $default;
  foreach ($equip_cache as $type) {
    echo "<option ";
    if (in_array($type["model_number"], $sel))
      echo 'selected="selected" ';
    echo "value=\"${type["model_number"]}\">${type["manufacturer"]} ${type["model_number"]}</option>\n";
  }
}

function print_equip_single(&$field) {
  global $equip_cache;

  foreach ($equip_cache as $type) {
    echo "<option ";
    if ($type["model_number"] == $field)
      echo 'selected="selected" ';
    echo "value=\"${type["model_number"]}\">${type["manufacturer"]} ${type["model_number"]}</option>\n";
  }
}
