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
            <a href="../features/addinventory.php" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">Add Item</a>
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
            <div class="overflow-x-auto rounded-md">
                <table id="myTable" class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th onclick="sortTable(0)" data-type="text" class="py-3 px-6 text-left border-r border-[#A88B68]">Item</th>
                            <th onclick="sortTable(1)" data-type="numeric" class="py-3 px-6 text-left border-r border-[#A88B68]">Quantity</th>
                            <th width="250px" class="py-3 px-6 text-left border-r border-[#A88B68]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $query = "SELECT id, item, qty FROM inventory";
                        $result = mysqli_query($con, $query);

                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-6 border-r border-black"><?= $row["item"] ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?= $row["qty"] ?></td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <a href="..features/editinventory.php?id=<?= $row['id'] ?>">
                                            <button class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">Edit</button>
                                        </a>
                                        <a href="#" id="<?= $row['item'] ?>" class="delbutton" title="Click to Archive the product">
                                            <button class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">Archive</button>
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
                            url: "../features/delinv.php",
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
