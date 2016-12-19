<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("fragments/header.php");

$id = def("id", "");
$list = [];

function get_reservations($id) {
  global $db, $list;

  $stmt = $db->prepare("SELECT first_name, last_name, customer_flight.reservation_number, flight.flight_number, bags, ((flight.price + bags*20)*1.05) AS price,
    flight_schedule.destination_city, flight_schedule.departure_city,flight_schedule.arrival_time, flight_schedule.departure_time
    FROM customers INNER JOIN customer_flight ON customers.customer_id = customer_flight.customer_id
    INNER JOIN flight ON customer_flight.flight_number = flight.flight_number
    INNER JOIN flight_schedule ON flight.flight_schedule_number = flight_schedule.flight_schedule_number
    WHERE customer_flight.customer_id = ?");

  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();

  $info = $stmt->get_result();

  $i = 0;
  while($row = $info->fetch_assoc()){
    $list[$i]= $row;
    $i += 1;
  }
  $stmt->close();
  if(!$list){
    return false;
  }else{
    return true;
  }
}

if(!empty($id))
  if (!get_reservations($id))
    error("No reservations were found for customer '$id'");
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4>View Past Reservations</h4>
    </div>
    <div class="panel-body">
      <form method="GET" action="reservation.php">
        <div class="form-group">
          <label for="id">Customer ID:</label>
          <input class="form-control" name="id" type="text" value="<?= $id; ?>" />
        </div>
        <div class="form-group">
          <input class="btn btn-primary pull-right" name="submit" type="submit" value="submit" />
        </div>
      </form>
    </div>
  </div>
  <?php foreach ($list as $reservation) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Reservation ID <?= $reservation["reservation_number"]; ?></h4>
      </div>
      <div class="panel-body">
        <dl class="dl-horizontal">
          <dt>Customer Name:</dt>
          <dd><?php echo $reservation['first_name'] . " " . $reservation['last_name'];?></dd>
          <dt>Flight Number:</dt>
          <dd><?php echo $reservation['flight_number']; ?></dd>
          <dt>Bags:</dt>
          <dd><?php echo $reservation['bags'];?></dd>
          <dt>Price:</dt>
          <dd><?php echo"$". $reservation['price'];?></dd>
          <dt>Destination:</dt>
          <dd><?php echo city_name($reservation['destination_city']); ?></dd>
          <dt>Departure:</dt>
          <dd><?php echo city_name($reservation['departure_city']); ?></dd>
          <dt>Arrival Time:</dt>
          <dd><?php echo $reservation['arrival_time']?></dd>
          <dt>Departure Time:</dt>
          <dd><?php echo $reservation['departure_time']?></dd>
        </dl>
      </div>
    </div>
  <?php } ?>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
