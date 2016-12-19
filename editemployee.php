<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/equip.php");
require_once("lib/password.php");
require_once("fragments/header.php");
require_once("lib/update-log.php");

$id = def("id", "new");
$current = def("current", $id == "new");
$fname = def("fname", "");
$lname = def("lname", "");
$role = def("role", "pilot");
$rank = def("rank", "pilot");
$active = def("active", "inactive");
$hours = def("hours", 0);

$username = def("username", "");
$password = def("password", "");
$passconfirm = def("passconfirm", "");

if (employee_is($id, "pilot")) {
  get_equip();
  $equipment = def("equipment", []);
}

$ranks = [
  [ "value" => "copilot", "label" => "Copilot" ],
  [ "value" => "pilot",   "label" => "Pilot" ],
  [ "value" => "junior",  "label" => "Junior Attendant" ],
  [ "value" => "senior",  "label" => "Senior Attendant" ]
];

$roles = [
  [ "value" => "pilot",         "label" => "Pilot" ],
  [ "value" => "attendant",     "label" => "Flight Attendant" ],
  [ "value" => "administrator", "label" => "Administrator" ]
];

function print_options($data, &$field) {
  foreach ($data as $option) {
    echo "<option ";
    if ($option["value"] == $field)
      echo 'selected="selected" ';
    echo "value=\"${option["value"]}\">${option["label"]}</option>\n";
  }
}

function add_employee() {
  global $db, $id, $current, $fname, $lname, $role, $rank, $active, $hours;

  $status = false;
  $stmt = $db->prepare("INSERT INTO employees (current, first_name, last_name) VALUES (?, ?, ?);");
  if (!$stmt->bind_param("iss", $current, $fname, $lname))
    error("Unable to add employee: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to add employee: failed to update the database.");
  $id = $db->insert_id;
  $stmt->close();
  if (!$status)
    return false;

  //update log
  $description = "Employee " . $id . " added as " . $role;
  $id_type = "employee";
  $action_type = "add";
  update_log($description, $id, $id_type, $action_type);

  if ($role == "pilot") {
    $stmt = $db->prepare("INSERT INTO pilots (employee_id, rank, status, total_hours) VALUES
      (?, ?, ?, ?);");
    if (!$stmt->bind_param("issi", $id, $rank, $active, $hours))
      error("Unable to add employee attributes: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to add employee attributes: failed to update the database.");
    $stmt->close();
  } else if ($role == "attendant") {
    $stmt = $db->prepare("INSERT INTO attendants (employee_id, rank, status) VALUES (?, ?, ?);");
    if (!$stmt->bind_param("iss", $id, $rank, $active))
      error("Unable to add employee attributes: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to add employee attributes: failed to update the database.");
    $stmt->close();
  } else if ($role == "administrator") {
    $stmt = $db->prepare("INSERT INTO administrators (employee_id) VALUES (?);");
    if (!$stmt->bind_param("i", $id))
      error("Unable to add employee attributes: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to add employee attributes: failed to update the database.");
    $stmt->close();
  } else {
    error("Unknown employee type: '$role'");
    $status = false;
  }
  return $status;
}

function add_equipment($model) {
  global $db, $id;

  $status = false;
  $stmt = $db->prepare("INSERT INTO pilot_equipment (employee_id, model_number) VALUES (?, ?);");
  if (!$stmt->bind_param("is", $id, $model))
    error("Unable to add certification: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to add certification: failed to update the database.");
  $stmt->close();

  //update log
  $description = "Equipment " . $model . " added for " . $id;
  $id_type = "employee";
  $action_type = "add";
  update_log($description, $model, $id_type, $action_type);

  return $status;
}

function delete_equipment($model) {
  global $db, $id;

  $status = false;
  $stmt = $db->prepare("DELETE FROM pilot_equipment WHERE employee_id = ? AND model_number = ?;");
  if (!$stmt->bind_param("is", $id, $model))
    error("Unable to remove certification: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to remove certification: failed to update the database.");
  $stmt->close();

  //update log
  $description = "Equipment " . $model . " deleted for employee " . $id;
  $id_type = "employee";
  $action_type = "delete";
  update_log($description, $id, $id_type, $action_type);

  return $status;
}

function get_account() {
  global $db, $id, $username;

  $status = false;
  $stmt = $db->prepare("SELECT username FROM authentication WHERE employee_id = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get account: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to get account: failed to query the database.");
  else if (!$stmt->bind_result($username))
    error("Unable to get account: failed to bind result.");
  else if (!($status = $stmt->fetch()))
    // This employee most likely does not have an account
    $username = "";
  $stmt->close();
  return $status;
}

function get_employee() {
  global $db, $id, $current, $fname, $lname, $role, $rank, $active, $hours;

  $status = false;
  $stmt = $db->prepare("SELECT current, first_name, last_name, role, rank, status, total_hours
    FROM employee WHERE employee_id = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get employee: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to get employee: failed to query the database.");
  else if (!$stmt->bind_result($current, $fname, $lname, $role, $rank, $active, $hours))
    error("Unable to get employee: failed to bind result.");
  else if (!($status = $stmt->fetch()))
    error("Unable to get employee: failed to fetch results.");
  $stmt->close();
  return $status;
}

function get_equipment(&$equipment) {
  global $db, $id;

  $status = false;
  $stmt = $db->prepare("SELECT model_number FROM pilot_equipment WHERE employee_id = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get equipment: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get equipment: failed to query the database.");
  else if (!($result = $stmt->get_result()))
    error("Unable to get employee: failed to get result.");
  else {
    $status = true;
    while ($row = $result->fetch_assoc())
      $equipment[] = $row["model_number"];
    $result->free();
  }
  $stmt->close();
  return $status;
}

function save_account() {
  global $db, $id, $username, $password, $passconfirm;

  $status = false;
  if (!employee_exists($id))
    error("Please verify the employee ID");
  else if (!employee_has_user($id))
    error("This employee does not have a user account");
  else if (strlen($username) == 0 || strlen($username) > 20)
    error("The username you provided is invalid");
  else if (user_exists($username) && !employee_is_user($id, $username))
    error("The username you selected is already in use");
  else if (strlen($password) == 0 && strlen($passconfirm) == 0) {
    $stmt = $db->prepare("UPDATE authentication SET username = ? WHERE employee_id = ?;");
    if (!$stmt->bind_param("si", $username, $id))
      error("Unable to save account: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to save account: failed to update the database.");
    $stmt->close();

    //update log
    $description = "Employee " . $id . " changed their login info";
    $id_type = "employee";
    $action_type = "change";
    update_log($description, $id, $id_type, $action_type);

  } else if ($passconfirm != $password) {
    error("The passwords you provided do not match");
  } else {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE authentication SET username = ?, password = ? WHERE
      employee_id = ?;");
    if (!$stmt->bind_param("ssi", $username, $hash, $id))
      error("Unable to save account: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to save account: failed to update the database.");
    $stmt->close();

    //update log
    $description = "Employee " . $id . " changed their login info";
    $id_type = "employee";
    $action_type = "change";
    update_log($description, $id, $id_type, $action_type);
  }
  return $status;
}

function update_employee() {
  global $db, $id, $current, $fname, $lname, $role, $rank, $active, $hours;

  $status = false;
  $stmt = $db->prepare("UPDATE employees SET current = ?, first_name = ?, last_name = ? WHERE
    employee_id = ?;");
  if (!$stmt->bind_param("issi", $current, $fname, $lname, $id))
    error("Unable to update employee: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to update employee: failed to update the database.");
  $stmt->close();
  if (!$status)
    return false;

  //update log
  $description = "Employee " . $id . " info changed";
  $id_type = "employee";
  $action_type = "change";
  update_log($description, $id, $id_type, $action_type);

  if (employee_is($id, "pilot")) {
    $role = "pilot";
    $stmt = $db->prepare("UPDATE pilots SET rank = ?, status = ?, total_hours = ? WHERE
      employee_id = ?;");
    if (!$stmt->bind_param("ssii", $rank, $active, $hours, $id))
      error("Unable to update employee attributes: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to update employee attributes: failed to update the database.");
    $stmt->close();
  } else if (employee_is($id, "attendant")) {
    $role = "attendant";
    $stmt = $db->prepare("UPDATE attendants SET rank = ?, status = ? WHERE employee_id = ?;");
    if (!$stmt->bind_param("ssi", $rank, $active, $id))
      error("Unable to update employee attributes: failed to bind parameters.");
    else if (!($status = $stmt->execute()))
      error("Unable to update employee attributes: failed to update the database.");
    $stmt->close();
  } else if (employee_is($id, "administrator")) {
    $role = "administrator";
    $status = true;
  } else {
    error("Unknown employee type: '$role'");
    $status = false;
  }
  return $status;
}

function update_equipment() {
  global $db, $id, $equipment;

  $origequip = [];
  get_equipment($origequip);
  foreach ($origequip as $model) {
    if (!in_array($model, $equipment))
      delete_equipment($model);
  }
  foreach ($equipment as $model) {
    if (!in_array($model, $origequip))
      add_equipment($model);
  }
}

restrict_roles_or_id(["administrator"], $id);

if (isset($_POST["add"]))
  add_employee();
else if (isset($_POST["update"]))
  update_employee();
else if ($id != "new")
  get_employee();

if (isset($_POST["save"])) {
  if (save_account())
    success("Updated username and/or password");
} else if ($id != "new") {
  get_account();
}

if (employee_is($id, "pilot")) {
  if (isset($_POST["updequ"])) {
    if (update_equipment())
      success("Updated equipment certification");
  } else {
    get_equipment($equipment);
  }
}
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <?php if ($id == "new") { ?>
        <h4>Add Employee</h4>
      <?php } else { ?>
        <h4>Edit Employee Information</h4>
      <?php } ?>
    </div>
    <div class="panel-body">
      <form action="" method="POST">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="id">Employee ID:</label>
              <div class="checkbox-inline pull-right"><label>
                <input <?php if ($current) echo 'checked="checked"'; ?>
                  name="current" type="checkbox" value="1" /> Current
              </label></div>
              <input class="form-control" name="id" readonly="readonly"
                type="text" value="<?= $id; ?>" />
            </div>
            <div class="form-group">
              <label for="fname">First Name:</label>
              <input class="form-control" name="fname" type="text" value="<?= $fname; ?>" />
            </div>
            <div class="form-group">
              <label for="lname">Last Name:</label>
              <input class="form-control" name="lname" type="text" value="<?= $lname; ?>" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="role">Employee Type:</label>
              <?php if (employee_is($id, "attendant") || employee_is($id, "pilot")) { ?>
              <div class="checkbox-inline pull-right"><label>
                <input <?php if ($active != "inactive") echo 'checked="checked"'; ?>
                  name="active" type="checkbox" value="active" /> Active
              </label></div>
              <?php } ?>
              <select class="form-control" name="role"
                <?php if ($id != "new") echo 'disabled="disabled"'; ?>>
                <?php print_options($roles, $role); ?>
              </select>
            </div>
            <?php if (employee_is($id, "attendant") || employee_is($id, "pilot")) { ?>
              <div class="form-group">
                <label for="rank">Rank:</label>
                <select class="form-control" name="rank">
                  <?php print_options($ranks, $rank); ?>
                </select>
              </div>
            <?php } ?>
            <?php if (employee_is($id, "pilot")) { ?>
              <div class="form-group">
                <label for="hours">Hours Flown:</label>
                <input class="form-control" name="hours" type="text" value="<?= $hours; ?>" />
              </div>
            <?php } ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <?php if ($id == "new") { ?>
                <input class="btn btn-primary pull-right" name="add" type="submit" value="Add" />
              <?php } else { ?>
                <input class="btn btn-primary pull-right" name="update" type="submit" value="Update" />
              <?php } ?>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php if ($id != "new" && employee_has_user($id)) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Edit Account Information</h4>
      </div>
      <div class="panel-body">
        <form action="" method="POST">
          <input name="id" type="hidden" value="<?= $id; ?>" />
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="username">Username:</label>
                <input class="form-control" name="username" type="text" value="<?= $username; ?>" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="password">New Password:</label>
                <input class="form-control" name="password" type="password" />
              </div>
              <div class="form-group">
                <label for="passconfirm">Confirm New Password:</label>
                <input class="form-control" name="passconfirm" type="password" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input class="btn btn-primary pull-right" name="save" type="submit" value="Save" />
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php } else if ($id != "new") { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>View Account Information</h4>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="regcode">Registration Code:</label>
              <input class="form-control" name="username" type="text" readonly="readonly"
                value="<?= regcode_get($id); ?>" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <p>This employee does not yet have a user account.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>
  <?php if ($id != "new" && employee_is($id, "pilot")) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Edit Pilot Certification</h4>
      </div>
      <div class="panel-body">
        <form action="" method="POST">
          <input name="id" type="hidden" value="<?= $id; ?>" />
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="equipment">Certified Equipment:</label>
                <select class="form-control" multiple="multiple" name="equipment[]">
                  <?php print_equip($equipment, ["NONE"]); ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input class="btn btn-primary pull-right" name="updequ" type="submit" value="Save" />
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
