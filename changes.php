<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");

$id = def("id", "");
// Non-administrators can only see their own changes
if (!employee_is($_SESSION["employee_id"], "administrator")) {
  $id = $_SESSION["employee_id"];
}
$stmt = $db->prepare("SELECT * FROM log WHERE customer_id LIKE CONCAT('%',?,'%') OR employee_id LIKE CONCAT('%',?,'%');");
if (!$stmt->bind_param("ss", $id,$id))
  error("Unable to retrieve equipment: failed to bind parameters.");
else if (!$stmt->execute())
  error("Unable to retrieve equipment: failed to query the database.");
else if (!($results = $stmt->get_result()))
  error("Unable to retrieve equipment: failed to retrieve results.");
$stmt->close();
/* The page that allows a user to view the log should include means to filter the log
 * so that specific information may be located quickly (for example, by date of action,
 * by type of action [reservation, flight, etc.] --from the requirements doc */

restrict_roles(["administrator", "attendant", "pilot"]);
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
              <label for="id">Customer or Employee ID:</label>
              <input class="form-control" name="id" type="text" value="<?= $id; ?>" />
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
          <h4>Change History</h4>
        </div>
        <?php if(isset($results)) { ?>
          <table class='table table-hover'>
            <tr>
              <th>ID</th>
              <th>Description</th>
              <th>Customer ID</th>
              <th>Employee ID</th>
        <th>IP Address</th>
        <th>Time</th>
        <th>Type</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()) { ?>
              <tr>
                <td><?= $row["action_id"]; ?></td>
                <td><?= $row["description"]; ?></td>
                <td><?= $row["customer_id"]; ?></td>
        <td><?= $row["employee_id"]; ?></td>
        <td><?= $row["ip_address"]; ?></td>
        <td><?= $row["timestamp"]; ?></td>
        <td><?= $row["type"]; ?></td>
              </tr>
            <?php } ?>
          </table>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
