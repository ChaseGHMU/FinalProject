<?php
// vim: set expandtab sts=2 sw=2 ts=2 tw=100:
require_once("fragments/header.php");
require_once("lib/update-log.php");
function check_user($user){
  global $db;
  $stmt = $db->prepare("Select customer_id from customers;");
  $stmt->execute();
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    if($user == $row['customer_id']){
      return true;
    }
  }
  return false;
}
function add_customer() {
  global $db, $id, $receipt;

  $status=false;

  if(check_user($_POST['userid']) == false){

    if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['age']) || empty($_POST['bags'])) {
      error("Please fill in the required fields.");
      return false;
    }

    $stmt = $db->prepare('INSERT INTO customers (first_name, last_name, age) VALUES (?,?,?)');
    $stmt->bind_param("ssi", $_POST['firstname'], $_POST['lastname'], $_POST['age']);
    $status = $stmt->execute();
    $id = $db->insert_id;
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO customer_flight (bags, customer_id, flight_number) VALUES (?,?,?)');
    $stmt->bind_param("iii", $_POST['bags'], $id, $_POST['flight_number']);
    $status = $stmt->execute();
    $receipt = $db->insert_id;
    $stmt->close();

    //update log
    $description = "Customer " . $id . " booked flight " . $_POST['flight_number'];
    $id_type = "customer";
    $action_type = "add";
    update_log($description, $id, $id_type, $action_type);
  }
  else if(check_user($_POST['userid']==true)) {
    $stmt = $db->prepare('INSERT INTO customer_flight (bags, customer_id, flight_number) VALUES (?,?,?)');
    $stmt->bind_param("iii", $_POST['bags'], $_POST['userid'], $_POST['flight_number']);
    $status = $stmt->execute();
    $id = $_POST['userid'];
    $receipt = $db->insert_id;
    $stmt->close();

    //update log
    $description = "Customer " . $_POST['userid'] . " booked flight " . $_POST['flight_number'];
    $id_type = "customer";
    $action_type = "add";
    update_log($description, $_POST['userid'], $id_type, $action_type);

  }else{
    error("Unable to update customer table.");
  }

  return $status;
}
echo '<div class="container">';
if (isset($_POST["submit"])) {
  if (add_customer()) {
    echo "<div class='jumbotron text-center'>
      <h1>Your order has been confirmed.</h1>
      <h3>Thank you for choosing Missouri Air!</h3>
      <p>Your receipt number is <b>$receipt</b></p>
      <p>Your customer number is <b>$id</b></p></div>";
  }
}
?>
<script type="text/javascript">
function testfunc(){
  var numBags = parseInt(document.getElementById('bagbox').value * 20);
  var price = parseInt(<?php echo $_GET['price'];?>);
  var total = ((price + numBags)*1.05).toFixed(2);
  document.getElementById('price').value = total;
}
</script>
  <?php require("fragments/status.php"); ?>
  <?php if(!isset($_POST['submit'])){?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Complete Flight Order</h4>
      </div>
      <div class="panel-body">
        <form action="#" method="POST">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                  <label for="flight_number">Flight Number:</label>
                    <input class="form-control" name="flight_number" readonly="readonly" type="text" value="<?php echo $_GET['flight_number']?>">
                </div>
                  <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input class="form-control" name="firstname" type="text"/>
                  </div>
                    <div class="form-group">
                      <label for="lastname">Last Name:</label>
                      <input class="form-control" name="lastname" type="text"/>
                    </div>
              </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="age">Age:</label>
                    <input class="form-control" name="age" type="number" min="0" max="150">
                  </div>
                    <div class="form-group">
                      <label for="bags">Bags:</label>
                        <select class="form-control" name="bags" id="bagbox" onchange="testfunc()">
                          <option value="0">0</option>
                          <option value="1">1</option>
                          <option value="2">2</option>
                          <option value="3">3</option>
                        </select>
                    </div>
          <div>
            <label for="user">Previous User?</label>
              <input type = "text" class ="form-control" placeholder="Enter User ID" name="userid">
          <div>
            <label for="price">Price:</label>
            <input type = "text" readonly id="price" value ="<?php echo $_GET['price'] * 1.05;?>">
          </div>
                </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <input class="btn btn-primary pull-right" name="submit" type="submit" />
                      </div>
                    </div>
                  </div>
            </div>
          </form>
      </div>
    </div>
  <?php } else{ ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Your Flight Order:</h4>
      </div>
      <div class="panel-body">
        <form action="#" method="POST">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="flight_number">Flight Number:</label>
                <input class="form-control" name="flight_number" style="text-align: center" readonly type="text" value="<?php echo $_GET['flight_number']?>">
              </div>
              <div class="form-group">
                <label for="firstname">First Name:</label>
                <input class="form-control" name="firstname" style="text-align: center" readonly type="text" value = "<?php echo$_POST['firstname'] ?>" />
              </div>
              <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input class="form-control" name="lastname" style="text-align: center" readonly type="text" value = "<?php echo$_POST['lastname']?>"/>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="age">Age:</label>
                <input class="form-control" style="text-align: center" name="age" readonly type="number" min="0" max="150" value = "<?php echo$_POST['age']?>">
              </div>
              <div class="form-group">
                <label for="bags">Bags:</label>
                <input class="form-control" name="bags" style="text-align: center" readonly type="number" min="0" max="150" value = "<?php echo$_POST['bags']?>">
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php } ?>
</div>
<?php require_once("fragments/footer.php"); ?>
