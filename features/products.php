<?php
session_start();
include "../conn/connection.php";

// RBAC Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')) {
    session_unset();
    session_destroy();
    header("Location: ../features/homepage.php");
    exit();
}

// Verify role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND (role = 'admin' OR role = 'employee')";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$query = "SELECT * FROM products";
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
    <title>Products</title>
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
            <h1 class="text-2xl font-bold mb-4">Products</h1>
            <div class="flex items-center space-x-4">    
                <button onclick="showAddModal()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                    Add New Product
                </button>
                <a class="underline text-blue-600" href="../features/archive_products_table.php">View Archived Products</a>
            </div>
        </div>

        <div class="mb-6">
            <input type="text" placeholder="Search products..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <table class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[    ]">Product Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Category</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Price</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Quantity</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]"> Items</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        try {
                            $query = "SELECT 
                                p.id,
                                p.product_name,
                                p.category_id,
                                p.price,
                                p.quantity,
                                p.required_items,
                                c.category_name
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id
                            ORDER BY p.id DESC";

                            $result = mysqli_query($con, $query);

                            if (!$result) {
                                throw new Exception(mysqli_error($con));
                            }
                        } catch (Exception $e) {
                            echo "<div class='text-red-500 p-4'>Error: Unable to fetch products.</div>";
                            error_log("Database Error: " . $e->getMessage());
                            $result = false;
                        }

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($row['product_name']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        ₱<?php echo number_format($row['price'], 2); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($row['quantity']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php 
                                        if (!empty($row['required_items'])) {
                                            $items = json_decode($row['required_items'], true);
                                            echo "<ul class='list-disc pl-4'>";
                                            foreach ($items as $item) {
                                                echo "<li>{$item['name']} ({$item['quantity']} pcs)</li>";
                                            }
                                            echo "</ul>";
                                        }
                                        ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="editProduct(<?php echo $row['id']; ?>)"
                                                class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                Edit
                                            </button>
                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                            <button onclick="archiveProduct(<?php echo $row['id']; ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                Archive
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='py-4 px-6 text-center'>No products found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-[600px]">
            <h2 class="text-xl font-bold mb-4">Add Product</h2>
            <form id="addProductForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" name="product_name" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category_id" required class="w-full p-2 border border-gray-300 rounded">
                        <?php
                        $categories = mysqli_query($con, "SELECT * FROM categories WHERE id NOT IN (SELECT id FROM archive_categories)");
                        while ($category = mysqli_fetch_assoc($categories)) {
                            echo "<option value='" . $category['id'] . "'>" . $category['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" name="price" step="0.01" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="number" name="quantity" min="0" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Required Items</label>
                    <div id="addItemsList" class="space-y-2">
                        <div class="flex gap-2">
                            <select name="items[]" class="w-2/3 p-2 border border-gray-300 rounded">
                                <?php
                                $inventory = mysqli_query($con, "SELECT * FROM inventory WHERE id NOT IN (SELECT id FROM archive_inventory)");
                                while($item = mysqli_fetch_assoc($inventory)) {
                                    echo "<option value='".$item['id']."|".$item['item']."'>".$item['item']."</option>";
                                }
                                ?>
                            </select>
                            <input type="number" name="item_qty[]" min="1" value="1" class="w-1/4 p-2 border border-gray-300 rounded">
                            <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 rounded">×</button>
                        </div>
                    </div>
                    <button type="button" onclick="addNewItem('addItemsList')" class="mt-2 text-blue-600 hover:text-blue-800">+ Add Another Item</button>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeAddModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-[600px]">
            <h2 class="text-xl font-bold mb-4">Edit Product</h2>
            <form id="editProductForm" class="space-y-4">
                <input type="hidden" id="edit_product_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" id="edit_product_name" name="product_name" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Category</label>
                    <select id="edit_category_id" name="category_id" required class="w-full p-2 border border-gray-300 rounded">
                        <?php
                        $categories = mysqli_query($con, "SELECT * FROM categories WHERE id NOT IN (SELECT id FROM archive_categories)");
                        while ($category = mysqli_fetch_assoc($categories)) {
                            echo "<option value='" . $category['id'] . "'>" . $category['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Available Quantity</label>
                    <input type="number" id="edit_available_quantity" name="available_quantity" readonly 
                        class="w-full p-2 border border-gray-300 rounded bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium">Additional Quantity</label>
                    <input type="number" id="edit_additional_quantity" name="additional_quantity" min="0" value="0"
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Required Items</label>
                    <div id="editItemsList" class="space-y-2"></div>
                    <button type="button" onclick="addNewItem('editItemsList')" class="mt-2 text-blue-600 hover:text-blue-800">+ Add Another Item</button>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editProduct(id) {
            fetch(`../endpoint/get_product.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_product_id').value = data.id;
                    document.getElementById('edit_product_name').value = data.product_name;
                    document.getElementById('edit_category_id').value = data.category_id;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_available_quantity').value = data.quantity;
                    document.getElementById('edit_additional_quantity').value = 0;
                    
                    // Clear existing items
                    document.getElementById('editItemsList').innerHTML = '';
                    
                    // Load existing items
                    if (data.required_items) {
                        const items = JSON.parse(data.required_items);
                        items.forEach(item => {
                            const itemDiv = `
                                <div class="flex gap-2">
                                    <select name="items[]" class="w-2/3 p-2 border border-gray-300 rounded">
                                        <?php
                                        $inventory = mysqli_query($con, "SELECT * FROM inventory WHERE id NOT IN (SELECT id FROM archive_inventory)");
                                        while($item = mysqli_fetch_assoc($inventory)) {
                                            echo "<option value='".$item['id']."|".$item['item']."'>".$item['item']."</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type="number" name="item_qty[]" min="1" value="${item.quantity}" class="w-1/4 p-2 border border-gray-300 rounded">
                                    <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 rounded">×</button>
                                </div>
                            `;
                            const div = document.createElement('div');
                            div.innerHTML = itemDiv;
                            
                            // Set selected value for dropdown
                            const select = div.querySelector('select');
                            const optionValue = `${item.id}|${item.name}`;
                            Array.from(select.options).forEach(option => {
                                if (option.value === optionValue) {
                                    option.selected = true;
                                }
                            });
                            
                            document.getElementById('editItemsList').appendChild(div.firstElementChild);
                        });
                    }
                    
                    document.getElementById('editProductModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details');
                });
        }

        // Add event listener for edit form submission
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate number of items
            const itemsCount = document.querySelectorAll('#editItemsList > div').length;
            if (itemsCount > 3) {
                alert('Maximum of 3 items allowed');
                return;
            }

            const formData = new FormData(this);
            
            // Collect items and quantities
            const items = [];
            const itemSelects = this.querySelectorAll('select[name="items[]"]');
            const itemQtys = this.querySelectorAll('input[name="item_qty[]"]');
            
            itemSelects.forEach((select, index) => {
                const [id, name] = select.value.split('|');
                items.push({
                    id: parseInt(id),
                    name: name,
                    quantity: parseInt(itemQtys[index].value)
                });
            });
            
            formData.set('required_items', JSON.stringify(items));
            
            // Calculate total quantity
            const availableQty = parseInt(document.getElementById('edit_available_quantity').value);
            const additionalQty = parseInt(document.getElementById('edit_additional_quantity').value);
            const totalQty = availableQty + additionalQty;
            
            formData.set('quantity', totalQty);
            
            fetch('../endpoint/update_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error updating product: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating product');
            });
        });

        function archiveProduct(id) {
            if (confirm('Are you sure you want to archive this product?')) {
                fetch('../endpoint/archive_product.php', {
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
                            window.location.href = 'archive_products_table.php';
                        } else {
                            alert('Error archiving product');
                        }
                    });
            }
        }

        function unarchiveProduct(id) {
            if (confirm('Are you sure you want to unarchive this product?')) {
                fetch('../endpoint/unarchive_product.php', {
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
                            alert('Error unarchiving product');
                        }
                    });
            }
        }

        function closeAddModal() {
            document.getElementById('addProductModal').classList.add('hidden');
            document.getElementById('addProductForm').reset();
        }

        function closeEditModal() {
            document.getElementById('editProductModal').classList.add('hidden');
        }

        function addNewItem(listId) {
            const itemsList = document.getElementById(listId);
            if (itemsList.children.length >= 3) {
                alert('Maximum of 3 items allowed');
                return;
            }

            const itemDiv = `
                <div class="flex gap-2">
                    <select name="items[]" class="w-2/3 p-2 border border-gray-300 rounded">
                        <?php
                        $inventory = mysqli_query($con, "SELECT * FROM inventory WHERE id NOT IN (SELECT id FROM archive_inventory)");
                        while($item = mysqli_fetch_assoc($inventory)) {
                            echo "<option value='".$item['id']."|".$item['item']."'>".$item['item']."</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="item_qty[]" min="1" value="1" class="w-1/4 p-2 border border-gray-300 rounded">
                    <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 rounded">×</button>
                </div>
            `;
            itemsList.insertAdjacentHTML('beforeend', itemDiv);
        }

        function removeItem(button) {
            button.parentElement.remove();
        }
    </script>

    <script>
        // Add modal functions
        function showAddModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addProductModal').classList.add('hidden');
            document.getElementById('addProductForm').reset();
        }

        // Add form submission handler
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate number of items
            const itemsCount = document.querySelectorAll('#addItemsList > div').length;
            if (itemsCount > 3) {
                alert('Maximum of 3 items allowed');
                return;
            }

            const formData = new FormData(this);
            
            // Collect items and quantities
            const items = [];
            const itemSelects = this.querySelectorAll('select[name="items[]"]');
            const itemQtys = this.querySelectorAll('input[name="item_qty[]"]');
            
            itemSelects.forEach((select, index) => {
                const [id, name] = select.value.split('|');
                items.push({
                    id: parseInt(id),
                    name: name,
                    quantity: parseInt(itemQtys[index].value)
                });
            });
            
            formData.append('required_items', JSON.stringify(items));
            
            fetch('../endpoint/add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddModal();
                    location.reload();
                } else {
                    alert('Error adding product: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product');
            });
        });
    </script>
</body>

</html>