<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("lib/update-log.php");
require_once("fragments/header.php");

$id = def("id", "new");
$sched = def("sched", null);
$date = def("date", null);
$equipment = def("equipment", null);
$price = def("price", null);
$pilots = def("pilots", []);
$attendants = def("attendants", []);

restrict_roles(["administrator"]);

function add_flight() {
  global $db, $id, $sched, $date, $equipment, $price;

  $status = false;
  $stmt = $db->prepare("INSERT INTO flight (date, price, flight_schedule_number,
    registration_number) VALUES (?, ?, ?, ?);");
  if (!$stmt->bind_param("siis", $date, $price, $sched, $equipment))
    error("Unable to add flight: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to add flight: failed to update the database." . $db->error);
  else {
    $id = $db->insert_id;

    //update log
    $description = "Flight added";
    $id_type = "employee";
    $action_type = "add";
    update_log($description, $sched, $id_type, $action_type);
  }

  $stmt->close();
  if (!$status)
    return $status;

  update_people("attendant");
  update_people("pilot");
}

function add_person($type, $empid) {
  global $db, $id;

  $stmt = $db->prepare("INSERT INTO ${type}_flight (employee_id, flight_number) VALUES (?, ?);");
  if (!$stmt->bind_param("ii", $empid, $id))
    error("Unable to add ${type}: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to add ${type}: failed to update the database.");
  else {
    //update log
    $description = "Employee " . $empid . " added to flight " . $id;
    $id_type = "employee";
    $action_type = "add";
    update_log($description, $empid, $id_type, $action_type);
  }
  $stmt->close();
}

function delete_person($type, $empid) {
  global $db, $id;

  $stmt = $db->prepare("DELETE FROM ${type}_flight WHERE employee_id = ? AND flight_number = ?;");
  if (!$stmt->bind_param("ii", $empid, $id))
    error("Unable to delete ${type}: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to delete ${type}: failed to update the database.");
  else {
    //update log
    $description = "Employee " . $empid . " deleted from flight " . $id;
    $id_type = "employee";
    $action_type = "delete";
    update_log($description, $empid, $id_type, $action_type);
  }
  $stmt->close();
}

function get_flight() {
  global $db, $id, $sched, $date, $equipment, $price, $pilots, $attendants;

  $status = false;
  $stmt = $db->prepare("SELECT date, price, flight_schedule_number,
      registration_number FROM flight WHERE flight_number = ?;");
  if (!$stmt->bind_param("i", $id))
      error("Unable to get flight: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
      error("Unable to get flight: failed to query the database.");
  else if (!$stmt->bind_result($date, $price, $sched, $equipment))
      error("Unable to get flight: failed to bind result.");
  else if (!($status = $stmt->fetch()))
      error("Unable to get flight: failed to fetch result.");
  $stmt->close();
  if (!$status)
      return $status;

  foreach (["attendant", "pilot"] as $type) {
    $stmt = $db->prepare("SELECT employee_id FROM ${type}_flight WHERE flight_number = ?;");
    if (!$stmt->bind_param("i", $id))
        error("Unable to get ${type}s: failed to bind parameters.");
    else if (!$stmt->execute())
        error("Unable to get ${type}s: failed to query the database.");
    else if (!($result = $stmt->get_result()))
        error("Unable to get ${type}s: failed to get result.");
    $stmt->close();
    if (!$result)
        return false;
    while ($row = $result->fetch_assoc())
      ${$type . "s"}[] = $row["employee_id"];
    $result->free();
  }
}

function print_attendants(&$field) {
  global $db;

  $results = $db->query("SELECT employee_id, first_name, last_name, rank FROM employee WHERE
    current = '1' AND role = 'attendant' ORDER BY last_name;");
  while ($entry = $results->fetch_assoc()) {
    echo "<option ";
    if (in_array($entry["employee_id"], $field))
      echo 'selected="selected" ';
    echo 'value="' . $entry["employee_id"] . '">' . $entry["last_name"] .
      ", " . $entry["first_name"] . " (" . ucwords($entry["rank"]) . ")</option>\n";
  }
}

function print_equipment(&$field) {
  global $db;

  $results = $db->query("SELECT registration_number, manufacturer, equipment.model_number,
    required_pilots, required_attendants FROM equipment INNER JOIN equipment_types ON
    equipment.model_number = equipment_types.model_number ORDER BY registration_number;");
  while ($entry = $results->fetch_assoc()) {
    echo "<option ";
    if ($entry["registration_number"] == $field)
      echo 'selected="selected" ';
    echo 'value="' . $entry["registration_number"] . '">' . $entry["registration_number"] .
      ": " . $entry["manufacturer"] . " " . $entry["model_number"] . " (" .
      $entry["required_pilots"] . " pilots, " . $entry["required_attendants"] .
      " attendants)</option>\n";
  }
}

function print_pilots(&$field) {
  global $db;

  $results = $db->query("SELECT employee_id, first_name, last_name, rank FROM employee WHERE
    current = '1' AND role = 'pilot' ORDER BY last_name;");
  while ($entry = $results->fetch_assoc()) {
    echo "<option ";
    if (in_array($entry["employee_id"], $field))
      echo 'selected="selected" ';
    echo 'value="' . $entry["employee_id"] . '">' . $entry["last_name"] .
      ", " . $entry["first_name"] . " (" . ucwords($entry["rank"]) . ")</option>\n";
  }
}

function print_schedlist(&$field) {
  global $db;

  $results = $db->query("SELECT flight_schedule_number, departure_city, destination_city,
    departure_day, departure_time FROM flight_schedule ORDER BY flight_schedule_number;");
  while ($entry = $results->fetch_assoc()) {
    echo "<option ";
    if ($entry["flight_schedule_number"] == $field)
      echo 'selected="selected" ';
    echo 'value="' . $entry["flight_schedule_number"] . '">' . $entry["flight_schedule_number"] .
      ": " . city_name($entry["departure_city"]) . " to " . city_name($entry["destination_city"]) .
      "; " . $entry["departure_day"] . "s at " . $entry["departure_time"] . "</option>\n";
  }
}

function update_flight() {
  global $db, $id, $sched, $date, $equipment, $price;

  $status = false;
  $stmt = $db->prepare("UPDATE flight SET date = ?, price = ?, flight_schedule_number = ?,
    registration_number = ? WHERE flight_number = ?;");
  if (!$stmt->bind_param("siisi", $date, $price, $sched, $equipment, $id))
    error("Unable to update flight: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to update flight: failed to update the database.");
  else {
    //update log
    $description = "Flight " . $id . " updated";
    $id_type = "employee";
    $action_type = "change";
    update_log($description, $id, $id_type, $action_type);
  }
  $stmt->close();
  if (!$status)
    return $status;

  update_people("attendant");
  update_people("pilot");
}

function update_people($type) {
  global $db, $id, $pilots, $attendants;

  $stmt = $db->prepare("SELECT employee_id FROM ${type}_flight WHERE flight_number = ?;");
  if (!$stmt->bind_param("i", $id))
    error("Unable to get ${type}s: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to get ${type}s: failed to query the database.");
  else if (!($result = $stmt->get_result()))
    error("Unable to get ${type}s: failed to get result.");
  $stmt->close();
  if (!$result)
    return false;

  $newpeople = ${$type . "s"};
  $oldpeople = [];
  while ($row = $result->fetch_assoc())
    $oldpeople[] = $row["employee_id"];
  $result->free();

  foreach ($newpeople as $new) {
    if (!in_array($new, $oldpeople))
      add_person($type, $new);
  }
  foreach ($oldpeople as $old) {
    if (!in_array($old, $newpeople))
      delete_person($type, $old);
  }
}

get_cities();

if (isset($_POST["add"]))
  add_flight();
else if (isset($_POST["update"]))
  update_flight();
else if ($id != "new")
  get_flight();
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <?php if ($id == "new") { ?>
        <h4>Add Flight</h4>
      <?php } else { ?>
        <h4>Edit Flight</h4>
      <?php } ?>
    </div>
    <div class="panel-body">
      <form action="" method="POST">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="id">Flight Number:</label>
              <input class="form-control" name="id" readonly="readonly" type="text"
                value="<?= $id; ?>" />
            </div>
            <div class="form-group">
              <label for="sched">Schedule Number:</label>
              <select class="form-control" name="sched">
                <?php print_schedlist($sched); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="date">Date:</label>
              <input class="form-control" name="date" type="date" value="<?= $date; ?>" />
            </div>
            <div class="form-group">
              <label for="equipment">Equipment Used:</label>
              <select class="form-control" name="equipment">
                <?php print_equipment($equipment); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="price">Price:</label>
              <input class="form-control" name="price" placeholder="(number only)" type="text"
                value="<?= $price; ?>" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="pilots">Pilots:</label>
              <select class="form-control" multiple="multiple" name="pilots[]" size="6">
                <?php print_pilots($pilots); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="attendants">Attendants:</label>
              <select class="form-control" multiple="multiple" name="attendants[]" size="6">
                <?php print_attendants($attendants); ?>
              </select>
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
