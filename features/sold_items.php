<?php
session_start();
include "../conn/connection.php";

// Fetch the current month or set to today's month
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sold Items</title>
    <link rel="stylesheet" href="../src/output.css"> <!-- Assuming Tailwind CSS is being used -->
</head>

<body class="bg-[#FFF0DC]">
    <!-- Include Topbar and Sidebar -->
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <main class="ml-[230px] mt-[171px] p-6">
        <div class="flex flex-col justify-between items-start mb-6">
            <h1 class="text-2xl font-bold mb-4">Sold Items</h1>
            
            <!-- Month Selector -->
            <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Select Month</label>
            <input 
                type="month" 
                id="month" 
                value="<?php echo $month; ?>" 
                class="block w-48 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-[#C2A47E] focus:border-[#C2A47E]"
            >
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Search products..." 
                class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
            >
        </div>

        <!-- Sold Items Table -->
        <div class="overflow-x-auto">
            <table id="soldItemsTable" class="min-w-full bg-white border-4 border-black rounded-md">
                <thead class="bg-[#C2A47E] text-black">
                    <tr>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Product</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Sales</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Total Sales</th>
                        <th class="py-3 px-6 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Dynamic Rows will be inserted here -->
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal for Editing Sold Items -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-3/4 max-w-md">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Edit Sold Item</h2>
            </div>
            <div class="p-4 space-y-4">
                <input type="hidden" id="editId">
                <label for="editQty" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input 
                    type="number" 
                    id="editQty" 
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                >
            </div>
            <div class="p-4 border-t flex justify-end">
                <button id="saveEdit" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
                <button id="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded ml-2">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Fetch Sold Items
        function fetchSoldItems() {
            const month = document.getElementById('month').value;

            fetch(`get_sold.php?month=${month}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector('#soldItemsTable tbody');
                    tableBody.innerHTML = ''; // Clear existing rows

                    if (data.error) {
                        tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-3">${data.error}</td></tr>`;
                        return;
                    }

                    data.forEach(item => {
                        const row = `
                            <tr>
                                <td class="py-3 px-6">${item.name}</td>
                                <td class="py-3 px-6">${item.tqty}</td>
                                <td class="py-3 px-6">${item.total_sales}</td>
                                <td class="py-3 px-6">
                                    <button 
                                        class="px-3 py-1 bg-blue-500 text-white rounded" 
                                        onclick="openEditModal(${item.id}, ${item.tqty})"
                                    >Edit</button>
                                </td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => console.error('Error fetching sold items:', error));
        }

        // Open Edit Modal
        function openEditModal(id, qty) {
            document.getElementById('editId').value = id;
            document.getElementById('editQty').value = qty;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Close Edit Modal
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('editModal').classList.add('hidden');
        });

        // Save Edited Item
        document.getElementById('saveEdit').addEventListener('click', () => {
            const id = document.getElementById('editId').value;
            const qty = document.getElementById('editQty').value;

            fetch('update_sold.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&quantity=${qty}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    fetchSoldItems();
                    document.getElementById('editModal').classList.add('hidden');
                } else {
                    alert(data.error || 'Failed to update item.');
                }
            })
            .catch(error => console.error('Error updating sold item:', error));
        });

        // Fetch Sold Items on Month Change
        document.getElementById('month').addEventListener('change', fetchSoldItems);

        // Initial Fetch
        fetchSoldItems();
    </script>
</body>
</html>
