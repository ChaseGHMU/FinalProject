<?php
  require_once("lib/equip.php");
  require_once("lib/password.php");
  require_once("fragments/header.php");

  /* LOG
  action_id     int                             not null    auto_increment
  description   varchar(99)                     not null
  customer_id   int
  employee_id   int
  ip_address    varchar(45)                     not null
  timestamp     datetime                        not null
  type          enum('add','change','delete')   not null
  */

  function update_log($description, $id, $id_type, $action_type) {
    global $db;
    
    //determine ID type, assign variables accordingly
    if ($id_type == "employee") {
      $employee_id = isset($_SESSION["employee_id"]) ? $_SESSION["employee_id"] : $id;
      $customer_id = null;
      $person_id = $employee_id;
    } else if ($id_type == "customer") {
      $customer_id = $id;
      $employee_id = null;
      $person_id = $customer_id;
    } else {
      $customer_id = null;
      $employee_id = null;
      $person_id = null;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $timestamp = date("Y-m-d H:i:s");
    
    /*
    echo "Description: " . $description . "<br>";
    echo "ID: " . $id . "<br>";
    echo "ID type: " . $id_type . "<br>";
    echo "Customer ID: " . $customer_id . "<br>";
    echo "Employee ID: " . $employee_id . "<br>";
    echo "IP address: " . $ip_address . "<br>";
    echo "Timestamp: " . $timestamp . "<br>";
    echo "Action type: " . $action_type . "<br>";
    */
          
    $status = false;
    $stmt = $db->prepare("INSERT INTO log (description, customer_id, employee_id, ip_address, timestamp, type) VALUES (?,?,?,?,?,?);");    
    if (!$stmt->bind_param("siisss", $description, $customer_id, $employee_id, $ip_address, $timestamp, $action_type))
      error("Unable to update log: failed to bind parameters.");
    else if (!($status = $stmt->execute())) {
      error("Unable to update log: failed to update the database.");
      echo $stmt->error;
    }
    $stmt->close();
    return $status;
  }
?>
