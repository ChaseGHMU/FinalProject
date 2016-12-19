<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("database.php");
require_once("status.php");

$cities_cache = [];

function city_name($code) {
  global $db;

  $name = null;
  $stmt = $db->prepare("SELECT city_name FROM cities WHERE airport_code = ?;");
  if ($stmt->bind_param("s", $code))
    if ($stmt->execute())
      if ($stmt->bind_result($name))
        $stmt->fetch();
  $stmt->close();
  return $name;
}

function get_cities() {
  global $cities_cache, $db;

  if (!($query = $db->query("SELECT * FROM cities ORDER BY city_name;"))) {
    error("Unable to retrieve the list of cities.");
    return;
  }
  while ($row = $query->fetch_assoc()) {
    $cities_cache[] = $row;
  }
}

function print_cities(&$field, $default) {
  global $cities_cache;

  if (isset($field))
    $sel = $field;
  else
    $sel = $default;
  foreach ($cities_cache as $city) {
    echo "<option ";
    if ($city["airport_code"] == $sel)
      echo 'selected="selected" ';
    echo "value=\"${city["airport_code"]}\">${city["city_name"]}</option>\n";
  }
}
