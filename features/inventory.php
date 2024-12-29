<?php 
session_start();

include("../conn/connection.php");
include("../conn/function.php");


	$user_data = check_login($con);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="bg-[#FFF0DC]">
    <!-- Topbar -->
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <!-- Sidebar -->
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>
    <!-- Main content -->
    <main class="ml-[230px] mt-[171px] p-6">
        <!-- Content here -->
<div class = "dashboard">
<h3>INVENTORY</h3>
<a href = "addinventory.php" class="add">Add Item</a><br/><br/>
    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for items..">
    <div class="table"><table align = "left" border = "1" cellpadding = "3" cellspacing = "2" id = "myTable">  
<tr class = "header">  
<th onclick="sortTable(0)" data-type="text"> Item </th>   
<th onclick="sortTable(1)" data-type="numeric"> Quantity </th>
<th width="250px"> Action </th>

</tr>  
    <?php
    $query = "SELECT id, item, qty FROM inventory";
    $result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0):
  // output data of each row
  while($row = mysqli_fetch_assoc($result)):?>
<tr>
<td> <?php echo $row["item"] ?> </td> 
<td> <?php echo $row["qty"] ?> </td> 
  <td><a href= "editinventory.php?id=<?= $row['id']?>"><button class="ed">Edit</button></a>
  <a href="#" id="<?php echo $row['item']; ?>" class="delbutton" title="Click to Archive the product"><button class="del">Archive</button></a></td>

</tr>
    <?php endwhile;
     endif;?>

</table>
</div>

<script src="js/jquery.js"></script>
  <script type="text/javascript">
$(function() {


$(".delbutton").click(function(){

//Save the link in a variable called element
var element = $(this);

//Find the id of the link that was clicked
var del_id = element.attr("id");

//Built a url to send
var info = 'id=' + del_id;
 if(confirm("Sure you want to delete "+info+" ? There is NO undo!"))
		  {

 $.ajax({
   type: "GET",
   url: "delinv.php",
   data: info,
   success: function(){
    setTimeout(function(){
						location.reload()
					},100)
   }
 });
         $(this).parents(".record").animate({ backgroundColor: "#fbc7c7" }, "fast")
		.animate({ opacity: "hide" }, "slow");

 }

return false;

});

});
</script>

    <h6> </h6>
</div>
</main>
</body>
</html>