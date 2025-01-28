<?php
session_start();
include "../conn/connection.php";
include "../endpoint/AdminAuth.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: ../features/admin_login.php");
    exit();
}

// Double check admin role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND role = 'admin'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../features/admin_login.php");
    exit();
}

// Fetch the number of orders made today
$date_today = date('Y-m-d');
$query_orders_today = "SELECT COUNT(*) AS orders_today FROM orders WHERE DATE(date_created) = ?";
$stmt_orders = mysqli_prepare($con, $query_orders_today);
mysqli_stmt_bind_param($stmt_orders, "s", $date_today);
mysqli_stmt_execute($stmt_orders);
$result_orders = mysqli_stmt_get_result($stmt_orders);
$data_orders = mysqli_fetch_assoc($result_orders);
$orders_today = $data_orders['orders_today'] ?? 0;

// Fetch the total revenue for today
$query_revenue_today = "SELECT SUM(total_amount) AS revenue_today FROM orders WHERE DATE(date_created) = ?";
$stmt_revenue = mysqli_prepare($con, $query_revenue_today);
mysqli_stmt_bind_param($stmt_revenue, "s", $date_today);
mysqli_stmt_execute($stmt_revenue);
$result_revenue = mysqli_stmt_get_result($stmt_revenue);
$data_revenue = mysqli_fetch_assoc($result_revenue);
$revenue_today = $data_revenue['revenue_today'] ?? 0.00;

// Fetch sold products data for the chart
$current_month = date('Y-m');
$current_month_name = date('F');
$query_products_sold = "
    SELECT 
        products.product_name,
        SUM(sold.qty) AS total_qty
    FROM sold
    JOIN products ON sold.product_id = products.id
    WHERE DATE_FORMAT(sold.date, '%Y-%m') = ?
    GROUP BY sold.product_id
";
$stmt_products = mysqli_prepare($con, $query_products_sold);
mysqli_stmt_bind_param($stmt_products, "s", $current_month);
mysqli_stmt_execute($stmt_products);
$result_products = mysqli_stmt_get_result($stmt_products);

$product_names = [];
$product_quantities = [];
while ($row = mysqli_fetch_assoc($result_products)) {
    $product_names[] = $row['product_name'];
    $product_quantities[] = $row['total_qty'];
}

// Fetch daily sales for the current month
$query_sales_analytics = "
    SELECT 
        DATE(date_created) AS sale_date,
        SUM(total_amount) AS daily_sales
    FROM orders
    WHERE DATE_FORMAT(date_created, '%Y-%m') = ?
    GROUP BY DATE(date_created)
    ORDER BY sale_date ASC
";
$stmt_sales = mysqli_prepare($con, $query_sales_analytics);
mysqli_stmt_bind_param($stmt_sales, "s", $current_month);
mysqli_stmt_execute($stmt_sales);
$result_sales = mysqli_stmt_get_result($stmt_sales);

$sales_dates = [];
$sales_values = [];
while ($row = mysqli_fetch_assoc($result_sales)) {
    $sales_dates[] = $row['sale_date'];
    $sales_values[] = $row['daily_sales'];
}

// Fetch upcoming birthdays for the current month
$query_upcoming_birthdays = "
    SELECT 
        id, name, DATE_FORMAT(birthday, '%M %d') AS formatted_birthday, DATE(birthday) AS birthday_date
    FROM customers
    WHERE MONTH(birthday) = MONTH(CURDATE())
    ORDER BY DAY(birthday) ASC
";
$result_birthdays = mysqli_query($con, $query_upcoming_birthdays);

$upcoming_birthdays = [];
$current_date = date('Y-m-d');
while ($row = mysqli_fetch_assoc($result_birthdays)) {
    $is_today = $row['birthday_date'] === $current_date;
    $upcoming_birthdays[] = [
        'name' => $row['name'],
        'formatted_birthday' => $row['formatted_birthday'],
        'is_today' => $is_today
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../src/output.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
    <script>
        // Prevent going back
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function () {
            history.pushState(null, null, document.URL);
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet">
</head>
<body class="bg-[#FFF0DC]">
    <!-- Topbar -->
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <!-- Sidebar -->
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>
    <!-- Main content -->
    <main class="ml-[230px] mt-[171px] p-6">
        <div class="grid grid-cols-2 gap-4">
            <!-- No. of Orders -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
                <h2 class="text-lg font-bold text-[#A88B68]">No. of Orders</h2>
                <p class="text-2xl text-[#A88B68] font-semibold"><?php echo $orders_today; ?></p>
            </div>
            <!-- Revenue -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
                <h2 class="text-lg font-bold text-[#A88B68]">Revenue</h2>
                <p class="text-2xl text-[#A88B68] font-semibold">₱<?php echo number_format($revenue_today, 2); ?></p>
            </div>
            <!-- Products Sold Chart -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
                <h2 class="text-lg font-bold text-[#A88B68]">Products Sold</h2>
                <canvas id="productsSoldChart"></canvas>
            </div>
            <!-- Sales Analytics -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
                <h2 class="text-lg font-bold text-[#A88B68]">Sales Analytics</h2>
                <canvas id="salesAnalyticsChart"></canvas>
            </div>
        </div>
        <!-- Birthdays and Calendar Section -->
        <div class="grid grid-cols-2 gap-4 mt-4">
<!-- Birthdays List -->
<div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
    <h2 class="text-lg font-bold text-[#A88B68] mb-2">Birthdays This <?php echo $current_month_name; ?></h2>
    <?php if (!empty($upcoming_birthdays)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($upcoming_birthdays as $birthday): ?>
                <div class="flex items-center space-x-4 p-2 bg-[#FAE6CF] rounded-md shadow-md">
                    <div class="w-10 h-10 flex items-center justify-center bg-[#A88B68] text-white rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#A88B68]">
                            <?php echo $birthday['name']; ?>
                        </p>
                        <p class="text-xs text-[#A88B68] <?php echo $birthday['is_today'] ? 'font-bold' : ''; ?>">
                            <?php echo $birthday['formatted_birthday']; ?>
                            <?php if ($birthday['is_today']): ?>
                                <span class="text-red-500">(Today!)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-[#A88B68]">No Birthdays This Month</p>
    <?php endif; ?>
</div>
            <!-- Calendar Section -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68]">
                <h2 class="text-lg font-bold text-[#A88B68]">Calendar</h2>
                <div id="calendar" class="h-[300px] overflow-hidden"></div>
            </div>
        </div>
    </main>

    <script>
        // Chart Data for Products Sold
        const productNames = <?php echo json_encode($product_names); ?>;
        const productQuantities = <?php echo json_encode($product_quantities); ?>;
        const ctx1 = document.getElementById('productsSoldChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [{
                    label: 'Quantity Sold',
                    data: productQuantities,
                    backgroundColor: 'rgba(168, 139, 104, 0.7)',
                    borderColor: 'rgba(168, 139, 104, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: { enabled: true }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

       // Chart Data for Sales Analytics
       const salesDates = <?php echo json_encode($sales_dates); ?>;
        const salesValues = <?php echo json_encode($sales_values); ?>;

        const ctxSales = document.getElementById('salesAnalyticsChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: salesDates,
                datasets: [{
                    label: 'Daily Sales',
                    data: salesValues,
                    backgroundColor: 'rgba(168, 139, 104, 0.5)',
                    borderColor: 'rgba(168, 139, 104, 1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach ($upcoming_birthdays as $birthday): ?>{
                        title: '<?php echo $birthday['name']; ?>\'s Birthday',
                        start: '<?php echo date('Y-m-d', strtotime($birthday['formatted_birthday'])); ?>',
                        color: '<?php echo $birthday['is_today'] ? "#FF5733" : "#A88B68"; ?>'
                    },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>
