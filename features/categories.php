<?php
session_start();
include "../conn/connection.php";

$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM categories";
$countResult = mysqli_query($con, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($total / $limit);

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

$query = "SELECT c.*, CASE WHEN ac.id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
          FROM categories c 
          LEFT JOIN archive_categories ac ON c.id = ac.id
          LIMIT $limit OFFSET $offset";
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add user ID to global scope for session monitor
        const userId = '<?php echo $_SESSION['user_id']; ?>';
    </script>
    <script src="../js/sessionMonitor.js"></script>
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
            <div class="flex items-center justify-between">
                <button onclick="showAddModal()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-4 rounded">
                    Add Category
                </button>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../features/archive_categories_table.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-archive mr-2"></i>View Archived Categories</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-6">
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search Categories..." class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <table id="categoryTable" class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Category Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Description</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $query = "SELECT c.*, CASE WHEN ac.id IS NOT NULL THEN 1 ELSE 0 END as is_archived 
                                 FROM categories c 
                                 LEFT JOIN archive_categories ac ON c.id = ac.id";
                        $result = mysqli_query($con, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $rowClass = $row['is_archived'] ? 'bg-gray-200 text-gray-600' : 'hover:bg-gray-50';
                        ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['category_name']; ?></td>
                                    <td class="py-4 px-6 border-r border-black"><?php echo $row['description']; ?></td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center gap-2">
                                            <?php if (!$row['is_archived']) { ?>
                                                <button onclick="editCategory(<?php echo $row['id']; ?>)"
                                                    class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                    Edit
                                                </button>
                                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <button onclick="archiveCategory(<?php echo $row['id']; ?>)"
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
        <div class="flex justify-center items-center mt-4 space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=1" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">First</a>
                <a href="?page=<?php echo $page - 1; ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Previous</a>
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
                <a href="?page=<?php echo $page + 1; ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Next</a>
                <a href="?page=<?php echo $totalPages; ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Last</a>
            <?php endif; ?>
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
                    <input type="text" id="category_name" maxlength="20" name="category_name" required
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
                            alert('Error deleting category');
                        }
                    });
            }
        }

        function archiveCategory(id) {
            if (confirm('Are you sure you want to archive this category?')) {
                fetch('../endpoint/archive_category.php', {
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
                            window.location.href = '../features/archive_categories_table.php';
                        } else {
                            alert('Error archiving category: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error archiving category');
                    });
            }
        }

        function unarchiveCategory(id) {
            if (confirm('Are you sure you want to unarchive this category?')) {
                fetch('../endpoint/unarchive_category.php', {
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
                            alert('Error unarchiving category');
                        }
                    });
            }
        }
    </script>
    <script>
    // Validation functions
    function validateCategoryName(input) {
        const trimmedValue = input.value.trim();
        
        // Check empty/spaces
        if (!trimmedValue) {
            input.value = '';
            return false;
        }
        
        // Check length and truncate if over 20 chars
        if (trimmedValue.length > 20) {
            input.value = trimmedValue.substring(0, 20);
            alert('Category name cannot exceed 20 characters');
        }
        
        return true;
    }

    function validateDescription(input) {
        const trimmedValue = input.value.trim();
        
        // Check empty/spaces
        if (!trimmedValue) {
            input.value = '';
            return false;
        }
        
        // Check length and truncate if over 50 chars
        if (trimmedValue.length > 50) {
            input.value = trimmedValue.substring(0, 50);
            alert('Description cannot exceed 50 characters');
        }
        
        return true;
    }

    // Add validation to both forms
    document.querySelectorAll('#addCategoryForm, #editCategoryForm').forEach(form => {
        // Category name validation
        const nameInput = form.querySelector('input[name="category_name"]');
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                validateCategoryName(this);
            });
        }
        
        // Description validation
        const descInput = form.querySelector('textarea[name="description"]');
        if (descInput) {
            descInput.addEventListener('input', function() {
                validateDescription(this);
            });
        }

        // Form submission validation
        form.addEventListener('submit', function(e) {
            const categoryName = nameInput.value.trim();
            const description = descInput.value.trim();
            
            if (!categoryName || categoryName.length === 0) {
                e.preventDefault();
                alert('Category name cannot be empty');
                return false;
            }
            
            if (categoryName.length > 20) {
                e.preventDefault();
                alert('Category name cannot exceed 20 characters');
                return false;
            }

            if (description.length > 50) {
                e.preventDefault();
                alert('Description cannot exceed 50 characters');
                return false;
            }
        });
    });

    // Add character counter displays
    function updateCharCount(input, counterId) {
        const counter = document.getElementById(counterId);
        const length = input.value.trim().length;
        const maxLength = input.hasAttribute('maxlength') ? input.getAttribute('maxlength') : 20;
        counter.textContent = `(${length}/${maxLength})`;
        counter.style.color = length > maxLength ? 'red' : '#6B7280';
    }
    </script>

<script>
    function searchTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("categoryTable");
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