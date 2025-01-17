<?php
session_start();
include "../conn/connection.php";

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Get orders for current month
$query = "SELECT * FROM orders 
          WHERE DATE_FORMAT(date_created,'%Y-%m') = ? 
          ORDER BY date_created DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $month);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Calculate total
$total = 0;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total += $row['total_amount'];
    }
    mysqli_data_seek($result, 0);
}
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
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>

    <main class="ml-[230px] mt-[171px] p-6">
        <div class="flex flex-col justify-between items-start mb-6">
            <h1 class="text-2xl font-bold mb-4">Orders</h1>
            
            <!-- Month Selector -->
            <div class="w-full max-w-xs mb-4">
                <label for="month" class="block text-sm font-medium text-gray-700">Select Month</label>
                <input 
                    type="month" 
                    name="month" 
                    id="month" 
                    value="<?php echo $month ?>"
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                >
            </div>

            <!-- Search Box -->
            <div class="w-full mb-6">
                <input type="text" 
                    id="searchInput" 
                    placeholder="Search orders..." 
                    class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]"
                >
            </div>

            <!-- Orders Table -->
            <div class="w-full overflow-x-auto rounded-md">
                <table class="w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Date</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Reference No.</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Order No.</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo date("M d, Y", strtotime($row['date_created'])) ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($row['ref_no']) ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo htmlspecialchars($row['order_number']) ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        ₱<?php echo number_format($row['total_amount'], 2) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 px-6 text-center text-gray-500">
                                    No orders found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <th colspan="3" class="py-3 px-6 text-right border-r border-black">Total:</th>
                            <th class="py-3 px-6 text-left border-r border-black">
                                ₱<?php echo number_format($total, 2) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Month selector handler
        document.getElementById('month').addEventListener('change', function() {
            window.location.href = 'order.php?month=' + this.value;
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toUpperCase();
            const table = document.querySelector('table tbody');
            const rows = table.getElementsByTagName('tr');

            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let cell of cells) {
                    const text = cell.textContent || cell.innerText;
                    if (text.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        });
    </script>
</body>
</html>