<?php
session_start();
include "../conn/connection.php";

// Query to fetch all customers including the 'orders' column
$query = "SELECT * FROM customers";
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
    <title>Customers</title>
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
            <h1 class="text-2xl font-bold mb-4">Customers</h1>
            <div class="flex items-center justify-between">
                <button onclick="showAddModal()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                    Add Customer
                </button>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../features/archive_customers_table.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-archive mr-2"></i>View Archived Customers</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-6">
            <input type="text" placeholder="Search customers..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <h2 class="text-xl font-bold mb-4">Customers</h2>
                <table class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Birthday</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Orders</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $query = "SELECT c.*, CASE WHEN ac.id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
                                 FROM customers c 
                                 LEFT JOIN archive_customers ac ON c.id = ac.id";
                        $result = mysqli_query($con, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $rowClass = $row['is_archived'] ? 'bg-gray-200 text-gray-600' : 'hover:bg-gray-50';
                        ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['name']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['birthday']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['orders']; ?></td> <!-- Displaying the 'orders' column -->
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center gap-2">
                                            <?php if (!$row['is_archived']) { ?>
                                                <button onclick="editCustomer(<?php echo $row['id']; ?>)"
                                                    class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                    Edit
                                                </button>
                                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <button onclick="archiveCustomer(<?php echo $row['id']; ?>)"
                                                    class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                    Archive
                                                </button>
                                                <?php endif; ?>
                                            <?php } else { ?>
                                            <?php } ?>
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
    <div id="customerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Edit Customer</h2>
            <form id="customerForm" class="space-y-4">
                <input type="hidden" id="customer_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" id="customer_name" name="name" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Birthday</label>
                    <input type="date" id="customer_birthday" name="birthday" required
                        class="w-full p-2 border border-gray-300 rounded">
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
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Customer';
            document.getElementById('customerForm').reset();
            document.getElementById('customerModal').classList.remove('hidden');
        }

        function editCustomer(id) {
            document.getElementById('modalTitle').textContent = 'Edit Customer';
            fetch(`../endpoint/get_customer.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('customer_id').value = data.id;
                    document.getElementById('customer_name').value = data.name;
                    document.getElementById('customer_birthday').value = data.birthday;
                    document.getElementById('customerModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('customerModal').classList.add('hidden');
        }

        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isAdd = !formData.get('id');

            fetch(`../endpoint/${isAdd ? 'add_customer' : 'update_customer'}.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error saving customer');
                    }
                });
        });

        function archiveCustomer(id) {
            if (confirm('Are you sure you want to archive this customer?')) {
                fetch('../endpoint/archive_customer.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id: id
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '../features/archive_customers_table.php';
                        } else {
                            alert('Error archiving customer');
                        }
                    });
            }
        }

        function unarchiveCustomer(id) {
            if (confirm('Are you sure you want to unarchive this customer?')) {
                fetch('../endpoint/unarchive_customer.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id: id
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error unarchiving customer');
                        }
                    });
            }
        }
    </script>
</body>

</html>
