<?php 
session_start();
include "../conn/connection.php";
include "../conn/function.php";
$user_data = check_login($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
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
        <div class="flex flex-col justify-between items-start mb-6">
            <h1 class="text-2xl font-bold mb-4">Users</h1>
            <button onclick="window.location.href='#'" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                Add User
            </button>
            </div>

    <!-- Search bar -->
     <div class="mb-6">
            <input type="text" placeholder="Search Users..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
    </div>
        <!-- table -->
        <div class="overflow-x-auto rounded-md">
            <table class="min-w-full bg-white border-4 border-black rounded-md">
                <thead class="bg-[#C2A47E] text-black">
                    <tr>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">ID</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">First Name</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Last Name</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Username</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Type</th>
                        <th class="py-3 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-2"><?php echo $row["user_id"] ?></td>
                            <td class="px-4 py-2"><?php echo $row["fname"] ?></td>
                            <td class="px-4 py-2"><?php echo $row["lname"] ?></td>
                            <td class="px-4 py-2"><?php echo $row["user_name"] ?></td>
                            <td class="px-4 py-2"><?php echo $row['role'] == 1 ? 'Admin' : 'Staff'; ?></td>
                            <td class="px-4 py-2 text-center">
                                <a href="edituser.php?id=<?= $row['id']?>" class="inline-block bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>
                                <a href="#" id="<?php echo $row['id']; ?>" class="delbutton inline-block bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Archive</a>
                            </td>
                        </tr>
                        <?php endwhile;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function myFunction() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break;
                }
            }
        }
    }
}
</script>


</body>
</html>
