<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("lib/days.php");
require_once("fragments/header.php");
require_once("lib/update-log.php");

$id = def("id", "new");
$from = def("from", "MCI");
$to = def("to", "STL");
$day = def("day", "Monday");
$deptime = def("deptime", "08:00:00");
$arrtime = def("arrtime", "10:00:00");

function add_schedule() {
  global $db, $id, $from, $to, $day, $deptime, $arrtime;

  $status = false;
  $stmt = $db->prepare("INSERT INTO flight_schedule (arrival_time, departure_city, departure_day,
    departure_time, destination_city) VALUES (?, ?, ?, ?, ?);");
  if (!$stmt->bind_param("sssss", $arrtime, $from, $day, $deptime, $to))
    error("Unable to add schedule entry: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to add schedule entry: failed to update the database.");
  else {
    $id = $db->insert_id;

    //update log
    $description = "Flight schedule added";
    $id_type = "employee";
    $action_type = "add";
    update_log($description, $from, $id_type, $action_type);
  }

  $stmt->close();
  return $status;
}

function get_schedule() {
  global $db, $id, $from, $to, $day, $deptime, $arrtime;

  $status = false;
  $stmt = $db->prepare("SELECT arrival_time, departure_city, departure_day, departure_time,
    destination_city FROM flight_schedule WHERE flight_schedule_number = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get schedule entry: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to get schedule entry: failed to query the database.");
  else if (!$stmt->bind_result($arrtime, $from, $day, $deptime, $to))
    error("Unable to get schedule entry: failed to bind result.");
  else if (!($status = $stmt->fetch()))
    error("Unable to get schedule entry: failed to fetch result.");
  $stmt->close();
  return $status;
}

function update_schedule() {
  global $db, $id, $from, $to, $day, $deptime, $arrtime;

  $status = false;
  $stmt = $db->prepare("UPDATE flight_schedule SET arrival_time = ?, departure_city = ?,
    departure_day = ?, departure_time = ?, destination_city = ? WHERE flight_schedule_number = ?;");
  if (!$stmt->bind_param("sssssi", $arrtime, $from, $day, $deptime, $to, $id))
    error("Unable to update schedule entry: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to update schedule entry: failed to update the database.");
  else {
    //update log
    $description = "Flight schedule " . $id . " updated";
    $id_type = "employee";
    $action_type = "change";
    update_log($description, $id, $id_type, $action_type);
  }
  $stmt->close();
  return $status;
}

restrict_roles(["administrator"]);

get_cities();

if (isset($_POST["add"]))
  add_schedule();
else if (isset($_POST["update"]))
  update_schedule();
else if ($id != "new")
  get_schedule();
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <?php if ($id == "new") { ?>
        <h4>Add Schedule Entry</h4>
      <?php } else { ?>
        <h4>Edit Schedule Entry</h4>
      <?php } ?>
    </div>
    <div class="panel-body">
      <form action="" method="POST">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="id">Schedule ID:</label>
              <input class="form-control" name="id" type="text" readonly="readonly"
                value="<?= $id; ?>" />
            </div>
            <div class="form-group">
              <label for="from">From:</label>
              <select class="form-control" name="from">
                <?php print_cities($from, null); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="to">To:</label>
              <select class="form-control" name="to">
                <?php print_cities($to, null); ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="day">Day:</label>
              <select class="form-control" name="day">
                <?php print_days($day); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="deptime">Departure Time:</label>
              <input class="form-control" name="deptime" type="time" value="<?= $deptime; ?>" />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="arrtime">Arrival Time:</label>
              <input class="form-control" name="arrtime" type="time" value="<?= $arrtime; ?>" />
            </div>
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
</div>
<?php require_once("fragments/footer.php"); ?>
