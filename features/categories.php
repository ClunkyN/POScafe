<?php
session_start();
include "../conn/connection.php";

$query = "SELECT * FROM categories";
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
    <title>Categories</title>
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
            <h1 class="text-2xl font-bold mb-4">Categories</h1>
            <button onclick="showAddModal()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                Add Category
            </button>
        </div>

        <div class="mb-6">
            <input type="text" placeholder="Search categories..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="overflow-x-auto rounded-md">
            <table class="min-w-full bg-white border-4 border-black rounded-md">
                <thead class="bg-[#C2A47E] text-black">
                    <tr>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Category Name</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Description</th>
                        <th class="py-3 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 px-6 border-r border-black"><?php echo $row['category_name']; ?></td>
                            <td class="py-4 px-6 border-r border-black"><?php echo $row['description']; ?></td>
                            <td class="py-4 px-6">
                                <div class="flex justify-center gap-2">
                                    <button onclick="editCategory(<?php echo $row['id']; ?>)"
                                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                        Edit
                                    </button>
                                    <button onclick="(<?php echo $row['id']; ?>)"
                                        class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                        Archive
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' class='py-4 px-6 text-center'>No categories found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 id="modalTitle" class="text-xl font-bold mb-4">Edit Category</h2>
            <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="category_id" name="id">

                <div>
                    <label class="block text-sm font-medium">Category Name</label>
                    <input type="text" id="category_name" name="category_name" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div>
                    <label class="block text-sm font-medium">Description</label>
                    <textarea 
                        id="category_description" 
                        name="description" 
                        rows="3"
                        maxlength="30"
                        style="resize: none;"
                        class="w-full p-2 border border-gray-300 rounded"
                        placeholder="Maximum 30 characters"></textarea>
                    <small class="text-gray-500">Character limit: 30</small>
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
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function editCategory(id) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            fetch(`../endpoint/get_category.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('category_id').value = data.id;
                    document.getElementById('category_name').value = data.category_name;
                    document.getElementById('category_description').value = data.description;
                    document.getElementById('categoryModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }

        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isAdd = !formData.get('id');

            fetch(`../endpoint/${isAdd ? 'add_category' : 'update_category'}.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert('Error saving category');
                }
            });
        });

        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                fetch('../endpoint/delete_category.php', {
                    method: 'POST',
                    body: JSON.stringify({ id: id }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting category');
                    }
                });
            }
        }
    </script>
</body>
</html>