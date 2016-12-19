<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("config.php");
require_once("status.php");

$db = new mysqli(HOSTNAME, USERNAME, PASSWORD, DATABASE);

if ($db->connect_error)
  error("Unable to connect to database (" . $db->connect_errno . "): " . $db->connect_error);

function check_boolean($query, $type, $data) {
  global $db;

  if (!($stmt = $db->prepare($query))) {
    error("Could not bind prepare query '$query'. Please check your code.");
    return false;
  }
  if (!$stmt->bind_param($type, $data)) {
    error("Could not bind paramaters for query '$query'. Please check your code.");
    $stmt->close();
    return false;
  }
  if (!$stmt->execute()) {
    error("Could not query the database ('$query').");
    $stmt->close();
    return false;
  }
  $stmt->store_result();
  $value = $stmt->num_rows == 1;
  $stmt->free_result();
  $stmt->close();
  return $value;
}
