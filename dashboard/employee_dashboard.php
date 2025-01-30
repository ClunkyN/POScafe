<?php
session_start();
include "../conn/connection.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and has employee role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    session_unset();
    session_destroy();
    header("Location: ../features/homepage.php");
    exit();
}

// Double check employee role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND role = 'employee'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../features/homepage.php");
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

// Fetch the total number of categories
$query_categories = "SELECT COUNT(*) AS categories_count FROM categories";
$result_categories = mysqli_query($con, $query_categories);
$data_categories = mysqli_fetch_assoc($result_categories);
$categories_count = $data_categories['categories_count'] ?? 0;

// Fetch the total number of items in inventory
$query_items = "SELECT COUNT(*) AS items_count FROM inventory";
$result_items = mysqli_query($con, $query_items);
$data_items = mysqli_fetch_assoc($result_items);
$items_count = $data_items['items_count'] ?? 0;

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
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="../src/output.css">
    <script>
        // Prevent going back
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, document.URL);
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Add user ID to global scope for session monitor
        const userId = '<?php echo $_SESSION['user_id']; ?>';
    </script>
    <script src="../js/sessionMonitor.js"></script>
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
        <div class="flex justify-center items-center space-x-16 mt-8">
            <!-- No. of Orders -->
            <div class="bg-[#FFF0DC] p-8 rounded-lg border border-[#A88B68] w-96 h-60 flex flex-col justify-center items-center">
                <h2 class="text-xl font-bold text-[#A88B68]">Number of Orders</h2>
                <p class="text-4xl text-[#A88B68] font-semibold"><?php echo $orders_today; ?></p>
            </div>
            <!-- Categories Section -->
            <div class="bg-[#FFF0DC] p-8 rounded-lg border border-[#A88B68] w-96 h-60 flex flex-col justify-center items-center">
                <h2 class="text-xl font-bold text-[#A88B68]">Number of Categories</h2>
                <p class="text-4xl text-[#A88B68] font-semibold"><?php echo $categories_count; ?></p>
            </div>
            <!-- Items Section -->
            <div class="bg-[#FFF0DC] p-8 rounded-lg border border-[#A88B68] w-96 h-60 flex flex-col justify-center items-center">
                <h2 class="text-xl font-bold text-[#A88B68]">Number of Items</h2>
                <p class="text-4xl text-[#A88B68] font-semibold"><?php echo $items_count; ?></p>
            </div>
        </div>


        <!-- Birthdays and Calendar Section -->
        <div class="grid grid-cols-2 gap-4 mt-4">
            <!-- Birthdays List -->
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68] mt-4">
                <h2 class="text-lg font-bold text-[#A88B68] mb-2">Birthdays This Month</h2>
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
            <div class="bg-[#FFF0DC] p-4 rounded-lg border border-[#A88B68] mt-4">
                <h2 class="text-lg font-bold text-[#A88B68]">Calendar</h2>
                <div id="calendar" class="h-[300px] overflow-hidden"></div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
    <script>
        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach ($upcoming_birthdays as $birthday): ?> {
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