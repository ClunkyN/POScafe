php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <!-- Content here -->
    </main>
</body>
</html>