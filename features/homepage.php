<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="../src/output.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let currentIndex = 0;
            const images = ["../assets/image1.jpg", "../assets/image2.jpg", "../assets/image3.jpg"];
            const carouselContainer = document.getElementById("carousel-container");
            
            images.forEach((src) => {
                let img = document.createElement("img");
                img.src = src;
                img.className = "w-full h-full object-cover flex-shrink-0";
                carouselContainer.appendChild(img);
            });
            
            function updateImage() {
                carouselContainer.style.transition = "transform 0.5s ease-in-out";
                carouselContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
            }
            
            function nextImage() {
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
            }
            
            function prevImage() {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateImage();
            }
            
            document.getElementById("prev").addEventListener("click", prevImage);
            document.getElementById("next").addEventListener("click", nextImage);
            
            setInterval(nextImage, 3000); // Automatically slide every 3 seconds
        });
    </script>
</head>
<body class="bg-[#F2DBBE] flex flex-col items-center min-h-screen">
    <header class="bg-[#C2A47E] w-full h-[150px] flex justify-between items-center px-8">
        <img src="../assets/header_logo.svg" alt="logo" class="h-[100px] w-[100px]">
        <div class="flex items-center gap-4">
            <a href="../features/aboutus.php" class="text-2xl font-bold text-[#F2DBBE]">About Us</a>
            <button onclick="window.location.href='../features/employee_login.php'" class="bg-[#F2DBBE] h-[52px] w-[150px] rounded-xl text-2xl font-bold">
                Login
            </button>
        </div>
    </header>
    <main class="w-full max-w-6xl p-4 flex flex-col md:flex-row items-center gap-8">
        <div class="flex-1 text-center md:text-left p-4 order-1 md:order-none">
            <h1 class="text-6xl font-bold">GOOD</h1>
            <p class="text-4xl font-bold">ideas start with brainstorming...</p>
            <h1 class="text-6xl font-bold mt-8">GREAT</h1>
            <p class="text-2xl font-bold">ideas start with a coffee...</p>
        </div>
        <div class="relative w-[400px] h-[400px] bg-gray-200 rounded-lg overflow-hidden shadow-lg flex-shrink-0 order-none md:order-1">
            <div id="carousel-container" class="flex w-full h-full transition-transform duration-500 ease-in-out">
            </div>
            <button id="prev" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white px-3 py-2 rounded-full shadow text-black">&#9665;</button>
            <button id="next" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white px-3 py-2 rounded-full shadow text-black">&#9655;</button>
        </div>
    </main>
</body>
</html>
