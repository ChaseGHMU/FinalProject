<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("database.php");
require_once("status.php");

// Role should always be set, even if the user is not logged in.
if (empty($_SESSION["role"]))
  $_SESSION["role"] = "none";

function employee_exists($id) {
  return check_boolean("SELECT 1 FROM employees WHERE employee_id = ?;", "i", $id);
}

function employee_has_user($id) {
  return check_boolean("SELECT 1 FROM authentication WHERE employee_id = ?;", "i", $id);
}

function employee_is_user($id, $username) {
  global $db;

  $stmt = $db->prepare("SELECT 1 FROM authentication WHERE employee_id = ? AND username = ?;");
  if (!$stmt->bind_param("is", $id, $username))
    error("Unable to get employee user: failed to bind parameters.");
  else if (!($value = $stmt->execute()))
    error("Unable to get employee user: failed to query the database.");
  else if (!($value = $stmt->store_result()))
    error("Unable to get employee user: failed to store result.");
  else {
    $value = $stmt->num_rows == 1;
    $stmt->free_result();
  }
  $stmt->close();
  return $value;
}

function employee_is($id, $type) {
  if ($type != "administrator" && $type != "attendant" && $type != "pilot")
    return false;
  return check_boolean("SELECT 1 FROM ${type}s WHERE employee_id = ?;", "i", $id);
}

function employee_role($id) {
  if (employee_is($id, "administrator"))
    return "administrator";
  if (employee_is($id, "attendant"))
    return "attendant";
  if (employee_is($id, "pilot"))
    return "pilot";
  return "none";
}

function regcode_get($id) {
  return substr(hash("sha256", $id . REGCODE_SECRET, false), 0, 6);
}

function regcode_validate($id, $regcode) {
  return regcode_get($id) == $regcode;
}

function restrict_error() {
    error("You are not authorized to access this page.");
    $_SESSION["return"] = basename($_SERVER["SCRIPT_FILENAME"]);
    if ($_SESSION["role"] == "none")
      header("Location: login.php");
    else
      header("Location: index.php");
    exit;
}

function restrict_roles($roles) {
  if (!in_array($_SESSION["role"], $roles)) {
    restrict_error();
  }
}

function restrict_roles_or_id($roles, $id) {
  if (empty($id)) {
    restrict_roles($roles);
  } else if ($_SESSION["employee_id"] != $id && !in_array($_SESSION["role"], $roles)) {
    restrict_error();
  }
}

function user_exists($username) {
  return check_boolean("SELECT 1 FROM authentication WHERE username = ?;", "s", $username);
}
