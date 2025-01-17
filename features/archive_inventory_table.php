<?php
session_start();
include('../conn/connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM archive_inventory";
    $countResult = mysqli_query($con, $countQuery);
    if (!$countResult) {
        throw new Exception(mysqli_error($con));
    }
    $total = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($total / $limit);

    // Get archived inventory items
    $query = "SELECT 
        ai.id,
        ai.item,
        ai.qty,
        ai.archived_at
    FROM archive_inventory ai
    ORDER BY ai.archived_at DESC
    LIMIT ?, ?";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($con));
        }
        
        $archivedItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        throw new Exception("Prepare failed: " . mysqli_error($con));
    }

} catch(Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Failed to fetch archived items: " . $e->getMessage();
    $archivedItems = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Inventory</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#FFF0DC]">
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>

    <main class="ml-[230px] mt-[171px] p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Archived Inventory</h2>
                <a href="inventory.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Item</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Quantity</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($archivedItems && count($archivedItems) > 0): ?>
                            <?php foreach ($archivedItems as $item): ?>
                                <tr id="archived-item-<?php echo $item['id']; ?>" class="hover:bg-gray-50">
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($item['item']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($item['qty']); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center">
                                            <button onclick="restoreItem(<?php echo $item['id']; ?>)"
                                                class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                Restore
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-4 px-6 text-center text-gray-500">
                                    No archived items found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function restoreItem(id) {
            if (confirm('Are you sure you want to restore this item?')) {
                fetch('../endpoint/unarchive_inventory.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../features/inventory.php';
                    } else {
                        alert('Error restoring item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error restoring item');
                });
            }
        }
    </script>
</body>
</html>