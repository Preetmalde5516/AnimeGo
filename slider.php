<!-- AnimeHub Slider -->
<div class="animehub-slider">
    <div class="slider-container">
        <div class="slide active">
            <img src="https://via.placeholder.com/1200x400" alt="Featured Anime 1">
            <div class="slide-content">
                <h3>Attack on Titan: Final Season</h3>
                <p>The epic conclusion to the battle between humanity and the Titans</p>
                <a href="#" class="btn btn-primary">Watch Now</a>
            </div>
        </div>
        <div class="slide">
            <img src="https://via.placeholder.com/1200x400" alt="Featured Anime 2">
            <div class="slide-content">
                <h3>Demon Slayer: Entertainment District Arc</h3>
                <p>Tanjiro and his friends join the Sound Hashira Tengen Uzui</p>
                <a href="#" class="btn btn-primary">Watch Now </a>
            </div>
        </div>
        <div class="slide">
            <img src="https://via.placeholder.com/1200x400" alt="Featured Anime 3">
            <div class="slide-content">
                <h3>Jujutsu Kaisen Season 2</h3>
                <p>The prequel arc continues with Gojo's past and the Shibuya Incident</p>
                <a href="#" class="btn btn-primary">Watch Now</a>
            </div>
        </div>
    </div>
    <div class="slider-nav">
        <button class="nav-btn prev-btn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="slide-dots">
            <span class="dot active" data-slide="0"></span>
            <span class="dot" data-slide="1"></span>
            <span class="dot" data-slide="2"></span>
        </div>
        <button class="nav-btn next-btn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<style>
/* AnimeHub Slider Styles */
.animehub-slider {
    position: relative;
    width: 100%;
    margin: 20px 0;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.slider-container {
    display: flex;
    transition: transform 0.5s ease;
    height: 400px;
}

.slide {
    min-width: 100%;
    position: relative;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.slide-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 30px;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    color: white;
}

.slide-content h3 {
    font-size: 28px;
    margin-bottom: 10px;
    color: #ff6b6b;
}

.slide-content p {
    font-size: 16px;
    margin-bottom: 15px;
    max-width: 600px;
}

.slider-nav {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 10;
}

.nav-btn {
    background-color: rgba(255, 107, 107, 0.8);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.nav-btn:hover {
    background-color: #ff5252;
}

.slide-dots {
    display: flex;
    gap: 8px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: background-color 0.3s;
}

.dot.active {
    background-color: #ff6b6b;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .slider-container {
        height: 300px;
    }
    
    .slide-content {
        padding: 20px;
    }
    
    .slide-content h3 {
        font-size: 22px;
    }
    
    .slide-content p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .slider-container {
        height: 250px;
    }
    
    .slide-content h3 {
        font-size: 18px;
    }
    
    .slide-content p {
        display: none;
    }
    
    .nav-btn {
        width: 35px;
        height: 35px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider-container');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    let currentSlide = 0;
    let slideInterval;
    
    // Function to move to a specific slide
    function goToSlide(index) {
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }
        
        slider.style.transform = `translateX(-${index * 100}%)`;
        
        // Update active classes
        document.querySelector('.slide.active').classList.remove('active');
        document.querySelector('.dot.active').classList.remove('active');
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        
        currentSlide = index;
    }
    
    // Next slide function
    function nextSlide() {
        goToSlide(currentSlide + 1);
    }
    
    // Previous slide function
    function prevSlide() {
        goToSlide(currentSlide - 1);
    }
    
    // Start auto sliding
    function startSlider() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }
    
    // Stop auto sliding
    function stopSlider() {
        clearInterval(slideInterval);
    }
    
    // Event listeners for navigation
    nextBtn.addEventListener('click', function() {
        stopSlider();
        nextSlide();
        startSlider();
    });
    
    prevBtn.addEventListener('click', function() {
        stopSlider();
        prevSlide();
        startSlider();
    });
    
    // Event listeners for dots
    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-slide'));
            stopSlider();
            goToSlide(slideIndex);
            startSlider();
        });
    });
    
    // Pause slider when hovering
    slider.addEventListener('mouseenter', stopSlider);
    slider.addEventListener('mouseleave', startSlider);
    
    // Initialize the slider
    startSlider();
});
</script>