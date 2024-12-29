<?php 
session_start();

include("../conn/connection.php");
	include("../conn/function.php");
    

	$user_data = check_login($con);

	$id=$_GET['id'];
    $query="SELECT * FROM inventory WHERE id=$id";

    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0):
        // output data of each row
        while($row = mysqli_fetch_assoc($result)):?>
    
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
  .category{
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
    <br/>
<div class="back" onclick="goBack()"></div>
<center><h2>Edit Item</h2></center>
    <form action="editinv.php" method="post" onsubmit="return validateForm()">
<div id="ac">
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<p>Item: </p><input type="hidden" name="item" value="<?php echo $row['item']; ?>"><input type="text" id="item" name="nitem" value="<?php echo $row['item']; ?>" required><br>
<p>Quantity: </p><input type="number" id="qty" name="qty" value="<?php echo $row['qty']; ?>"><br>

<button class="save">Save</button>

</div>
<?php endwhile?>
<?php endif?>
    <h6> </h6>

</body>
</html>