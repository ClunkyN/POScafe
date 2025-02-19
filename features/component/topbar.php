<?php
include "../conn/connection.php";


try {
    // Get username from database
    $query = "SELECT user_name, role FROM user_db WHERE user_id = ?";
    $user_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Default username if query fails
        $username = "User";
        $role = "Guest";

        if ($result && $user = mysqli_fetch_assoc($result)) {
            $username = $user['user_name'];
            $role = ucfirst(strtolower($user['role']));
        }
    }
} catch (Exception $e) {
    error_log("Error in topbar: " . $e->getMessage());
}
?>

<div class="fixed top-0 right-0 h-[171px] w-[1920px] bg-[#C2A47E] shadow-md flex justify-between items-center px-8">
    <!-- Logo Section -->
    <div class="flex items-center">
        <img src="../assets/header_logo.svg" alt="logo" class="h-[100px] w-[100px]">
    </div>

    <!-- User Profile Dropdown -->
    <div class="relative" id="userDropdown">
        <button onclick="toggleDropdown()" class="flex items-center space-x-3 bg-[#F2DBBE] hover:bg-gray-100 rounded-full p-2">
            <img src="../assets/header_logo.svg" alt="Cafe Logo" class="h-12 w-12 rounded-full object-cover">
            <div class="text-left">
                <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($username); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($role); ?></p>
            </div>
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-[#F2DBBE] rounded-md shadow-lg py-1">
            <a href="../features/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
            <hr class="my-1">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="../endpoint/admin_logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</a>
            <?php else: ?>
                <a href="../endpoint/employee_logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userDropdown = document.getElementById('userDropdown');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (!userDropdown.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
</script>