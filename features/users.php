<?php
session_start();
include "../conn/connection.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: ../features/admin_login.php");
    exit();
}

// Double check admin role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND role = 'admin'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$query = "SELECT * FROM user_db";
$result = mysqli_query($con, $query);

if (!$result) {
    die('Query Failed' . mysqli_error($con));
}
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
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>

    <main class="ml-[230px] mt-[171px] p-6">
        <div class="flex flex-col justify-between items-start mb-6">
            <h1 class="text-2xl font-bold mb-4">Users</h1>
            <div class="flex items-center justify-between">
                <a href="../features/archive_users_table.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-archive mr-2"></i>View Archived Users</a>
            </div>
        </div>

        <div class="mb-6">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search users..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <h2 class="text-xl font-bold mb-4">Users</h2>
                <table id="userTable" class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">First Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Last Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Username</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Role</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $query = "SELECT u.*, CASE WHEN au.user_id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
                                 FROM user_db u 
                                 LEFT JOIN archive_users au ON u.user_id = au.user_id
                                 WHERE u.role != 'admin'";
                        $result = mysqli_query($con, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $rowClass = $row['is_archived'] ? 'bg-gray-200 text-gray-600' : 'hover:bg-gray-50';
                        ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['fname']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['lname']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['user_name']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['role']; ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center gap-2">
                                            <?php if (!$row['is_archived'] && $row['role'] === 'new_user') { ?>
                                                <button onclick="editUser(<?php echo $row['user_id']; ?>)"
                                                    class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                    Edit
                                                </button>
                                            <?php } ?>
                                            <button onclick="archiveUser(<?php echo $row['user_id']; ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                Archive
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Edit User</h2>
            <form id="userForm" class="space-y-4">
                <input type="hidden" id="user_id" name="user_id">
                <input type="hidden" id="original_role" name="original_role">

                <div>
                    <label class="block text-sm font-medium">First Name</label>
                    <input type="text" id="fname" name="fname" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Last Name</label>
                    <input type="text" id="lname" name="lname" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Username</label>
                    <input type="text" id="user_name" name="user_name" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Role</label>
                    <select id="edit_role" name="role" class="w-full p-2 border border-gray-300 rounded" required>
                        <option value="new_user">New User</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const originalRole = document.getElementById('original_role').value;
            const newRole = document.getElementById('edit_role').value;
            
            if (originalRole === 'employee' && newRole === 'new_user') {
                e.preventDefault();
                alert('Cannot change role back to new user once set to employee');
                document.getElementById('edit_role').value = 'employee';
                return false;
            }
        });

        function editUser(id) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            fetch(`../endpoint/get_user.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('user_id').value = data.user_id;
                    document.getElementById('fname').value = data.fname;
                    document.getElementById('lname').value = data.lname;
                    document.getElementById('user_name').value = data.user_name;
                    document.getElementById('edit_role').value = data.role;
                    document.getElementById('original_role').value = data.role; // Store original role
                    document.getElementById('userModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                    alert('Failed to fetch user details.');
                });
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        document.getElementById('userForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../endpoint/update_user.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload(); // Refresh to show updated data
                    } else {
                        alert(`Error: ${data.error || 'Unknown error'}`);
                    }
                })
                .catch(error => {
                    console.error('Error during form submission:', error);
                    alert('An error occurred while saving changes.');
                });
        });

        function archiveUser(id) {
            if (confirm('Are you sure you want to archive this user?')) {
                fetch('../endpoint/archive_user.php', {
                    method: 'POST',
                    body: JSON.stringify({ user_id: id }),
                    headers: { 'Content-Type': 'application/json' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirect to archive_users_table.php after successful archiving
                            window.location.href = '../features/archive_users_table.php';
                        } else {
                            alert(`Error: ${data.error || 'Unknown error'}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while archiving the user.');
                    });
            }
        }
    </script>
<script>
    function searchTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("userTable");
        let rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].innerText.toLowerCase().includes(input)) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? "" : "none";
        }
    }
</script>

</body>

</html>
