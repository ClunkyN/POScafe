<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
    <link rel="stylesheet" href="../src/output.css">
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
        <div class="bg-[#FFF0DC] p-6 max-h-[600px] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6 text-center">Add New Item</h2>
            <?php
            include "../conn/connection.php";

            // Fetch categories
            $categories_query = "SELECT * FROM categories ORDER BY category_name ASC";
            $categories_result = mysqli_query($con, $categories_query);

            // Check if the query failed
            if (!$categories_result) {
                echo "<p class='text-red-500'>Error fetching categories: " . mysqli_error($con) . "</p>";
            }
            ?>
            <form action="save_inventory.php" method="POST" class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Item Name</label>
                    <input type="text" name="item" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Quantity</label>
                    <input type="number" name="qty" min="1" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <!-- Uncomment the following section if you need category selection -->
                <!--
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category" required class="w-full p-2 border border-gray-300 rounded">
                        <option value="" disabled selected>Select a category</option>
                        <?php
                        if (mysqli_num_rows($categories_result) > 0) {
                            while ($category = mysqli_fetch_assoc($categories_result)) {
                                echo "<option value='" . htmlspecialchars($category['id']) . "'>" . htmlspecialchars($category['category_name']) . "</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No categories available</option>";
                        }
                        ?>
                    </select>
                </div>
                -->

                <!-- Uncomment the following section for additional item-related inputs -->
                <!--
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Large Cups</label>
                    <input type="number" name="large_cups" class="w-full p-2 border border-gray-300 rounded">
                </div>
                -->

                <!-- Availability Status -->
                <!--
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Availability Status</label>
                    <button type="button"
                        id="availabilityBtn"
                        onclick="toggleAvailability()"
                        class="w-24 py-2 px-4 rounded transition-colors duration-300 bg-[#F0BB78] text-white">
                        Unavailable
                    </button>
                    <input type="hidden" name="is_available" id="availabilityStatus" value="0">
                </div>
                -->

                <div class="pt-8">
                    <button type="submit"
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white py-2 px-6 rounded">
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>
        // Toggle availability button behavior
        function toggleAvailability() {
            const btn = document.getElementById('availabilityBtn');
            const status = document.getElementById('availabilityStatus');

            if (btn.classList.contains('bg-[#F0BB78]')) {
                btn.classList.remove('bg-[#F0BB78]');
                btn.classList.add('bg-[#C2A47E]');
                btn.textContent = 'Available';
                status.value = '1';
            } else {
                btn.classList.remove('bg-[#C2A47E]');
                btn.classList.add('bg-[#F0BB78]');
                btn.textContent = 'Unavailable';
                status.value = '0';
            }
        }
    </script>
</body>

</html>
