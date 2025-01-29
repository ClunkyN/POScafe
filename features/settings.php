<?php
session_start();
include "../conn/connection.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
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
            <h1 class="text-2xl font-bold mb-4">Change Password</h1>
        </div>
        <div class="bg-[#A88B68] p-6 rounded-lg w-[450px] text-white">
            <div class="bg-[#5A3A1B] p-6 rounded-lg">
                <form>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Current Password:</label>
                        <input type="password" class="w-full p-2 border rounded text-black" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">New Password:</label>
                        <input type="password" class="w-full p-2 border rounded text-black" />
                        <p class="text-xs text-gray-300">Leave this blank if you don’t want to change password</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Confirm Password:</label>
                        <input type="password" class="w-full p-2 border rounded text-black" />
                    </div>
                    <button class="bg-[#F0BB78] hover:bg-[#C2A47E]  text-white font-semibold px-4 py-2 rounded w-full">Update Password</button>
                    
                   
                </form>
            </div>
        </div>
    </main>
</body>
</html>
