<?php
session_start();
include "../conn/connection.php";

$query = "SELECT * FROM products";
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
    <title>Sold Items</title>
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
            <h1 class="text-2xl font-bold mb-4">Sold Items</h1>
            <label for="month" class="block mt-2 text-sm font-medium text-gray-700">Select Month</label>
<input 
    type="month" 
    name="month" 
    id="month" 
    value="<?php echo $month ?>" 
    class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm text-gray-900"
/>
        </div>

        <div class="mb-6">
            <input type="text" placeholder="Search products..."
                class="min-w-full max-w-xs px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-[#C2A47E]">
        </div>

        <div class="space-y-6">
            <div class="overflow-x-auto rounded-md">
                <h2 class="text-xl font-bold mb-4">Sold Items List</h2>
                <table class="min-w-full bg-white border-4 border-black rounded-md">
                    <thead class="bg-[#C2A47E] text-black">
                        <tr>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Product</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Sales</th>
                            <th class="py-3 px-6 text-left border-r border-[#A88B68]">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                         ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>


    <script>
    </script>
</body>

</html>