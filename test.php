<!DOCTYPE html>
<html>
<head>
    <style>
        .logo-carousel {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .logos-container {
            display: flex;
            transition: transform 0.5s ease;
        }
        
        .logo-slide {
            min-width: 100%;
            text-align: center;
            padding: 20px 0;
        }
        
        .logo-slide img {
            max-height: 80px;
            max-width: 80%;
            filter: grayscale(100%);
            transition: filter 0.3s;
        }
        
        .logo-slide img:hover {
            filter: grayscale(0%);
        }
    </style>
</head>
<body>
    <div class="logo-carousel">
        <div class="logos-container">
            <div class="logo-slide">
                <img src="logo1.png" alt="Company 1">
            </div>
            <div class="logo-slide">
                <img src="logo2.png" alt="Company 2">
            </div>
            <div class="logo-slide">
                <img src="logo3.png" alt="Company 3">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.logos-container');
            const slides = document.querySelectorAll('.logo-slide');
            let currentIndex = 0;
            
            function nextSlide() {
                currentIndex = (currentIndex + 1) % slides.length;
                container.style.transform = `translateX(-${currentIndex * 100}%)`;
            }
            
            // Change slide every 3 seconds
            setInterval(nextSlide, 3000);
        });
    </script>
</body>
</html>