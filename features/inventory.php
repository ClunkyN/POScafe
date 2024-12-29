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
        <div class="dashboard">
            <h3 class="text-xl font-bold mb-4">INVENTORY</h3>
            <a href="addinventory.php" class="add bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600">Add Item</a>
            <br /><br />
            
            <!-- Search Input -->
            <input 
                type="text" 
                id="myInput" 
                onkeyup="myFunction()" 
                placeholder="Search for items.." 
                class="w-[90%] text-lg py-3 pl-10 pr-5 mb-3 border-2 border-purple-500 rounded-lg bg-[url('./icons/search.png')] bg-no-repeat bg-[length:30px] bg-[10px_10px] focus:outline-purple-500 focus:outline-[4px]"
            >

            <!-- Table -->
            <div class="table w-[90%] overflow-hidden rounded-lg border border-purple-500">
                <table id="myTable" class="w-full text-lg text-purple-900 border-collapse border-[0.5px] border-purple-500 rounded-lg">
                    <thead>
                        <tr class="header bg-purple-300 text-purple-900">
                            <th onclick="sortTable(0)" data-type="text" class="cursor-pointer px-3 py-2 hover:text-white hover:bg-purple-500">Item</th>
                            <th onclick="sortTable(1)" data-type="numeric" class="cursor-pointer px-3 py-2 hover:text-white hover:bg-purple-500">Quantity</th>
                            <th width="250px" class="px-3 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id, item, qty FROM inventory";
                        $result = mysqli_query($con, $query);

                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="border-b border-purple-500 bg-white hover:bg-purple-100">
                                    <td class="text-left px-3 py-2"><?= $row["item"] ?></td>
                                    <td class="text-left px-3 py-2"><?= $row["qty"] ?></td>
                                    <td class="text-left px-3 py-2">
                                        <a href="editinventory.php?id=<?= $row['id'] ?>">
                                            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Edit</button>
                                        </a>
                                        <a href="#" id="<?= $row['item'] ?>" class="delbutton" title="Click to Archive the product">
                                            <button class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Archive</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        endif; ?>
                    </tbody>
                </table>
            </div>

            <script src="js/jquery.js"></script>
            <script type="text/javascript">
            $(function() {
                $(".delbutton").click(function() {
                    var element = $(this);
                    var del_id = element.attr("id");
                    var info = 'id=' + del_id;
                    if (confirm("Sure you want to delete " + info + " ? There is NO undo!")) {
                        $.ajax({
                            type: "GET",
                            url: "delinv.php",
                            data: info,
                            success: function() {
                                setTimeout(function() {
                                    location.reload()
                                }, 100);
                            }
                        });
                        $(this).parents("tr").animate({ backgroundColor: "#fbc7c7" }, "fast")
                            .animate({ opacity: "hide" }, "slow");
                    }
                    return false;
                });
            });
            </script>
        </div>
    </main>
</body>
</html>
