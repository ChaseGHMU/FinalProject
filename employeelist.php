<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");

$admin = isset($_GET["admin"]) ? "administrator" : null;
$attend = isset($_GET["attend"]) ? "attendant" : null;
$current = isset($_GET["current"]) ? $_GET["current"] != "on" : true;
$name = def("name", "");
$pilot = isset($_GET["pilot"]) ? "pilot" : null;

// If none of the boxes are checked, all of them are checked.
if (empty($admin) && empty($attend) && empty($pilot)) {
  $admin = "administrator";
  $attend = "attendant";
  $pilot = "pilot";
}

function get_employees() {
  global $db, $admin, $attend, $name, $pilot, $results;

  $stmt = $db->prepare("SELECT employee_id, current, first_name, last_name, role, rank, status
    FROM employee WHERE
    CONCAT(first_name, ' ', last_name) LIKE CONCAT('%', ?, '%') AND role IN (?, ?, ?);");
  if (!$stmt->bind_param("ssss", $name, $admin, $attend, $pilot))
    error("Unable to retrieve employees: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to retrieve employees: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to retrieve employees: failed to retrieve results.");
  $stmt->close();
}

restrict_roles(["administrator"]);
get_employees();
?>
<div class="container-fluid">
  <?php require("fragments/status.php"); ?>
  <div class="row">
    <div class="col-md-2">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Filter</h4>
        </div>
        <div class="panel-body">
          <form action="" method="GET">
            <div class="form-group">
              <label for="name">Name:</label>
              <input class="form-control" name="name" type="text" value="<?= $name; ?>" />
            </div>
            <div class="form-group">
              Position:
              <div class="checkbox"><label>
                <input <?php if(!empty($admin)) echo 'checked="checked"'; ?>
                  name="admin" type="checkbox" />Administrator
              </label></div>
              <div class="checkbox"><label>
                <input <?php if(!empty($attend)) echo 'checked="checked"'; ?>
                  name="attend" type="checkbox" />Attendant
              </label></div>
              <div class="checkbox"><label>
                <input <?php if(!empty($pilot)) echo 'checked="checked"'; ?>
                  name="pilot" type="checkbox" />Pilot
              </label></div>
            </div>
            <div class="form-group">
              Past employees:
              <div class="checkbox"><label>
                <input <?php if(!$current) echo 'checked="checked"'; ?>
                  name="current" type="checkbox" />Show
              </label></div>
            </div>
            <div class="form-group">
              <input class="btn btn-primary pull-right" name="filter" type="submit" value="Apply" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-10">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="pull-left">Employee Roster</h4>
          <a class="btn btn-primary pull-right" href="editemployee.php?new">Add Employee</a>
          <div class="clearfix"></div>
        </div>
        <?php if(isset($results)) { ?>
          <table class='table table-hover'>
            <tr>
              <th>Employee ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Position</th>
              <th>Rank</th>
              <th>Status</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()) {
              if ($current && !$row["current"]) continue; ?>
              <tr>
                <td><?= $row["employee_id"]; ?></td>
                <td><?= $row["first_name"]; ?></td>
                <td><?= $row["last_name"]; ?></td>
                <td><?= ucwords($row["role"]); ?></td>
                <td><?= ucwords($row["rank"]); ?></td>
                <td><?= ucwords($row["status"]); ?>
                  <div class="pull-right">
                    <a class="btn btn-primary btn-sm"
                      href="employeeinfo.php?id=<?= $row["employee_id"]; ?>">
                      <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <a class="btn btn-warning btn-sm"
                      href="editemployee.php?id=<?= $row["employee_id"]; ?>">
                      <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </table>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
