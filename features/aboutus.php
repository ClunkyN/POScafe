<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#F2DBBE] flex flex-col items-center min-h-screen">
    <header class="bg-[#C2A47E] w-full h-[150px] flex justify-between items-center px-8">
        <img src="../assets/header_logo.svg" alt="logo" class="h-[100px] w-[100px]">
        <div class="flex items-center gap-4">
            <a href="../features/homepage.php" class="text-2xl font-bold text-[#F2DBBE]">Home</a>
            <button onclick="window.location.href='../features/employee_login.php'" class="bg-[#F2DBBE] h-[52px] w-[100px] rounded-xl text-2xl font-bold">
                Login
            </button>
        </div>
    </header>
    <main class="w-full max-w-6xl p-8 flex flex-col items-center gap-12">
        <section class="text-center space-y-4">
            <h1 class="text-6xl font-bold mb-6">About Us</h1>
            <p class="text-2xl font-medium text-gray-700 max-w-2xl mx-auto">
                Welcome to our website. We believe in great ideas, collaboration, and the power of coffee.
            </p>
        </section>

        <section class="bg-white rounded-lg shadow-lg p-8 w-full max-w-3xl">
            <div class="grid gap-6">
                <div class="flex items-center gap-4">
                    <i class="fas fa-map-marker-alt text-2xl text-[#C2A47E]"></i>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Location</h3>
                        <p class="text-gray-700">387 Amethyst Street Tiongco Subdivision Barangay Tagapo, Santa Rosa, Philippines, 4026</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <i class="fas fa-phone text-2xl text-[#C2A47E]"></i>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Contact Number</h3>
                        <p class="text-gray-700">0962 827 9896</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <i class="fas fa-envelope text-2xl text-[#C2A47E]"></i>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Email</h3>
                        <p class="text-gray-700">misyaachi@gmail.com</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <i class="fas fa-clock text-2xl text-[#C2A47E]"></i>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Business Hours</h3>
                        <p class="text-gray-700">
                            Monday, Wednesday, Thursday, Saturday, Sunday: 10am - 11pm<br>
                            Tuesday, Friday: 9am - 11pm
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>