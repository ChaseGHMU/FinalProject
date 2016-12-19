<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");

$id = def("id", 0);

function get_pilots($id){
  global $db, $resultspilots;

  $stmt = $db->prepare("SELECT first_name, last_name FROM employees INNER JOIN pilot_flight ON employees.employee_id = pilot_flight.employee_id WHERE flight_number = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get pilots: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get pilots: failed to query the database.");
  else if (!($resultspilots = $stmt->get_result()))
    error("Unable to get pilots: failed to retrieve results.");
  $stmt->close();
  return $resultspilots ? $resultspilots->num_rows : 0;
}

function get_attendants($id){
  global $db, $resultsattend;
 
  $stmt = $db->prepare("SELECT first_name, last_name FROM employees INNER JOIN attendant_flight ON employees.employee_id = attendant_flight.employee_id WHERE flight_number = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get attendants: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get attendants: failed to query the database.");
  else if (!($resultsattend = $stmt->get_result()))
    error("Unable to get attendants: failed to retrieve results.");
  $stmt->close();
  return $resultsattend ? $resultsattend->num_rows : 0;
}

function get_flights($id) {
  global $db, $result, $results;

  $stmt = $db->prepare("SELECT flight_number, date, price, flight.flight_schedule_number,
    registration_number, arrival_time, departure_city, departure_day, departure_time,
    destination_city FROM flight INNER JOIN flight_schedule ON flight.flight_schedule_number =
    flight_schedule.flight_schedule_number WHERE flight_number = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get flight: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get flight: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to get flight: failed to retrieve results.");
  else if (!($result = $results->fetch_assoc()))
    error("Unable to get flight: failed to retrieve row.");
  $stmt->close();
  return $results ? $results->num_rows : 0;
}

if (!get_flights($id))
  error("No information found for flight $id");

if (!get_pilots($id))
  error("No information found for pilots");

if (!get_attendants($id))
  error("No information found for attendants");
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <?php if(isset($result)) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Flight Information</h4>
      </div>
      <div class="panel-body">
        <dl class="dl-horizontal">
          <dt>Flight Number</dt>
          <dd><?= $result["flight_number"]; ?></dd>
          <dt>Date</dt>
          <dd><?= $result["date"]; ?> (<?= $result["departure_day"]; ?>)</dd>
          <dt>Departure Time</dt>
          <dd><?= $result["departure_time"]; ?></dd>
          <dt>Arrival Time</dt>
          <dd><?= $result["arrival_time"]; ?></dd>
          <dt>Departure City</dt>
          <dd><?= $result["departure_city"]; ?></dd>
          <dt>Destination City</dt>
          <dd><?= $result["destination_city"]; ?></dd>
          <dt>Ticket Price</dt>
          <dd>$<?= $result["price"]; ?></dd>
          <dt>Plane</dt>
          <dd><?= $result["registration_number"]; ?></dd>
        </dl>
      </div>
    </div>
  <?php if(isset($resultspilots)) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Pilots</h4>
      </div>
      <div class="panel-body">
      <?php while ($row = $resultspilots->fetch_assoc()) { ?>
        <dd><?php echo $row["first_name"] . " " . $row["last_name"]; ?></dd>
      <?php } ?>
      </div>
    </div>
  <?php } ?>
  <?php if(isset($resultsattend)) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Attendants</h4>
      </div>
      <div class="panel-body">
        <?php while ($row = $resultsattend->fetch_assoc()) { ?>
          <dd><?php echo $row["first_name"] . " " . $row["last_name"]; ?></dd>
        <?php } ?>
      </div>
    </div>
  <?php } ?>
  <?php $results->free(); } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
