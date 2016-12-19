<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("fragments/header.php");

$id = def("id", 0);

function get_employeeinfo($id){
  global $db, $results;

  $stmt = $db->prepare("SELECT * FROM employee WHERE employee_id = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get pilots: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get pilots: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to get pilots: failed to retrieve results.");
  $stmt->close();
  return $results ? $results->num_rows : 0;
}

function get_flights($id) {
  global $db, $flights;

  $stmt = $db->prepare("SELECT flight_number, date, departure_city, destination_city,
    departure_time, arrival_time FROM employee_flight WHERE employee_id = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to search for flights: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to search for flights: failed to query the database.");
  else if (!($flights = $stmt->get_result()))
    error("Unable to search for flights: failed to retrieve results.");
  $stmt->close();
  return $flights ? $flights->num_rows : 0;
}

if (employee_is($id, "attendant") || employee_is($id, "pilot"))
  get_flights($id);

if (!get_employeeinfo($id))
  error("No information found for employee $id");
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4>Employee Information</h4>
    </div>
    <div class="panel-body">
      <?php if(isset($results)) { ?>
        <?php while ($row = $results->fetch_assoc()) { ?>
          <dl class="dl-horizontal">
            <dt>Employee ID</dt>
            <dd><?= $row["employee_id"]; ?></dd>
            <dt>First Name</dt>
            <dd><?= $row["first_name"]; ?></dd>
            <dt>Last Name</dt>
            <dd><?= $row["last_name"]; ?></dd>
            <dt>Role</dt>
            <dd><?= ucwords($row["role"]); ?></dd>
            <?php if ($row["role"] == "attendant" || $row["role"] == "pilot") { ?>
              <dt>Rank</dt>
              <dd><?= ucwords($row["rank"]); ?></dd>
              <dt>Status</dt>
              <dd><?= ucwords($row["status"]); ?></dd>
            <?php } ?>
            <?php if ($row["role"] == "pilot") { ?>
              <dt>Hours Flown</dt>
              <dd><?= ucwords($row["total_hours"]); ?></dd>
            <?php } ?>
          </dl>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <?php if(isset($flights)) if (is_object($flights)) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Flight History</h4>
      </div>
      <table class="table table-hover">
        <tr>
          <?php while ($field = $flights->fetch_field()) { ?>
            <th><?= ucwords(str_replace("_", " ", $field->name)); ?></th>
          <?php } ?>
        </tr>
        <?php while ($row = $flights->fetch_assoc()) { ?>
          <tr>
            <td><?= $row["flight_number"]; ?></td>
            <td><?= $row["date"]; ?></td>
            <td><?= city_name($row["departure_city"]); ?></td>
            <td><?= city_name($row["destination_city"]); ?></td>
            <td><?= $row["departure_time"]; ?></td>
            <td><?= $row["arrival_time"]; ?>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php $flights->free(); } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
