<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
            <h2 class="text-2xl font-bold mb-6 text-center">Add New Product</h2>
            <form action="save_product.php" method="POST" class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Category</label>
                    <input type="text" name="category" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Product Name</label>
                    <input type="text" name="prod_name" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" name="price" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>
<!--
                <div>
                    <h3>How many of these items would it take to make this product?</h3>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Large Cups</label>
                    <input type="number" name="price" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Medium Cups</label>
                    <input type="number" name="price" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Small Cups</label>
                    <input type="number" name="price" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Straws</label>
                    <input type="number" name="price" required
                        class="w-full p-2 border border-gray-300 rounded">
                </div>
-->

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