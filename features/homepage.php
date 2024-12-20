<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="bg-[#F2DBBE]">
    <header class="bg-[#C2A47E] h-[150px] z-20 flex justify-between items-center">
        <div class="flex justify-start pl-8">
            <img src="../assets/header_logo.svg" alt="logo" class="h-[100px] w-[100px]">
        </div>
        <div class="flex justify-end items-center px-6 gap-4 pr-10">
            <ol class="px-6 text-2xl font-bold text-[#F2DBBE]"><a href="#">About Us</a></ol>
            <button onclick="window.location.href='login.php'" class="bg-[#F2DBBE] h-[52px] w-[150px] rounded-xl text-2xl font-bold">
                Login
            </button>
        </div>
    </header>
    <main class="max-h-screen grid grid-cols-2 gap-4 p-4">
        <!-- Left Column -->
        <div class="flex flex-col justify-center pl-8 mt-12">
            <div class="pb-8">
                <h1 class="text-6xl font-bold">GOOD</h1>
                <p class="text-4xl font-bold">ideas start with brainstorming...</p>
            </div>
            <div>
                <h1 class="text-6xl font-bold">GREAT</h1>
                <p class="text-2xl font-bold">ideas start with a coffee...</p>
            </div>
        </div>
        
        <!-- Right Column - Carousel Placeholder -->
        <div class="flex items-center justify-center pt-[133px] pr-8">
            <div class="w-full h-[400px] bg-gray-200 rounded-lg">
                <p class="text-center pt-8">Carousel</p>
            </div>
        </div>
    </main>
</body>
</html>