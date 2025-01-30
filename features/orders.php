<?php
session_start();
include "../conn/connection.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add after session_start() and includes
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Debug connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

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

// Get all orders for the date range (no pagination)
$allOrdersQuery = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC";

$allOrdersStmt = mysqli_prepare($con, $allOrdersQuery);
mysqli_stmt_bind_param($allOrdersStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($allOrdersStmt);
$allOrdersResult = mysqli_stmt_get_result($allOrdersStmt);

// Calculate total for all orders in range
$totalAllOrders = 0;
while ($row = mysqli_fetch_assoc($allOrdersResult)) {
    $totalAllOrders += $row['total_amount'];
}
mysqli_data_seek($allOrdersResult, 0); // Reset pointer

// Verify role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND (role = 'admin' OR role = 'employee')";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Get date range parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Default to today

// Ensure start_date is not greater than end_date
if ($startDate > $endDate) {
    echo "<script>alert('Error: Start date cannot be later than end date.'); window.history.back();</script>";
    exit; // Stop further execution
}


// Modify main query to include date range
$query = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    amount_tendered,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC
LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ssii", $startDate, $endDate, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query failed: " . mysqli_error($con) . "\nQuery: " . $query);
}

// Update count query to include date range
$countQuery = "SELECT COUNT(*) as total FROM orders WHERE DATE(date_created) BETWEEN ? AND ?";
$countStmt = mysqli_prepare($con, $countQuery);
mysqli_stmt_bind_param($countStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($countStmt);
$total_records = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'];
$totalPages = ceil($total_records / $limit);

// Initialize total
$total = 0;

// Get all rows
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    $total += floatval($row['total_amount']);
}

// After the pagination query, add this new query for PDF data:

// Fetch ALL orders data for PDF (without pagination) 
$pdfQuery = "SELECT 
    id,
    customer, 
    ref_no,
    total_amount,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC";

$pdfStmt = mysqli_prepare($con, $pdfQuery);
mysqli_stmt_bind_param($pdfStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($pdfStmt);
$pdfResult = mysqli_stmt_get_result($pdfStmt);

// Calculate total for all orders in date range
$totalPdfAmount = 0;
while ($row = mysqli_fetch_assoc($pdfResult)) {
    $totalPdfAmount += $row['total_amount'];
}
mysqli_data_seek($pdfResult, 0);

// Add after your existing pagination query:

// Fetch ALL orders for PDF (without pagination)
$pdfQuery = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC";

$pdfStmt = mysqli_prepare($con, $pdfQuery);
mysqli_stmt_bind_param($pdfStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($pdfStmt);
$pdfResult = mysqli_stmt_get_result($pdfStmt);

// Calculate total for all orders
$totalPdfAmount = 0;
$totalPdfOrders = 0;
while ($row = mysqli_fetch_assoc($pdfResult)) {
    $totalPdfAmount += $row['total_amount'];
    $totalPdfOrders++;
}
mysqli_data_seek($pdfResult, 0); // Reset pointer

// Add after existing pagination query:

// Fetch ALL orders for PDF (without pagination)
$pdfQuery = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC";

$pdfStmt = mysqli_prepare($con, $pdfQuery);
mysqli_stmt_bind_param($pdfStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($pdfStmt);
$pdfResult = mysqli_stmt_get_result($pdfStmt);

// Calculate totals for PDF
$totalPdfAmount = 0;
$totalPdfOrders = 0;
while ($row = mysqli_fetch_assoc($pdfResult)) {
    $totalPdfAmount += $row['total_amount'];
    $totalPdfOrders++;
}
mysqli_data_seek($pdfResult, 0); // Reset pointer

// Function to clean data for PDF
function cleanData($str)
{
    return str_replace(["'", '"', "\n", "\r"], ['&#39;', '&quot;', ' ', ' '], trim($str));
}

// Reset result pointer
mysqli_data_seek($result, 0);

// Add after your database connection:
$pdfQuery = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
WHERE DATE(date_created) BETWEEN ? AND ?
ORDER BY date_created DESC";

$pdfStmt = mysqli_prepare($con, $pdfQuery);
mysqli_stmt_bind_param($pdfStmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($pdfStmt);
$pdfResult = mysqli_stmt_get_result($pdfStmt);

// Calculate totals
$totalAmount = 0;
$totalOrders = mysqli_num_rows($pdfResult);
while($row = mysqli_fetch_assoc($pdfResult)) {
    $totalAmount += $row['total_amount'];
}
mysqli_data_seek($pdfResult, 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="../src/output.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Add user ID to global scope for session monitor
        const userId = '<?php echo $_SESSION['user_id']; ?>';
    </script>
    <script src="../js/sessionMonitor.js"></script>
    <script>
        // Replace the entire generatePDF function:

        window.generatePDF = function() {
            try {
                const {jsPDF} = window.jspdf;
                const doc = new jsPDF();

                // Get date range
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                // Table data
                const tableData = {
                    head: [['Date', 'Reference No.', 'Order No.', 'Amount']],
                    body: [
                        <?php
                        while ($row = mysqli_fetch_assoc($pdfResult)) {
                            echo "['" . 
                                cleanData($row['formatted_date']) . "','" . 
                                cleanData($row['ref_no']) . "','" . 
                                cleanData($row['order_number']) . "','PHP " . 
                                number_format($row['total_amount'], 2) . 
                            "'],";
                        }
                        ?>
                    ]
                };

                let firstPage = true;
                
                // Generate table
                doc.autoTable({
                    head: tableData.head,
                    body: tableData.body,
                    startY: 40,
                    theme: 'grid',
                    styles: {
                        fontSize: 9,
                        cellPadding: 3
                    },
                    headStyles: {
                        fillColor: [194, 164, 126],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold'
                    },
                    columnStyles: {
                        0: {cellWidth: 40},
                        1: {cellWidth: 40},
                        2: {cellWidth: 40},
                        3: {cellWidth: 40}
                    },
                    didDrawPage: function(data) {
                        if (firstPage) {
                            // Header only on first page
                            doc.setFontSize(20);
                            doc.text('ZEFMAVEN COMPUTER PARTS AND ACCESSORIES', 
                                doc.internal.pageSize.getWidth() / 2, 15, {
                                    align: 'center'
                            });
                            
                            doc.setFontSize(14);
                            doc.text(`Orders Report (${startDate} to ${endDate})`,
                                doc.internal.pageSize.getWidth() / 2, 25, {
                                    align: 'center'
                            });
                            firstPage = false;
                        }
                        
                        // Page number on all pages
                        doc.setFontSize(10);
                        doc.text(`Page ${data.pageNumber}`, data.settings.margin.left,
                            doc.internal.pageSize.height - 10);
                    },
                    didDrawCell: function(data) {
                        // Right align amount column
                        if (data.column.index === 3) {
                            data.cell.styles.halign = 'left';
                        }
                    }
                });

                // Add totals at the bottom of last page
                const finalY = doc.lastAutoTable.finalY || 40;
                doc.setFontSize(11);
                doc.text(`Total Number of Orders: <?php echo $totalOrders; ?>`, 14, finalY + 10);
                doc.text(`Total Amount: PHP <?php echo number_format($totalAmount, 2); ?>`, 14, finalY + 20);

                // Save PDF
                doc.save(`Orders_Report_${startDate}_to_${endDate}.pdf`);
            } catch (error) {
                console.error('PDF Generation failed:', error);
                alert('Failed to generate PDF. Please try again.');
            }
        }
    </script>
    <script>
    function validateDates() {
        let startDate = document.getElementById("start_date").value;
        let endDate = document.getElementById("end_date").value;

        if (startDate > endDate) {
            alert("Error: Start date cannot be later than end date.");
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }
</script>
</head>

<body class="bg-[#FFF0DC]">
    <?php include '../features/component/topbar.php'; ?>
    <?php include '../features/component/sidebar.php'; ?>

    <main class="ml-[230px] mt-[171px] p-6">
        <h1 class="text-2xl font-bold mb-4">Orders</h1>

<!-- Date Range Form -->
<div class="mb-6">
    <form method="GET" action="orders.php" class="mb-2 space-y-2 sm:space-y-4" onsubmit="return validateDates();">
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
            <input type="date" id="start_date" name="start_date"
                class="w-full sm:w-auto px-3 py-2 text-sm border rounded-lg"
                value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-01'); ?>"
                max="<?php echo date('Y-m-d'); ?>">
            <input type="date" id="end_date" name="end_date"
                class="w-full sm:w-auto px-3 py-2 text-sm border rounded-lg"
                value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-d'); ?>"
                max="<?php echo date('Y-m-d'); ?>">
            <button type="submit"
                class="w-full sm:w-auto bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded-lg">
                View Orders
            </button>
        </div>
    </form>
</div>

        <div class="flex justify-between items-center mb-4">
            <button onclick="generatePDF()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">
                Download PDF
            </button>
            <div class="text-lg font-semibold">
                Total Orders: <?php echo $totalPdfOrders; ?> |
                Total Amount: ₱<?php echo number_format($totalPdfAmount, 2); ?>
            </div>
        </div>

        <div class="w-full overflow-x-auto rounded-md">
            <table class="w-full bg-white border-4 border-black rounded-md" id="ordersTable">
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
                <!--
                <tfoot>
                    <tr class="bg-[#FFF0DC]">
                        <td colspan="3" class="py-3 px-6 text-right border-r border-black font-bold">Total:</td>
                        <td class="py-3 px-6 border-r border-black font-bold">
                            ₱<?php echo number_format($total, 2); ?>
                        </td>
                    </tr>
                </tfoot>
                -->
            </table>

            <div class="flex justify-center items-center mt-4 space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=1&start_date=<?php echo htmlspecialchars($startDate); ?>&end_date=<?php echo htmlspecialchars($endDate); ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">First</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&start_date=<?php echo htmlspecialchars($startDate); ?>&end_date=<?php echo htmlspecialchars($endDate); ?>"
                        class="px-4 py-2 rounded <?php echo $i == $page ? 'bg-[#C2A47E] text-white' : 'bg-[#F0BB78] hover:bg-[#C2A47E] text-white'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $totalPages; ?>&start_date=<?php echo htmlspecialchars($startDate); ?>&end_date=<?php echo htmlspecialchars($endDate); ?>" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">Last</a>
                <?php endif; ?>
            </div>
        </div>
    </main>


</body>

</html>