<!-- begin fragments/status.php -->
<?php foreach ($_SESSION["success"] as $msg) { ?>
  <div class="alert alert-success">
    <strong>Success!</strong> <?= $msg; ?>
  </div>
<?php } $_SESSION["success"] = []; ?>
<?php foreach ($_SESSION["info"] as $msg) { ?>
  <div class="alert alert-info">
    <strong>Note:</strong> <?= $msg; ?>
  </div>
<?php } $_SESSION["info"] = []; ?>
<?php foreach ($_SESSION["error"] as $msg) { ?>
  <div class="alert alert-danger">
    <strong>Error:</strong> <?= $msg; ?>
  </div>
<?php } $_SESSION["error"] = []; ?>
<!-- end fragments/status.php -->
