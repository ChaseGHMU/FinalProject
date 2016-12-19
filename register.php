<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/password.php");
require_once("fragments/header.php");
require_once("lib/update-log.php");

function register_user() {
  global $db;

  if (!employee_exists($_POST["employee_id"]))
    error("Please verify your employee ID");
  else if (!regcode_validate($_POST["employee_id"], $_POST["regcode"]))
    error("Please verify your registration code");
  else if (employee_has_user($_POST["employee_id"]))
    error("You already have a user account");
  else if (strlen($_POST["username"]) == 0 || strlen($_POST["username"]) > 20)
    error("The username you provided is invalid");
  else if (user_exists($_POST["username"]))
    error("The username you selected is already in use");
  else if (strlen($_POST["password"]) == 0)
    error("You must choose a password");
  else if ($_POST["passconfirm"] != $_POST["password"])
    error("The passwords you provided do not match");
  else {
    $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO authentication (employee_id, username, password)
      VALUES (?, ?, ?);");
    if (!$stmt->bind_param("iss", $_POST["employee_id"], $_POST["username"], $hash))
      error("Unable to create account: failed to bind parameters.");
    else if (!$stmt->execute())
      error("Unable to create account: failed to update the database.");
    else
      success('Thank you for registering. <a href="login.php">Log in here.</a>');
    
      //update log
      $description = "Employee " . $_POST["employee_id"] . " registered";
      $id_type = "employee";
      $action_type = "add";
      update_log($description, $_POST["employee_id"], $id_type, $action_type);
    
      $stmt->close();
  }
}

if (isset($_POST["reg"]))
  register_user();
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="row">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Create Your Account</h4>
        </div>
        <div class="panel-body">
          <form action="" method="POST">
            <div class="form-group">
              <label for="employee_id">Employee ID:</label>
              <input class="form-control" name="employee_id" type="text" />
            </div>
            <div class="form-group">
              <label for="regcode">Registration Code:</label>
              <input class="form-control" name="regcode" type="text" />
            </div>
            <div class="form-group">
              <label for="username">Pick A Username:</label>
              <input class="form-control" name="username" type="text" />
            </div>
            <div class="form-group">
              <label for="password">Create A Password:</label>
              <input class="form-control" name="password" type="password" />
            </div>
            <div class="form-group">
              <label for="passconfirm">Confirm Your Password:</label>
              <input class="form-control" name="passconfirm" type="password" />
            </div>
            <div class="form-group">
              <input class="btn btn-primary pull-right" name="reg" type="submit" value="Register" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Instructions</h4>
        </div>
        <div class="panel-body">
          <p>Enter your employee ID and the registration code provided by your manager.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
