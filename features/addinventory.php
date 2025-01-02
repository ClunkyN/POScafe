<?php 
session_start();

include("../conn/connection.php");
include("../conn/function.php");

	$user_data = check_login($con);

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
      p{
        font-weight: bold;
      }
  input {
    background-image: url('./icons/add.png');
    background-position: 10px 5px;
    background-repeat: no-repeat;
    background-size: 30px;
    width: 95%;
    font-size: 16px;
    padding: 12px 20px 12px 45px;
    border: 2px solid purple;
    border-radius: 10px;
    margin-bottom: 12px;
    margin-left: 20px;
  }
  input:focus{
    outline-color: purple;
    outline-width: 4px;
  }
</style>    
<script>
  function validateForm() {
    var item = document.getElementById("item").value;
    var qty = document.getElementById("qty").value;
    var regex = /^[a-zA-Z0-9 ]*$/;

    if (!regex.test(item)) {
      alert("Item name can only contain letters, numbers, and spaces.");
      return false;
    }
    if (!regex.test(qty)) {
      alert("Quantity can only contain numbers.");
      return false;
    }

    return true;
  }
</script>
<title>CTT</title>
</head>
<body>
<div class = "dashboard">

    <form action="../features/addinv.php" method="post"  onsubmit="return validateForm()">
        <br/>
<div class="back" onclick="goBack()"></div>
<center><h2>Add Item</h2></center>

<div id="ac">
<p>Item: </p><input type="text" id="item" name="item" required><br>
<p>Quantity: </p><input type="number" id="qty" name="qty" required><br>

<button class="save">Save</button>
</div>
    <h6> </h6>
</div>
	
</body>
</html>