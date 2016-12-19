<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/password.php");
require_once("fragments/header.php");

$username = def("username", "");

function login_user($username, $password) {
  global $db;

  $stmt = $db->prepare("SELECT employee_id, password FROM authentication WHERE username = ?;");
  if (!($stmt->bind_param("s", $username)))
    error("Unable to log in: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to log in: failed to query the database.");
  else if (!$stmt->bind_result($employee_id, $hash))
    error("Unable to log in: failed to bind results.");
  else if (!($status = $stmt->fetch()))
    error("Unable to log in: failed to retrieve results.");
  $stmt->close();
  if ($status && password_verify($password, $hash)) {
    $_SESSION["employee_id"] = $employee_id;
    $_SESSION["role"] = employee_role($employee_id);
    $_SESSION["username"] = $username;
  } else {
    $status = false;
    error("Unknown username or bad password");
  }
  return $status;
}

if (isset($_POST["login"]))
  login_user($_POST["username"], $_POST["password"]);
if ($_SESSION["role"] != "none") {
  if (empty($_SESSION["return"]))
    header("Location: index.php");
  else
    header("Location: ${_SESSION["return"]}");
  exit;
}
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="row">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Access Your Account</h4>
        </div>
        <div class="panel-body">
          <form action="" method="POST">
            <div class="form-group">
              <label for="username">Username:</label>
              <input class="form-control" name="username" type="text" value="<?= $username; ?>" />
            </div>
            <div class="form-group">
              <label for="password">Password:</label>
              <input class="form-control" name="password" type="password" />
            </div>
            <div class="form-group">
              <input class="btn btn-primary pull-right" name="login" type="submit" value="Log In" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Need Help?</h4>
        </div>
        <ul class="list-group">
          <li class="list-group-item"><a href="password.php">I forgot my password</a></li>
          <li class="list-group-item"><a href="register.php">I need to set up my account</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
