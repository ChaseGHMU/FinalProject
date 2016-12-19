<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:

session_start();
session_destroy();
session_start();

$_SESSION["success"] = [];
$_SESSION["success"][] = "You have successfully logged out.";

header("Location: login.php");
