<?php
session_start();
include "../conn/connection.php";

// Add this function at the top of the file
function generateOrderNumber($con)
{
    $today = date('Ymd');

    // Get the last order number for today
    $query = "SELECT order_number 
             FROM orders 
             WHERE DATE(date_created) = CURDATE()
             ORDER BY order_number DESC 
             LIMIT 1";

    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        // Extract sequence number and increment
        $lastNumber = substr($row['order_number'], -3);
        $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        // First order of the day
        $sequence = '001';
    }

    return $today . '-' . $sequence;
}

// Get order number
$orderNumber = generateOrderNumber($con);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-[#FFF0DC]">
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>

    <main class=" mt-[171px] p-4">
        <div class="grid grid-cols-2 gap-4">
            <!-- Order List Section -->
            <div class="col-span-1">
                <div class="bg-[#FFF0DC] rounded-lg shadow-md p-4 border-2 border-[#C2A47E]">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-bold">Order List</h2>
                        <a href="../dashboard/admin_dashboard.php" class="bg-[#543A14] hover:bg-[#C2A47E] text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                    </div>
                    <form id="manage-order" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 bg-[#543A14]">
                            <div class="bg-[#543A14] p-3 rounded flex items-center">
                                <label class="text-sm font-medium text-white text-center w-24">Order No.</label>
                                <input type="text" name="order_number"
                                    value="<?php echo $orderNumber; ?>"
                                    class="bg-gray-100 border border-gray-300 rounded px-3 py-2"
                                    readonly>
                            </div>
                            <div class="bg-[#543A14] p-3 rounded flex items-center">
                                <label class="text-sm font-medium text-white text-center w-24">Customer</label>
                                <select name="customer_id" class="flex-1 p-2 text-sm border rounded">
                                    <option value="">Select Customer</option>
                                    <?php
                                    $customer_query = "SELECT * FROM customers WHERE id NOT IN (SELECT id FROM archive_customers)";
                                    $customer_result = mysqli_query($con, $customer_query);
                                    while ($customer = mysqli_fetch_assoc($customer_result)):
                                    ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="overflow-y-auto h-[calc(100vh-450px)]" id="order-items">
                            <table class="w-full text-sm border-2 border-black">
                                <thead class="bg-[#C2A47E] text-white sticky top-0">
                                    <tr>
                                        <th class="p-2 border border-black text-center">Qty</th>
                                        <th class="p-2 border border-black text-center">Item</th>
                                        <th class="p-2 border border-black text-center">Amount</th>
                                        <th class="p-2 border border-black text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="order-list" class="divide-y divide-black">
                                </tbody>
                            </table>
                        </div>
                        <div class="bg-[#543A14] p-4 rounded-b">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-bold text-white">Total:</span>
                                <span class="text-lg font-bold text-white" id="total-amount">₱0.00</span>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" id="pay-btn" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-[#543A14] px-6 py-2 text-sm rounded-md">
                                    Pay
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Combined Categories and Products Section -->
            <div class="col-span-1">
                <div class="bg-[#FFF0DC] rounded-lg shadow-md p-4 border-2 border-[#C2A47E]">
                    <div class="grid grid-cols-5 gap-4">
                        <!-- Categories Column -->
                        <div class="col-span-2 border-r border-[#C2A47E] pr-4">
                            <h2 class="text-lg font-bold mb-3">Categories</h2>
                            <div class="flex flex-col space-y-2 overflow-y-auto h-[calc(100vh-450px)]">
                                <button class="category-btn bg-[#543A14] hover:bg-[#C2A47E] text-white p-3 rounded text-left text-sm"
                                    data-category="all">All Categories</button>
                                <?php
                                $cat_query = "SELECT * FROM categories WHERE id NOT IN (SELECT id FROM archive_categories)";
                                $cat_result = mysqli_query($con, $cat_query);
                                while ($category = mysqli_fetch_assoc($cat_result)):
                                ?>
                                    <button class="category-btn bg-[#543A14] hover:bg-[#C2A47E] text-white p-3 rounded text-left text-sm"
                                        data-category="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </button>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- Products Column -->
                        <div class="col-span-3">
                            <h2 class="text-lg font-bold mb-3">Products</h2>
                            <div class="h-[calc(100vh-280px)] overflow-y-auto pr-2">
                                <div class="grid grid-cols-2 gap-4">
                                    <?php
                                    $query = "SELECT p.*, c.category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id NOT IN (SELECT id FROM archive_products)";
                                    $result = mysqli_query($con, $query);
                                    while ($product = mysqli_fetch_assoc($result)):
                                    ?>
                                        <div class="product-item w-[200px] h-[120px] cursor-pointer bg-[#543A14] hover:bg-[#C2A47E] text-white rounded-lg p-3"
                                            onclick="addToOrder(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                            data-category="<?php echo $product['category_id']; ?>">

                                            <div class="h-full flex flex-col justify-between">
                                                <div>
                                                    <h3 class="font-bold text-sm truncate mb-1">
                                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                                    </h3>
                                                    <p class="text-xs text-gray-300 truncate">
                                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-lg font-bold mb-1">
                                                        ₱<?php echo number_format($product['price'], 2); ?>
                                                    </p>
                                                    <p class="text-xs <?php echo $product['quantity'] > 0 ? 'text-green-300' : 'text-red-300'; ?>">
                                                        <?php echo $product['quantity'] > 0 ? "Stock: " . $product['quantity'] : "Out of Stock"; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Payment Modal -->
    <div id="payment-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Payment Details</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Amount Due</label>
                    <input type="text" id="amount-due" class="w-full p-2 border rounded" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium">Amount Tendered</label>
                    <input type="number" id="amount-tendered" class="w-full p-2 border rounded" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium">Change</label>
                    <input type="text" id="change-amount" class="w-full p-2 border rounded" readonly>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closePaymentModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Cancel
                    </button>
                    <button type="button" onclick="processPayment()" class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white px-4 py-2 rounded">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add your JavaScript code here
        let orderItems = [];
        let total = 0;

        // Product click handler
        $('.product-item').click(function() {
            const product = JSON.parse($(this).attr('data-json'));
            addToOrder(product);
        });

        function addToOrder(product) {
            // Check if product already exists in order
            const existingItem = orderItems.find(item => item.id === product.id);

            // Get current stock from product data
            const availableStock = parseInt(product.quantity);

            if (availableStock <= 0) {
                alert(`Sorry, ${product.product_name} is out of stock!`);
                return;
            }

            if (existingItem) {
                // Check if adding one more exceeds available stock
                if (existingItem.quantity >= availableStock) {
                    alert(`Sorry, only ${availableStock} ${product.product_name}(s) available in stock!`);
                    return;
                }
                existingItem.quantity++;
            } else {
                orderItems.push({
                    id: product.id,
                    name: product.product_name,
                    price: product.price,
                    quantity: 1,
                    maxStock: availableStock
                });
            }

            updateOrderDisplay();
        }

        function updateOrderDisplay() {
            const tbody = $('#order-list');
            tbody.empty();
            total = 0;

            orderItems.forEach((item, index) => {
                const amount = item.price * item.quantity;
                total += amount;

                tbody.append(`
                    <tr>
                        <td class="p-2 border border-black text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <button type="button" onclick="decreaseQuantity(${index})" class="text-red-500">-</button>
                                <span>${item.quantity}</span>
                                <button type="button" onclick="increaseQuantity(${index})" class="text-green-500">+</button>
                            </div>
                        </td>
                        <td class="p-2 border border-black text-center">${item.name}</td>
                        <td class="p-2 border border-black text-center">₱${amount.toFixed(2)}</td>
                        <td class="p-2 border border-black text-center">
                            <button type="button" onclick="removeItem(${index})" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            $('#total-amount').text(`₱${total.toFixed(2)}`);
        }

        // Add quantity control functions
        function increaseQuantity(index) {
            const item = orderItems[index];
            if (item.quantity >= item.maxStock) {
                alert(`Sorry, only ${item.maxStock} ${item.name}(s) available in stock!`);
                return;
            }
            item.quantity++;
            updateOrderDisplay();
        }

        function decreaseQuantity(index) {
            if (orderItems[index].quantity > 1) {
                orderItems[index].quantity--;
                updateOrderDisplay();
            }
        }

        function removeItem(index) {
            orderItems.splice(index, 1);
            updateOrderDisplay();
        }

        // Payment modal functions
        $('#pay-btn').click(function() {
            if (orderItems.length === 0) {
                alert('Please add items to the order first');
                return;
            }

            $('#amount-due').val(`₱${total.toFixed(2)}`);
            $('#payment-modal').removeClass('hidden');
        });

        function closePaymentModal() {
            $('#payment-modal').addClass('hidden');
            $('#amount-tendered').val('');
            $('#change-amount').val('');
        }

        $('#amount-tendered').on('input', function() {
            const tendered = parseFloat($(this).val()) || 0;
            const change = tendered - total;
            $('#change-amount').val(`₱${change.toFixed(2)}`);
        });

        // Update the processPayment function in your script tag

        // Update processPayment validation
        function processPayment() {
            const tendered = parseFloat($('#amount-tendered').val()) || 0;
            const orderNumber = $('input[name="order_number"]').val();

            if (!orderNumber) {
                alert('Please enter order number');
                return;
            }

            if (tendered < total) {
                alert('Insufficient amount');
                return;
            }

            // Check stock availability for all items
            let stockError = false;
            orderItems.forEach(item => {
                if (item.quantity > item.maxStock) {
                    alert(`Sorry, only ${item.maxStock} ${item.name}(s) available in stock!`);
                    stockError = true;
                }
            });

            if (stockError) return;

            // Format data for submission
            const formData = {
                customer_id: $('select[name="customer_id"]').val() || null, // Make customer optional
                order_number: orderNumber,
                order_items: JSON.stringify(orderItems),
                total_amount: total,
                amount_tendered: tendered
            };

            $.ajax({
                url: '../endpoint/save_order.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        alert('Order completed successfully');
                        orderItems = [];
                        updateOrderDisplay();
                        closePaymentModal();
                        // Optional: Redirect or refresh
                        window.location.reload();
                    } else {
                        alert(response.message || 'Error processing order');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                    alert('Error processing order. Please check console for details.');
                }
            });
        }

        // Category filter functionality
        $('.category-btn').click(function() {
            $('.category-btn').removeClass('bg-[#C2A47E]').addClass('bg-[#543A14]');
            $(this).removeClass('bg-[#543A14]').addClass('bg-[#C2A47E]');

            const categoryId = $(this).data('category');

            if (categoryId === 'all') {
                $('.product-item').show();
            } else {
                $('.product-item').hide();
                $(`.product-item[data-category="${categoryId}"]`).show();
            }
        });
    </script>
</body>

</html>