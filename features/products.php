<?php
session_start();
include "../conn/connection.php";

// RBAC Check
if (
    !isset($_SESSION['user_id']) || !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')
) {
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

// Add pagination setup
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count without modifying main query
$countQuery = "SELECT COUNT(*) as total FROM products";
$countResult = mysqli_query($con, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($total / $limit);

$query = "SELECT * FROM products LIMIT $limit OFFSET $offset";
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
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search product..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <table id="productTable" class="min-w-full bg-white border-4 border-black rounded-md">
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
                            ORDER BY p.id DESC
                            LIMIT $limit OFFSET $offset";

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

        <!-- Add this before closing main div -->
        <div class="flex justify-center items-center mt-4 space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=1" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">First</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?php echo $i; ?>"
                    class="px-4 py-2 rounded <?php echo $i == $page ? 'bg-[#C2A47E] text-white' : 'bg-[#F0BB78] hover:bg-[#C2A47E] text-white'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $totalPages; ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Last</a>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Add New Product</h2>
            <form id="addProductForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" name="product_name" id="add_product_name" maxlength="50"
                        class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category_id" id="add_category_id" required class="w-full p-2 border border-gray-300 rounded">
                        <?php
                        $categories = mysqli_query($con, "SELECT * FROM categories WHERE id NOT IN (SELECT id FROM archive_categories)");
                        while ($category = mysqli_fetch_assoc($categories)) {
                            echo "<option value='" . $category['id'] . "'>" . $category['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="text" name="price" id="add_price"
                        class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="text" name="quantity" id="add_quantity"
                        class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Required Items</label>
                    <div id="addItemsList" class="space-y-2">
                        <div class="flex gap-2">
                            <select name="items[]" class="w-2/3 p-2 border border-gray-300 rounded">
                                <?php
                                $inventory = mysqli_query($con, "SELECT * FROM inventory WHERE id NOT IN (SELECT id FROM archive_inventory)");
                                while ($item = mysqli_fetch_assoc($inventory)) {
                                    echo "<option value='" . $item['id'] . "|" . $item['item'] . "'>" . $item['item'] . "</option>";
                                }
                                ?>
                            </select>
                            <input type="number" name="item_qty[]" min="1" value="1" required class="w-1/4 p-2 border border-gray-300 rounded">
                            <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 rounded">×</button>
                        </div>
                    </div>
                    <button type="button" onclick="addNewItem('addItemsList')" class="mt-2 text-blue-600 hover:text-blue-800">
                        + Add Another Item
                    </button>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Edit Product</h2>
            <form id="editProductForm">
                <input type="hidden" id="edit_product_id" name="id">

                <div class="mb-4">
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" name="product_name" id="edit_product_name" maxlength="50"
                        class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category_id" id="edit_category_id" required class="w-full p-2 border border-gray-300 rounded">
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($category = mysqli_fetch_assoc($categories)) {
                            echo "<option value='" . $category['id'] . "'>" . $category['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="text" name="price" id="edit_price"
                        class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Available Quantity</label>
                    <input type="text" id="edit_available_quantity" readonly
                        class="w-full p-2 border border-gray-300 rounded bg-gray-100">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Additional Quantity</label>
                    <input type="text" name="additional_quantity" id="edit_additional_quantity"
                        class="w-full p-2 border border-gray-300 rounded" value="0">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Required Items</label>
                    <div id="editItemsList" class="space-y-2"></div>
                    <button type="button" onclick="addNewItem('editItemsList')"
                        class="mt-2 text-blue-600 hover:text-blue-800">+ Add Another Item</button>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Update Product</button>
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
                                        while ($item = mysqli_fetch_assoc($inventory)) {
                                            echo "<option value='" . $item['id'] . "|" . $item['item'] . "'>" . $item['item'] . "</option>";
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
                        while ($item = mysqli_fetch_assoc($inventory)) {
                            echo "<option value='" . $item['id'] . "|" . $item['item'] . "'>" . $item['item'] . "</option>";
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
        function validateProductName(input) {
            const trimmedValue = input.value.trim();
            const charCount = document.getElementById(input.id + '_count');


            // Validate empty/spaces only
            if (!trimmedValue) {
                input.value = '';
                return false;
            }

            // Truncate if over 50 chars
            if (trimmedValue.length > 50) {
                input.value = trimmedValue.substring(0, 50);
                alert('Product name cannot exceed 50 characters');
            }

            return true;
        }

        // Add validation to both forms
        document.querySelectorAll('#addProductForm, #editProductForm').forEach(form => {
            const productNameInput = form.querySelector('input[name="product_name"]');
            const formId = form.id;

            // Add input validation
            productNameInput.addEventListener('input', function() {
                validateProductName(this);
            });

            // Add form submit validation
            form.addEventListener('submit', function(e) {
                const productName = productNameInput.value.trim();

                if (!productName || productName.length === 0) {
                    e.preventDefault();
                    alert('Product name cannot be empty');
                    return false;
                }

                if (productName.length > 50) {
                    e.preventDefault();
                    alert('Product name cannot exceed 50 characters');
                    return false;
                }
            });
        });

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

    <script>
        function validatePrice(input) {
            let value = input.value;
            const cursorPos = input.selectionStart;

            // Remove non-numeric and non-decimal characters
            value = value.replace(/[^\d.]/g, '');

            // Handle decimal points
            const parts = value.split('.');
            if (parts.length > 2) {
                parts.splice(2); // Remove extra decimal points
            }

            // Handle whole number part (1-9999)
            if (parts[0]) {
                // Remove leading zeros
                parts[0] = parts[0].replace(/^0+/, '');
                // Limit to 4 digits
                if (parts[0].length > 4) {
                    parts[0] = parts[0].slice(0, 4);
                }
                // If empty after removing zeros, set to '0'
                if (!parts[0]) parts[0] = '0';
            }

            // Handle decimal part (max 2 digits)
            if (parts[1]) {
                parts[1] = parts[1].slice(0, 2);
            }

            // Combine parts
            input.value = parts.join('.');

            // Restore cursor position
            input.setSelectionRange(cursorPos, cursorPos);

            // Validate final value
            const numValue = parseFloat(input.value);
            if (numValue > 9999.99) {
                input.value = '9999.99';
                return false;
            }
            if (numValue <= 0) {
                return false;
            }
            return true;
        }

        // Add price validation to forms
        document.querySelectorAll('#addProductForm, #editProductForm').forEach(form => {
            const priceInput = form.querySelector('input[name="price"]');

            // Change input type to text for better decimal handling
            priceInput.type = 'text';
            priceInput.placeholder = '0.00';

            // Add input validation
            priceInput.addEventListener('input', function() {
                validatePrice(this);
            });

            // Format on blur
            priceInput.addEventListener('blur', function() {
                if (this.value && validatePrice(this)) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });

            // Add to existing form submit validation
            form.addEventListener('submit', function(e) {
                // ...existing validation...

                const price = parseFloat(priceInput.value);
                if (!price || price <= 0 || price > 9999.99) {
                    e.preventDefault();
                    alert('Price must be between 0.01 and 9999.99');
                    return false;
                }
            });
        });
    </script>

    <script>
        function validateQuantity(input) {
            // Store cursor position
            const cursorPos = input.selectionStart;

            // Remove non-numeric characters
            let value = input.value.replace(/[^\d]/g, '');

            // Remove leading zeros
            value = value.replace(/^0+/, '');

            // Limit to 3 digits
            if (value.length > 3) {
                value = value.slice(0, 3);
            }

            // Ensure value is between 1-999
            const numValue = parseInt(value);
            if (numValue > 999) {
                value = '999';
            } else if (numValue < 1) {
                value = '';
            }

            // Update input value
            input.value = value;

            // Restore cursor position
            input.setSelectionRange(cursorPos, cursorPos);

            return value !== '';
        }

        // Add quantity validation to forms
        document.querySelectorAll('#addProductForm, #editProductForm').forEach(form => {
            const qtyInput = form.querySelector('input[name="quantity"], input[name="additional_quantity"]');

            if (qtyInput) {
                qtyInput.type = 'text';
                qtyInput.placeholder = 'Enter quantity (1-999)';

                qtyInput.addEventListener('input', function() {
                    validateQuantity(this);
                });

                // Add to form submit validation
                form.addEventListener('submit', function(e) {
                    const qty = parseInt(qtyInput.value);
                    if (!qty || qty < 1 || qty > 999) {
                        e.preventDefault();
                        alert('Quantity must be between 1 and 999');
                        return false;
                    }
                });
            }
        });
    </script>

    <script>
    function searchTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("productTable");
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