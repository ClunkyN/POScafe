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

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM orders";
$countResult = mysqli_query($con, $countQuery);
$total_records = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($total_records / $limit);

// Modify main query to include pagination
$query = "SELECT 
    id,
    customer,
    ref_no,
    total_amount,
    amount_tendered,
    order_number,
    DATE_FORMAT(date_created, '%M %d, %Y') as formatted_date
FROM orders 
ORDER BY date_created DESC
LIMIT $limit OFFSET $offset";

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

// Function to clean data for PDF
function cleanData($str) {
    return str_replace(["'", '"', "\n", "\r"], ['&#39;', '&quot;', ' ', ' '], trim($str));
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        // Initialize when document loads
        document.addEventListener('DOMContentLoaded', function() {
            window.jsPDF = window.jspdf.jsPDF;
            
            // Make function globally available
            window.generatePDF = function() {
                try {
                    const doc = new jsPDF();

                    // Add title
                    doc.setFontSize(18);
                    doc.text('Orders Report', 14, 20);

                    // Add date
                    doc.setFontSize(11);
                    doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 30);

                    // Table data
                    const tableData = {
                        head: [['Date', 'Reference No.', 'Order No.', 'Amount']],
                        body: [
                            <?php
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "['" . 
                                    cleanData($row['formatted_date']) . "','" . 
                                    cleanData($row['ref_no']) . "','" . 
                                    cleanData($row['order_number']) . "','₱" . 
                                    number_format($row['total_amount'], 2) . 
                                "'],";
                            }
                            ?>
                        ]
                    };

                    // Generate table
                    doc.autoTable({
                        head: tableData.head,
                        body: tableData.body,
                        startY: 35,
                        theme: 'grid',
                        styles: {
                            fontSize: 9,
                            cellPadding: 3,
                            overflow: 'linebreak',
                            halign: 'left'
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
                        }
                    });

                    // Add total
                    const finalY = doc.lastAutoTable.finalY || 35;
                    doc.setFontSize(11);
                    doc.text('Total Sales: ₱<?php echo number_format($total, 2); ?>', 14, finalY + 10);

                    // Save PDF
                    doc.save('orders-report.pdf');
                } catch (error) {
                    console.error('PDF Generation failed:', error);
                    alert('Failed to generate PDF. Please try again.');
                }
            }
        });
    </script>
</head>

<body class="bg-[#FFF0DC]">
    <?php include '../features/component/topbar.php'; ?>
    <?php include '../features/component/sidebar.php'; ?>

    <main class="ml-[230px] mt-[171px] p-6">
        <h1 class="text-2xl font-bold mb-4">Orders</h1>

        <div class="mb-4">
            <button onclick="generatePDF()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">
                Download PDF
            </button>
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
                <tfoot>
                    <tr class="bg-[#FFF0DC]">
                        <td colspan="3" class="py-3 px-6 text-right border-r border-black font-bold">Total:</td>
                        <td class="py-3 px-6 border-r border-black font-bold">
                            ₱<?php echo number_format($total, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

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
        </div>
    </main>

  
</body>
</html>