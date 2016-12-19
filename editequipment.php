<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");
require_once("lib/equip.php");
require_once("lib/update-log.php");
//fill in fields after submit
$id = def("id", "new");
$modelnum = def("modelnum", "");
$manufacturer = def("manufacturer", "");
$requiredattend = def("requiredattend", "");
$requiredpilots = def("requiredpilots", "");
$seatingcap = def("seatingcap", "");
//only allow admins to use this page
restrict_roles(["administrator"]);
//Delete function for equipment
function delete_equipment($id){
  global $db;

  $status = false;
  $stmt = $db->prepare("DELETE FROM equipment WHERE registration_number = ?;");
  if (!$stmt->bind_param("s", $id))
    error("Unable to delete equipment: failed to bind parameters.");
  else if(!($status = $stmt->execute()))
    error("Unable to get equipment: failed to query the database.");
  return $status;
}

function get_equipmentinfo() {
  global $db, $id, $registration, $modelnum, $manufacturer, $requiredattend, $requiredpilots, $seatingcap;

  $status = false;
  $stmt = $db->prepare("SELECT registration_number, equipment.model_number, manufacturer, required_attendants, required_pilots, seating_capacity FROM equipment_types INNER JOIN equipment ON equipment.model_number = equipment_types.model_number WHERE registration_number = ?;");
  if (!$stmt->bind_param("s", $id))
    error("Unable to get equipment: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to get equipment: failed to query the database.");
  else if (!$stmt->bind_result($registration, $modelnum, $manufacturer, $requiredattend, $requiredpilots, $seatingcap))//bind variables to variables to fill in fields
    error("Unable to get equipment: failed to bind result.");
  else if (!($status = $stmt->fetch())) {
    //error("Unable to get employee: failed to fetch results.");
  }
  $stmt->close();
  return $status;
}

function add_equipment($registration, $equipment){
  global $db;

  $status = false;
  $stmt = $db->prepare("INSERT INTO equipment (registration_number, model_number) VALUES (?, ?);");
  if (!$stmt->bind_param("ss", $registration, $equipment))
    error("Unable to add equipment: failed to bind parameters.");
  else if (!($status = $stmt->execute()))
    error("Unable to add equipment: failed to update the database.");
  $stmt->close();
  return $status;
}

if(isset($_POST['delete'])){
  if(delete_equipment($id)){
    success("Equipment deleted");
  }
}

if(isset($_POST['add'])){
  if(strlen($_POST['registration']) > 3){
    if(add_equipment($_POST['registration'],$_POST['equipment'])){
      success("Equipment successfully added");
    }
  }else{
    error("Must be atleast 4 characters");
  }
}

if($id != "new"){
  get_equipmentinfo();
}
get_equip();
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <?php if ($id == "new") { ?>
        <h4>Add Equipment</h4>
      <?php } else { ?>
        <h4>Edit Equipment Information</h4>
      <?php } ?>
    </div>
    <div class="panel-body">
      <form action="" method="POST">
        <div class="row">
          <?php if ($id == "new") { ?>
          <div class="col-md-6">
            <div class="form-group">
              <label for="id">Registration Number:</label>
              <input class="form-control" name="registration" type="text" value="" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="equipment">Equipment Type:</label>
              <select class="form-control" name="equipment">
                <?php print_equip($equipment, ["NONE"]); ?>
              </select>
            </div>
          <?php } else { ?>
          <div class="col-md-6">
            <dl class="dl-horizontal">
              <dt>Registration Number</dt>
              <dd><?= $registration; ?></dd>
              <dt>Model Number</dt>
              <dd><?= $modelnum; ?></dd>
              <dt>Manufacturer</dt>
              <dd><?= $manufacturer; ?></dd>
              <dt>Required Attendants</dt>
              <dd><?= $requiredattend; ?></dd>
              <dt>Required Pilots</dt>
              <dd><?= $requiredpilots; ?></dd>
              <dt>Seating Capacity</dt>
              <dd><?= $seatingcap; ?></dd>
            </dl>
            <?php } ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <?php if ($id == "new") { ?>
              <input class="btn btn-primary pull-right" name="add" type="submit" value="Add" />
              <?php } else { ?>
              <input class="btn btn-primary pull-left" name="delete" type="submit" value="delete" />  
              <?php } ?>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
