<?php 
session_start();
include "../conn/connection.php";
include "../conn/function.php";

// Check if user is logged in
$user_data = check_login($con);

// Fetch user data from the correct table
$query = "SELECT * FROM user_db";
$result = mysqli_query($con, $query);

if (!$result) {
    die('Query Failed: ' . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="../src/output.css">
    <!-- Optional: Add error reporting for debugging -->
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ?>
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
            
        </div>

        <!-- Search bar -->
        <div class="mb-6">
            <input type="text" placeholder="Search Users..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-md">
            <h2 class="text-xl font-bold mb-4">Users List</h2>
            <table class="min-w-full bg-white border-4 border-black rounded-md">
                <thead class="bg-[#C2A47E] text-black">
                    <tr>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">First Name</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Last Name</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Username</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Role</th>
                        <th class="py-3 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0): 
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-t border-gray-200">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row["fname"]); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row["lname"]); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row["user_name"]); ?></td>
                                <td class="px-4 py-2"><?php echo $row['role'] === 'admin' ? 'Admin' : 'Staff'; ?></td>
                                <td class="px-4 py-2 text-center">
                                    <button onclick="editUser(<?php echo $row['user_id']; ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                                    <button onclick="archiveUser(<?php echo $row['user_id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Archive</button>
                                </td>
                            </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Edit User</h2>
            <form id="userForm" class="space-y-4">
                <input type="hidden" id="user_id" name="user_id">

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
                    <select id="role" name="role" required
                        class="w-full p-2 border border-gray-300 rounded">
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>

        function editUser(id) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            fetch(`../endpoint/get_user.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('user_id').value = data.user_id;
                    document.getElementById('fname').value = data.fname;
                    document.getElementById('lname').value = data.lname;
                    document.getElementById('user_name').value = data.user_name;
                    document.getElementById('role').value = data.role;
                    document.getElementById('userModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isAdd = !formData.get('user_id');

            fetch(`../endpoint/${isAdd ? 'add_user' : 'update_user'}.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error saving user');
                    }
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
                            location.reload();
                        } else {
                            alert('Error archiving user');
                        }
                    });
            }
        }
    </script>
</body>
</html>
