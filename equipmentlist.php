<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");

$model_num = def("model_num", "");
$manufacturer = def("manufacturer", "");
$registration_num = def("registration_num", "");

function get_equipment() {
  global $db, $manufacturer, $model_num, $results, $registration_num;

  $stmt = $db->prepare("SELECT registration_number, equipment.model_number, manufacturer,
    required_attendants, required_pilots, seating_capacity FROM equipment_types INNER JOIN equipment
    ON equipment.model_number = equipment_types.model_number WHERE equipment_types.model_number LIKE
    CONCAT('%', ?, '%') AND manufacturer LIKE CONCAT('%', ?, '%') AND registration_number
    LIKE CONCAT('%', ?, '%') ORDER BY equipment.model_number;");
  if (!$stmt->bind_param("sss", $model_num, $manufacturer, $registration_num))
    error("Unable to retrieve equipment: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to retrieve equipment: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to retrieve equipment: failed to retrieve results.");
  $stmt->close();
}

restrict_roles(["administrator", "attendant", "pilot"]);
get_equipment();
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
              <label for="registration_num">Registration Number:</label>
              <input class="form-control" name="registration_num" type="text" value="<?= $registration_num; ?>" />
            </div>
            <div class="form-group">
              <label for="model_num">Model Number:</label>
              <input class="form-control" name="model_num" type="text" value="<?= $model_num; ?>" />
            </div>
            <div class="form-group">
              <label for="manufacturer">Manufacturer:</label>
              <input class="form-control" name="manufacturer" type="text" value="<?= $manufacturer; ?>" />
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
          <h4 class="pull-left">Equipment</h4>
          <?php if ($_SESSION["role"] == "administrator") { ?>
          <a class="btn btn-primary pull-right" href="editequipment.php?new">Add Equipment</a>
          <?php } ?>
          <div class="clearfix"></div>
        </div>
        <?php if(isset($results)) { ?>
          <table class='table table-hover'>
            <tr>
              <th>Registration Number</th>
              <th>Model Number</th>
              <th>Manufacturer</th>
              <th>Required Attendants</th>
              <th>Required Pilots</th>
              <th>Seating Capacity</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()) { ?>
              <tr>
                <td><?= $row["registration_number"]; ?></td>
                <td><?= $row["model_number"]; ?></td>
                <td><?= $row["manufacturer"]; ?></td>
                <td><?= $row["required_attendants"]; ?></td>
                <td><?= $row["required_pilots"]; ?></td>
                <td><?= $row["seating_capacity"]; ?>
                  <div class="pull-right">
                    <a class="btn btn-primary btn-sm"
                      href="equipmentinfo.php?id=<?= $row["registration_number"]; ?>">
                      <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php if ($_SESSION["role"] == "administrator") { ?>
                    <a class="btn btn-warning btn-sm"
                      href="editequipment.php?id=<?= $row["registration_number"]; ?>">
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
