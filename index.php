<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("lib/cities.php");
require_once("fragments/header.php");

get_cities();
$date = def("date", date("Y-m-d"));
?>
<div class="container">
  <?php require("fragments/status.php"); ?>
  <div class="jumbotron text-center">
    <h1>Let's go places!</h1>
    <p>Wherever you want to be, from across the state to the other side of the country, Missouri Air can take you there.</p>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Search for Flights</h4>
        </div>
        <div class="panel-body">
          <form action="flightsearch.php" method="GET">
            <div class="form-group">
              <label for="from">From:</label>
              <select class="form-control" name="from">
                <?php print_cities($_GET["from"], "COU"); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="to">To:</label>
              <select class="form-control" name="to">
                <?php print_cities($_GET["to"], "ORD"); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="date">Date:</label>
              <input class="form-control" name="date" type="date" value="<?= $date; ?>"/>
            </div>
            <div class="form-group">
              <input class="btn btn-primary pull-right" name="search" type="submit" value="Search" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Current Sales</h4>
        </div>
        <ul class="list-group">
          <li class="list-group-item"><a href="flightsearch.php?to=ORD&search">
            Business trip? Book a flight to Chicago today! Only $349</a></li>
          <li class="list-group-item"><a href="flightsearch.php?to=MIA&search">
            Miami is calling! Flights start at $499</a></li>
        </ul>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4>Our Mission</h4>
        </div>
        <div class="panel-body">
          <p>At Missouri Air, convenience and accessibility are our greatest qualities. We provide
            flights to over 75 airports worldwide, so we will always have options for getting you to
            your dream destination. With our online tools, you can view all of the flights we offer,
            as well as check the status of the flights you have purchased tickets for. With Missouri
            Air, flying is no longer a hassle.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once("fragments/footer.php"); ?>
