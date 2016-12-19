<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/database.php");
require_once("lib/param.php");
require_once("lib/status.php");
require_once("lib/user.php");

date_default_timezone_set("America/Chicago");
?>
<!DOCTYPE html>
<!-- begin fragments/header.php -->
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="author" content="CS3380 FS2016 Group 3" />
  <meta name="description" content="Missouri Air" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="css/site.css" rel="stylesheet" type="text/css" />
  <title>Missouri Air</title>
</head>
<body>
<nav class="navbar navbar-fixed-top navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button class="navbar-toggle collapsed" data-target="#navbar" data-toggle="collapse"
              type="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href=".">Missouri Air</a>
    </div>
    <div class="collapse navbar-collapse" id="navbar">
      <ul class="nav navbar-nav">
        <li><a href=".">Home</a></li>
        <li><a href="flightsearch.php">Book A Flight</a></li>
        <li><a href="reservation.php">View Reservations</a></li>
        <li><a href="contact.php">Contact Us</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if ($_SESSION["role"] == "none") { ?>
          <li><a href="login.php">Employee Login</a></li>
        <?php } else { ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
              <?= $_SESSION["username"]; ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <li><a href="editemployee.php?id=<?= $_SESSION["employee_id"]; ?>">Edit Profile</a></li>
              <li role="separator" class="divider"></li>
              <?php if ($_SESSION["role"] == "administrator") { ?>
                <li><a href="employeelist.php">Employee Roster</a></li>
              <?php } ?>
              <li><a href="equipmentlist.php">Equipment List</a></li>
              <li><a href="schedulelist.php">Flight Schedule</a></li>
              <li><a href="flightlist.php">Flight List</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="changes.php">Change History</a></li>
            </ul>
          </li>
          <li><a href="logout.php">Logout</a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
</nav>
<!-- end fragments/header.php -->
