<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:

// Ensure there is a session. We use the session so we can set an status message and then redirect.
// The status message will then show on the page the user was redirected to.
session_start();

// Ensure the session contains the required arrays.
if (!isset($_SESSION["error"]))
  $_SESSION["error"] = [];
if (!isset($_SESSION["info"]))
  $_SESSION["info"] = [];
if (!isset($_SESSION["success"]))
  $_SESSION["success"] = [];

// Helper functions to append a message to the list
function error($message) {
  $_SESSION["error"][] = $message;
}

function info($message) {
  $_SESSION["info"][] = $message;
}

function success($message) {
  $_SESSION["success"][] = $message;
}
