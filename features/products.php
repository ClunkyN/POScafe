<?php
session_start();
include "../conn/connection.php";

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
                <a class="" href="../features/archive_products_table.php">archived products</a>
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
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Product Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Category</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Price</th>
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
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="editProduct(<?php echo $row['id']; ?>)"
                                                class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                Edit
                                            </button>
                                            <button onclick="archiveProduct(<?php echo $row['id']; ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                                Archive
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='py-4 px-6 text-center'>No products found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Product Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Product</h2>
            <form id="productForm" class="space-y-4">
                <input type="hidden" id="product_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" id="product_name" name="product_name" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Category</label>
                    <select id="category_id" name="category_id" required class="w-full p-2 border border-gray-300 rounded">
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
                    <input type="number" id="price" name="price" step="0.01" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.remove('hidden');
        }

        function editProduct(id) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            fetch(`../endpoint/get_product.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('product_id').value = data.id;
                    document.getElementById('product_name').value = data.product_name;
                    document.getElementById('category_id').value = data.category_id;
                    document.getElementById('price').value = data.price;
                    document.getElementById('productModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isAdd = !formData.get('id');

            fetch(`../endpoint/${isAdd ? 'add_product' : 'update_product'}.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error saving product');
                    }
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
    </script>
</body>

</html>