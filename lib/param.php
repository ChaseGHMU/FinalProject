<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("database.php");
require_once("status.php");

function def($field, $default) {
  if (isset($_POST[$field]))
    return $_POST[$field];
  if (isset($_GET[$field]))
    return $_GET[$field];
  return $default;
}
