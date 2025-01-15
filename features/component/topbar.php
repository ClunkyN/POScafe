<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../conn/connection.php";

// Get username from database
$user_id = $_SESSION['user_id'];
$query = "SELECT user_name FROM user_db WHERE user_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Default username if query fails
$username = "User";

if ($result && $user = mysqli_fetch_assoc($result)) {
    $username = $user['user_name'];
}
?>

<div class="fixed top-0 right-0 h-[171px] w-[1920px] bg-[#C2A47E] shadow-md flex justify-between items-center px-8">
    <!-- Logo Section -->
    <div class="flex items-center">
        <img src="../img/header_logo.svg" alt="Cafe Logo" class="object-cover">
    </div>

    <!-- User Profile Dropdown -->
    <div class="relative" id="userDropdown">
        <button onclick="toggleDropdown()" class="flex items-center space-x-3 bg-[#F2DBBE] hover:bg-gray-100 rounded-full p-2">
            <img src="../assets/header_logo.svg" alt="Cafe Logo" class="h-12 w-12 rounded-full object-cover">
            <div class="text-left">
                <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($username); ?></p>
                <p class="text-sm text-gray-600">Admin</p>
            </div>
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-[#F2DBBE] rounded-md shadow-lg py-1">
            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
            <hr class="my-1">
            <a href="../endpoint/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</a>
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