<?php
session_start();
include "../conn/connection.php";

$query = "SELECT * FROM inventory WHERE id NOT IN (SELECT id FROM archive_inventory)";
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
    <title>Inventory</title>
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
            <h1 class="text-2xl font-bold mb-4">Inventory</h1>
            <div class="flex items-center space-x-4">
                <button onclick="showAddModal()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                    Add Item
                </button>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="../features/archive_inventory_table.php" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-archive mr-2"></i>View Archived Items
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-6">
            <input type="text" placeholder="Search inventory..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <table class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Item</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Quantity</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $query = "SELECT i.*, CASE WHEN ai.id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
                                  FROM inventory i 
                                  LEFT JOIN archive_inventory ai ON i.id = ai.id";
                        $result = mysqli_query($con, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $rowClass = $row['is_archived'] ? 'bg-gray-200 text-gray-600' : 'hover:bg-gray-50';
                        ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['item']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['qty']; ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center gap-2">
                                            <?php if (!$row['is_archived']) { ?>
                                                <button onclick="editItem(<?php echo $row['id']; ?>)"
                                                    class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                    Edit
                                                </button>
                                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <button onclick="archiveItem(<?php echo $row['id']; ?>)"
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

    <!-- Add Inventory Modal -->
    <div id="addInventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Add Inventory Item</h2>
            <form id="addInventoryForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Item Name</label>
                    <input type="text" name="item" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="number" name="qty" min="0" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeAddModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div id="editInventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Edit Inventory Item</h2>
            <form id="editInventoryForm" class="space-y-4">
                <input type="hidden" id="edit_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Item Name</label>
                    <input type="text" id="edit_item" name="item" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Available Quantity</label>
                    <input type="number" id="edit_available_qty" name="available_qty" readonly
                        class="w-full p-2 border border-gray-300 rounded bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium">Additional Quantity</label>
                    <input type="number" id="edit_additional_qty" name="additional_qty" min="0" value="0"
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeEditModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Update Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="inventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Edit Item</h2>
            <form id="inventoryForm" class="space-y-4">
                <input type="hidden" id="item_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Item Name</label>
                    <input type="text" id="item_name" name="item" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="number" id="item_qty" name="qty" required
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
        // Update the form submission handler
        document.getElementById('editInventoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../endpoint/update_inventory.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Error updating inventory: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating inventory');
                });
        });

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Item';
            document.getElementById('inventoryForm').reset();
            document.getElementById('inventoryModal').classList.remove('hidden');
        }

        function editItem(id) {
            document.getElementById('modalTitle').textContent = 'Edit Item';
            fetch(`../endpoint/get_inventory.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('item_id').value = data.id;
                    document.getElementById('item_name').value = data.item;
                    document.getElementById('item_qty').value = data.qty;
                    document.getElementById('inventoryModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('inventoryModal').classList.add('hidden');
        }

        document.getElementById('inventoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isAdd = !formData.get('id');

            fetch(`../endpoint/${isAdd ? 'add_inventory' : 'update_inventory'}.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error saving item');
                    }
                });
        });

        function archiveItem(id) {
            if (confirm('Are you sure you want to archive this item?')) {
                fetch('../endpoint/archive_inventory.php', {
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
                            window.location.href = '../features/archive_inventory_table.php';
                        } else {
                            alert('Error archiving item');
                        }
                    });
            }
        }

        function unarchiveItem(id) {
            if (confirm('Are you sure you want to unarchive this item?')) {
                fetch('../endpoint/unarchive_inventory.php', {
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
                            alert('Error unarchiving item');
                        }
                    });
            }
        }

        function openAddModal() {
            document.getElementById('addInventoryModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addInventoryModal').classList.add('hidden');
        }

        function editItem(id) {
            fetch(`../endpoint/get_inventory.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_item').value = data.item;
                    document.getElementById('edit_available_qty').value = data.qty;
                    document.getElementById('edit_additional_qty').value = 0;
                    document.getElementById('editInventoryModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching item details');
                });
        }

        function closeEditModal() {
            document.getElementById('editInventoryModal').classList.add('hidden');
        }
    </script>
</body>

</html>