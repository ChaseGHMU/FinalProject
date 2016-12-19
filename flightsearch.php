<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("fragments/header.php");

get_cities();
$date = def("date", date("Y-m-d"));
$from = def("from", "COU");
$price = def("price", -1);
$time1 = def("time1", "00:00");
$time2 = def("time2", "23:59");
$to = def("to", "MCI");

function print_prices(&$field, $default) {
  $prices = [
    [ "value" =>   -1, "label" => "any price" ],
    [ "value" =>    0, "label" => "$0 - $199" ],
    [ "value" =>  200, "label" => "$200 - $399" ],
    [ "value" =>  400, "label" => "$400 - $599" ],
    [ "value" =>  600, "label" => "$600 - $799" ],
    [ "value" =>  800, "label" => "$800 - $999" ],
    [ "value" => 1000, "label" => "$1000+" ],
  ];

  if (isset($field))
    $sel = $field;
  else
    $sel = $default;
  foreach ($prices as $price) {
    echo "<option ";
    if ($price["value"] == $sel)
      echo 'selected="selected" ';
    echo "value=\"${price["value"]}\">${price["label"]}</option>\n";
  }
}

function search_flights() {
  global $date, $db, $from, $price, $results, $time1, $time2, $to;

  $minprice = $price >= 0 ? $price : 0;
  $maxprice = $price >= 0 && $price < 1000 ? $price + 199 : 10000;
  $stmt = $db->prepare("SELECT flight_number, departure_time, arrival_time, price FROM flight JOIN
    flight_schedule ON flight.flight_schedule_number = flight_schedule.flight_schedule_number WHERE
    date = ? AND price BETWEEN ? AND ? AND departure_city = ? AND departure_time BETWEEN ? AND ?
    AND destination_city = ? AND status = 'active';");
  if (!$stmt->bind_param("siissss", $date, $minprice, $maxprice, $from, $time1, $time2, $to))
    error("Unable to search for flights: failed to bind parameters.");
  else if (!$stmt->execute())
    error("Unable to search for flights: failed to query the database.");
  else if (!($results = $stmt->get_result()))
    error("Unable to search for flights: failed to retrieve results.");
  $stmt->close();
  return $results ? $results->num_rows : 0;
}

if (isset($_GET["search"])) {
  $numresults = search_flights();
  if ($numresults == 0)
    error("No results were found for your query. Please adjust your search options and try again.");
}

?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4>Search for Flights</h4>
    </div>
    <div class="panel-body">
      <form action="" method="GET">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="from">From:</label>
              <select class="form-control" name="from">
                <?php print_cities($_GET["from"], "COU"); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="to">To:</label>
              <select class="form-control" name="to">
                <?php print_cities($_GET["to"], "MCI"); ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="date">Date:</label>
              <input class="form-control" name="date" type="date" value="<?= $date; ?>" />
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label for="time1">Earliest Time:</label>
                  <input class="form-control" name="time1" type="time" value="<?= $time1; ?>" />
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label for="time2">Latest Time:</label>
                  <input class="form-control" name="time2" type="time" value="<?= $time2; ?>" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="price">Price:</label>
              <select class="form-control" name="price">
                <?php print_prices($_GET["price"], -1); ?>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <input class="btn btn-primary pull-right" name="search" type="submit" value="Search" />
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php if(isset($results) && $numresults > 0) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Results (<?= $numresults; ?>)</h4>
      </div>
      <table class="table table-hover">
        <tr>
          <?php while ($field = $results->fetch_field()) { ?>
            <th><?= ucwords(str_replace("_", " ", $field->name)); ?></th>
          <?php } ?>
        </tr>
        <?php while ($row = $results->fetch_assoc()) { ?>
          <tr>
            <td><?= $row["flight_number"]; ?></td>
            <td><?= $row["departure_time"]; ?></td>
            <td><?= $row["arrival_time"]; ?></td>
            <td>$<?= $row["price"]; ?>
              <div class="pull-right">
                <a class="btn btn-primary btn-sm"
                  href="confirmation.php?flight_number=<?= $row["flight_number"]?>&price=<?= $row["price"]; ?>">Book</a>
              </div>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php $results->free(); } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
