    <?php
    session_start();
    include "../conn/connection.php";

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

    // Verify role from database
    $user_id = $_SESSION['user_id'];
    $query = "SELECT role FROM user_db WHERE user_id = ? AND (role = 'admin' OR role = 'employee')";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Debug connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch sold items data
    $query = "SELECT 
        sold.id,
        products.product_name,
        SUM(sold.qty) AS total_qty,
        SUM(sold.qty * products.price) AS total_sales,
        DATE_FORMAT(sold.date, '%M %d, %Y') as formatted_date
    FROM sold
    JOIN products ON sold.product_id = products.id
    GROUP BY sold.product_id, sold.date
    ORDER BY sold.date DESC";

    $result = mysqli_query($con, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($con) . "\nQuery: " . $query);
    }

    // Initialize total sales
    $total_sales = 0;

    // Get all rows
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
        $total_sales += floatval($row['total_sales']);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sold Items</title>
        <link rel="stylesheet" href="../src/output.css">
    </head>

    <body class="bg-[#FFF0DC]">
        <?php include '../features/component/topbar.php'; ?>
        <?php include '../features/component/sidebar.php'; ?>

        <main class="ml-[230px] mt-[171px] p-6">
            <h1 class="text-2xl font-bold mb-4">Sold Items</h1>

            <div class="w-full overflow-x-auto rounded-md">
                <table class="w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Date</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Product</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Quantity Sold</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Total Sales</th>
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
                                        <?php echo htmlspecialchars($row['product_name']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        <?php echo number_format($row['total_qty']); ?>
                                    </td>
                                    <td class="py-4 px-6 border-r border-black">
                                        ₱<?php echo number_format($row['total_sales'], 2); ?>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="py-4 px-6 text-center">No sold items found</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#FFF0DC]">
                            <td colspan="3" class="py-3 px-6 text-right border-r border-black font-bold">Total Sales:</td>
                            <td class="py-3 px-6 border-r border-black font-bold">
                                ₱<?php echo number_format($total_sales, 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </main>
    </body>

    </html>