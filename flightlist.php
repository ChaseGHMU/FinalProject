<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("lib/equip.php");
require_once("fragments/header.php");

$number = def("number", "");
$date = def("date", "");
$sched = def("sched", "");
$equipment = def("equipment", "");

function get_flights() {
  global $db, $number, $date, $sched, $equipment, $results;

  $stmt = $db->prepare("SELECT flight_number, date, price, flight.flight_schedule_number, departure_city,
    destination_city, departure_time, arrival_time, registration_number FROM flight INNER JOIN flight_schedule ON
    flight.flight_schedule_number = flight_schedule.flight_schedule_number WHERE
    CONVERT(flight_number, CHAR(16)) LIKE CONCAT('%', ? ,'%') AND date LIKE CONCAT('%',?,'%') AND flight.flight_schedule_number LIKE
    CONCAT('%',?,'%') AND registration_number LIKE CONCAT('%',?,'%');");
  if (!$stmt->bind_param("ssss", $number, $date, $sched, $equipment))
    error("Unable to retrieve flights: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to retrieve flights: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to retrieve flights: failed to retrieve results.");
  $stmt->close();
}

restrict_roles(["administrator", "attendant", "pilot"]);

get_cities();
get_equip();
get_flights();
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
              <label for="number">Flight Number:</label>
              <input class="form-control" name="number" type="text" value="<?= $number; ?>"/>
            </div>
            <div class="form-group">
              <label for="date">Date:</label>
              <input class="form-control" name="date" type="date" value="<?= $date; ?>"/>
            </div>
            <div class="form-group">
              <label for="sched">Schedule Number:</label>
              <input class="form-control" name="sched" type="text" value="<?= $sched; ?>"/>
            </div>
            <div class="form-group">
              <label for="equipment">Equipment Number:</label>
              <input class="form-control" name="equipment" type="text" value="<?= $equipment; ?>"/>
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
          <h4 class="pull-left">Flight List</h4>
          <?php if ($_SESSION["role"] == "administrator") { ?>
          <a class="btn btn-primary pull-right" href="editflight.php?new">Add Flight</a>
          <?php } ?>
          <div class="clearfix"></div>
        </div>
        <?php if(isset($results)) { ?>
          <table class='table table-hover'>
            <tr>
              <?php while ($field = $results->fetch_field()) { ?>
                <th><?= ucwords(str_replace("_", " ", $field->name)); ?></th>
              <?php } ?>
            </tr>
            <?php while ($row = $results->fetch_assoc()) { ?>
              <tr>
                <td><?= $row["flight_number"]; ?></td>
                <td><?= $row["date"]; ?></td>
                <td>$<?= $row["price"]; ?></td>
                <td><?= $row["flight_schedule_number"]; ?></td>
                <td><?= city_name($row["departure_city"]); ?></td>
                <td><?= city_name($row["destination_city"]); ?></td>
                <td><?= $row["departure_time"]; ?></td>
                <td><?= $row["arrival_time"]; ?></td>
                <td><?= $row["registration_number"]; ?>
                  <div class="pull-right">
                    <a class="btn btn-primary btn-sm"
                      href="flightinfo.php?id=<?= $row["flight_number"]; ?>">
                      <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php if ($_SESSION["role"] == "administrator") { ?>
                    <a class="btn btn-info btn-sm"
                      href="editschedule.php?id=<?= $row["flight_schedule_number"]; ?>">
                      <span class="glyphicon glyphicon-calendar"></span>
                    </a>
                    <a class="btn btn-warning btn-sm"
                      href="editflight.php?id=<?= $row["flight_number"]; ?>">
                      <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <?php } ?>
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
</div>
<?php require_once("fragments/footer.php"); ?>
