<?php
session_start();
include "../conn/connection.php";

$query = "SELECT i.*, CASE WHEN ai.id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
         FROM inventory i 
         LEFT JOIN archive_inventory ai ON i.id = ai.id";
$result = mysqli_query($con, $query);

if (!$result) {
    die('Query Failed' . mysqli_error($con));
}

$categories_query = "SELECT * FROM categories ORDER BY category_name ASC";
$categories_result = mysqli_query($con, $categories_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory</title>
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
            <h1 class="text-2xl font-bold mb-4">Inventory</h1>
            <button onclick="window.location.href='../endpoint/add_inventory_button.php'" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                Add Item
            </button>
        </div>

        <!-- Search bar -->
        <div class="mb-6">
            <input type="text" placeholder="Search items..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <!-- table -->
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
                                            <button onclick="archiveItem(<?php echo $row['id']; ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                Archive
                                            </button>
                                        <?php } else { ?>
                                            <button onclick="unarchiveItem(<?php echo $row['id']; ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                Unarchive
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='3' class='py-4 px-6 text-center'>No items found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="editItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Edit Item</h2>
            <form id="editItemForm" class="space-y-4">
                <input type="hidden" id="edit_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Item</label>
                    <input type="text" id="edit_item" name="item" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="number" id="edit_qty" name="qty" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeEditModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editItem(id) {
            console.log('Editing item:', id);
            fetch(`../endpoint/get_inventory.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if(data.error) {
                        throw new Error(data.error);
                    }
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_item').value = data.item;
                    document.getElementById('edit_qty').value = data.qty;
                    document.getElementById('editItemModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading item data');
                });
        }

    

function closeEditModal() {
    document.getElementById('editItemModal').classList.add('hidden');
}

document.getElementById('editItemForm').addEventListener('submit', function(e) {
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
                alert('Error updating product');
            }
        });
});

        function archiveItem(Id) {
            if (confirm('Are you sure you want to archive this item?')) {
                fetch('../endpoint/archive_inventory.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: Id }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error archiving item');
                    }
                });
            }
        }

        function unarchiveItem(Id) {
            if (confirm('Are you sure you want to unarchive this item?')) {
                fetch('../endpoint/unarchive_inventory.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: Id }),
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
    </script>
</body>

</html>
