<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("lib/days.php");
require_once("lib/equip.php");
require_once("fragments/header.php");

$day = def("day", "");
$from = def("from", "");
$to = def("to", "");

// Avoid using % in the HTML
if ($day == "")
  $day = "%";
if ($from == "")
  $from = "%";
if ($to == "")
  $to = "%";

function get_schedule() {
  global $db, $day, $from, $to, $results;

  $stmt = $db->prepare("SELECT flight_schedule_number, departure_day, departure_city,
    destination_city, departure_time, arrival_time FROM flight_schedule WHERE
    departure_day LIKE ? AND departure_city LIKE ? AND destination_city LIKE ?
    ORDER BY departure_day, departure_time;");
  if (!$stmt->bind_param("sss", $day, $from, $to))
    error("Unable to retrieve schedule: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to retrieve schedule: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to retrieve schedule: failed to retrieve results.");
  $stmt->close();
}

restrict_roles(["administrator", "attendant", "pilot"]);

get_cities();
get_schedule();
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
              <label for="day">Day:</label>
              <select class="form-control" name="day">
                <option value="">Any</option>
                <?php print_days($day); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="from">From:</label>
              <select class="form-control" name="from">
                <option value="">Any</option>
                <?php print_cities($from, ""); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="to">To:</label>
              <select class="form-control" name="to">
                <option value="">Any</option>
                <?php print_cities($to, ""); ?>
              </select>
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
          <h4 class="pull-left">Flight Schedule</h4>
          <?php if ($_SESSION["role"] == "administrator") { ?>
          <a class="btn btn-primary pull-right" href="editschedule.php?new">Add Schedule Entry</a>
          <?php } ?>
          <div class="clearfix"></div>
        </div>
        <?php if(isset($results)) if (is_object($results)) { ?>
          <table class='table table-hover'>
            <tr>
              <?php while ($field = $results->fetch_field()) { ?>
                <th><?= ucwords(str_replace("_", " ", $field->name)); ?></th>
              <?php } ?>
            </tr>
            <?php while ($row = $results->fetch_assoc()) { ?>
              <tr>
                <td><?= $row["flight_schedule_number"]; ?></td>
                <td><?= $row["departure_day"]; ?></td>
                <td><?= city_name($row["departure_city"]); ?></td>
                <td><?= city_name($row["destination_city"]); ?></td>
                <td><?= $row["departure_time"]; ?></td>
                <td><?= $row["arrival_time"]; ?>
                  <div class="pull-right">
                    <?php if ($_SESSION["role"] == "administrator") { ?>
                    <a class="btn btn-warning btn-sm"
                      href="editschedule.php?id=<?= $row["flight_schedule_number"]; ?>">
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
<?php require_once("fragments/footer.php"); ?>
