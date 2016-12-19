<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("fragments/header.php");

$id = def("id", 0);

function get_flights($id) {
  global $db, $flights;

  $stmt = $db->prepare("SELECT flight_number, flight_schedule.flight_schedule_number, registration_number, date, price, arrival_time, departure_time, departure_city, destination_city, status FROM flight_schedule INNER JOIN flight ON flight_schedule.flight_schedule_number = flight.flight_schedule_number WHERE registration_number = ?;");
  if (!$stmt->bind_param("s", $id))
    error("Unable to search for flights: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to search for flights: failed to query the database.");
  else if (!($flights = $stmt->get_result()))
    error("Unable to search for flights: failed to retrieve results.");
  $stmt->close();
  return $flights ? $flights->num_rows : 0;
}

function get_equipmentinfo($id){
  global $db, $results;

  $status = false;
  $stmt = $db->prepare("SELECT registration_number, equipment.model_number, manufacturer, required_attendants, required_pilots, seating_capacity FROM equipment_types INNER JOIN equipment ON equipment.model_number = equipment_types.model_number WHERE registration_number = ?;");
  if (!$stmt->bind_param("s", $id))
    error("Unable to get employee: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to get employee: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to get employee: failed to fetch results.");
  $stmt->close();
  return $status;
}
get_equipmentinfo($id);
get_flights($id);
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="row";>
   <div class="panel panel-default">
    <div class="panel-heading">
      <h4>Equipment Information</h4>
    </div>
    <div class="panel-body">
      <?php if(isset($results)) { ?>
          <?php while ($row = $results->fetch_assoc()) { ?>
            <dl class="dl-horizontal">
              <dt>Registration Number</dt>
              <dd><?= $row["registration_number"]; ?></dd>
              <dt>Model Number</dt>
              <dd><?= $row["model_number"]; ?></dd>
              <dt>Manufacturer</dt>
              <dd><?= $row["manufacturer"]; ?></dd>
              <dt>Required Attendants</dt>
              <dd><?= $row["required_attendants"]; ?></dd>
              <dt>Required Pilots</dt>
              <dd><?= $row["required_pilots"]; ?></dd>
              <dt>Seating Capacity</dt>
              <dd><?= $row["seating_capacity"]; ?></dd>
            </dl>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php if(isset($flights)) if (is_object($flights)) { ?>
  <div class = "row">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Flight Schedule for Equipment</h4>
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
            <td><?= $row["flight_schedule_number"]; ?></td>
            <td><?= $row["registration_number"]; ?></td>
            <td><?= $row["date"]; ?></td>
            <td><?= $row["price"]; ?></td>
            <td><?= $row["arrival_time"]; ?>
            <td><?= $row["departure_time"]; ?>
            <td><?= city_name($row["departure_city"]); ?>
            <td><?= city_name($row["destination_city"]); ?>
            <td><?= $row["status"]; ?>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
  <?php $flights->free(); } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
