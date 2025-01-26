<?php
session_start();
include "../conn/connection.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Debug query to count orders
$count_query = "SELECT COUNT(*) as count FROM orders";
$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);

// Main query to fetch orders
$query = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    amount_tendered,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
ORDER BY date_created DESC";

$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con) . "\nQuery: " . $query);
}

// Initialize total
$total = 0;

// Get all rows
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    $total += floatval($row['total_amount']);
}

// Reset result pointer
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="../src/output.css">
</head>

<body class="bg-[#FFF0DC]">
    <?php include '../features/component/topbar.php'; ?>
    <?php include '../features/component/sidebar.php'; ?>

    <main class="ml-[230px] mt-[171px] p-6">
        <h1 class="text-2xl font-bold mb-4">Orders</h1>

        <div class="w-full overflow-x-auto rounded-md">
            <table class="w-full bg-white border-4 border-black rounded-md">
                <thead class="bg-[#C2A47E] text-black">
                    <tr>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Date</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Reference No.</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Order No.</th>
                        <th class="py-3 px-6 text-left border-r border-[#A88B68]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($rows)) {
                        foreach ($rows as $row) {
                    ?>
                        <tr class="hover:bg-gray-50 bg-[#FFF0DC]">
                            <td class="py-4 px-6 border-r border-black">
                                <?php echo htmlspecialchars($row['formatted_date']); ?>
                            </td>
                            <td class="py-4 px-6 border-r border-black">
                                <?php echo htmlspecialchars($row['ref_no']); ?>
                            </td>
                            <td class="py-4 px-6 border-r border-black">
                                <?php echo htmlspecialchars($row['order_number']); ?>
                            </td>
                            <td class="py-4 px-6 border-r border-black">
                                ₱<?php echo number_format($row['total_amount'], 2); ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="4" class="py-4 px-6 text-center">No orders found</td>
                        </tr>
                    <?php 
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="bg-[#FFF0DC]">
                        <td colspan="3" class="py-3 px-6 text-right border-r border-black font-bold">Total:</td>
                        <td class="py-3 px-6 border-r border-black font-bold">
                            ₱<?php echo number_format($total, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
</body>
</html>
