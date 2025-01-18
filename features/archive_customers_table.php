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
    $countQuery = "SELECT COUNT(*) as total FROM archive_customers";
    $countResult = mysqli_query($con, $countQuery);
    if (!$countResult) {
        throw new Exception(mysqli_error($con));
    }
    $total = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($total / $limit);

    // Get archived customers
    $query = "SELECT 
        ac.id,
        ac.name,
        ac.birthday,
        ac.orders
    FROM archive_customers ac
    ORDER BY ac.id DESC
    LIMIT ?, ?";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($con));
        }
        
        $archivedCustomers = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        throw new Exception("Prepare failed: " . mysqli_error($con));
    }

} catch(Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Failed to fetch archived customers: " . $e->getMessage();
    $archivedCustomers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Customers</title>
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
                <h2 class="text-2xl font-bold">Archived Customers</h2>
                <a href="customers.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Customers
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
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Customer Name</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Birthday</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Orders</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($archivedCustomers && count($archivedCustomers) > 0): ?>
                            <?php foreach ($archivedCustomers as $customer): ?>
                                <tr id="archived-customer-<?php echo $customer['id']; ?>" class="hover:bg-gray-50">
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($customer['birthday']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($customer['orders']); ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center">
                                            <button onclick="restoreCustomer(<?php echo $customer['id']; ?>)"
                                                class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-1 px-3 rounded">
                                                Restore
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 px-6 text-center text-gray-500">
                                    No archived customers found
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
        function restoreCustomer(id) {
            if (confirm('Are you sure you want to restore this customer?')) {
                fetch('../endpoint/unarchive_customer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../features/customers.php';
                    } else {
                        alert('Error restoring customer');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error restoring customer');
                });
            }
        }
    </script>
</body>
</html>
