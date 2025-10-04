<?php
// --- FETCH DATA FOR THE SLIDER ---
$slider_sql = "
    (SELECT 
        id, title, description, thumbnail_path, 'movie' as item_type 
    FROM movies ORDER BY id DESC LIMIT 3)
    UNION ALL
    (SELECT 
        id, title, description, thumbnail_path, 'series' as item_type 
    FROM series ORDER BY id DESC LIMIT 3)
    ORDER BY id DESC
    LIMIT 3;
";
$slider_result = mysqli_query($conn, $slider_sql);
$slides = [];
if ($slider_result) {
    while ($row = mysqli_fetch_assoc($slider_result)) {
        $slides[] = $row;
    }
}
?>

<div class="animehub-slider">
    <div class="slider-container">
        <?php if (!empty($slides)): ?>
            <?php foreach ($slides as $slide): ?>
                <div class="slide">
                    <img src="<?php echo (!empty($slide['thumbnail_path'])) ? 'assets/thumbnail/' . htmlspecialchars($slide['thumbnail_path']) : 'https://via.placeholder.com/1200x400'; ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                    <div class="slide-content">
                        <h3><?php echo htmlspecialchars($slide['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($slide['description'], 0, 100)) . '...'; ?></p>
                        <a href="anime_info.php?id=<?php echo $slide['id']; ?>&type=<?php echo $slide['item_type']; ?>" class="btn-primary">Watch Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="slide">
                <img src="https://via.placeholder.com/1200x400" alt="Welcome">
                <div class="slide-content">
                    <h3>Welcome to AnimeGo</h3>
                    <p>Add new movies and series to feature them here.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($slides) && count($slides) > 1): ?>
    <div class="slider-nav">
        <button class="nav-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
        <div class="slide-dots">
            <?php foreach ($slides as $index => $slide): ?>
                <span class="dot <?php echo ($index == 0) ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
            <?php endforeach; ?>
        </div>
        <button class="nav-btn next-btn"><i class="fas fa-chevron-right"></i></button>
    </div>
    <?php endif; ?>
</div>

<style>
/* --- CORRECTED SLIDER STYLES --- */
.animehub-slider {
    position: relative;
    width: 100%;
    margin: 20px 0;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.slider-container {
    display: flex; /* This is required for the sliding effect */
    transition: transform 0.5s ease-in-out; /* This animates the slide */
    height: 630px;
}

.slide {
    min-width: 100%; /* Each slide takes up the full width */
    flex-shrink: 0;  /* Prevents slides from shrinking */
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

.btn-primary {
    background-color: #ff6b6b;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
    margin-top: 10px;
}

.slide-content h3 { font-size: 28px; margin-bottom: 10px; color: #ff6b6b; }
.slide-content p { font-size: 16px; margin-bottom: 15px; max-width: 600px; }
.slider-nav { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; gap: 15px; z-index: 10; }
.nav-btn { background-color: rgba(255, 107, 107, 0.8); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 0.3s; }
.nav-btn:hover { background-color: #ff5252; }
.slide-dots { display: flex; gap: 8px; }
.dot { width: 12px; height: 12px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.5); cursor: pointer; transition: background-color 0.3s; }
.dot.active { background-color: #ff6b6b; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .slider-container { height: 300px; }
    .slide-content { padding: 20px; }
    .slide-content h3 { font-size: 22px; }
    .slide-content p { font-size: 14px; }
}
@media (max-width: 480px) {
    .slider-container { height: 250px; }
    .slide-content h3 { font-size: 18px; }
    .slide-content p { display: none; }
    .nav-btn { width: 35px; height: 35px; }
}
</style>

<script>
/* --- CORRECTED SLIDER SCRIPT --- */
document.addEventListener('DOMContentLoaded', function() {
    const sliderWrapper = document.querySelector('.animehub-slider');
    if (!sliderWrapper) return;

    const slider = sliderWrapper.querySelector('.slider-container');
    const slides = sliderWrapper.querySelectorAll('.slide');
    const dots = sliderWrapper.querySelectorAll('.dot');
    const prevBtn = sliderWrapper.querySelector('.prev-btn');
    const nextBtn = sliderWrapper.querySelector('.next-btn');
    
    if (slides.length <= 1) return;

    let currentSlide = 0;
    let slideInterval;
    
    function goToSlide(index) {
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }
        
        slider.style.transform = `translateX(-${index * 100}%)`;
        
        dots.forEach(d => d.classList.remove('active'));
        dots[index].classList.add('active');
        
        currentSlide = index;
    }
    
    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }
    
    function startSlider() {
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    function stopSlider() {
        clearInterval(slideInterval);
    }
    
    nextBtn.addEventListener('click', () => { stopSlider(); nextSlide(); startSlider(); });
    prevBtn.addEventListener('click', () => { stopSlider(); prevSlide(); startSlider(); });
    
    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-slide'));
            stopSlider();
            goToSlide(slideIndex);
            startSlider();
        });
    });
    
    sliderWrapper.addEventListener('mouseenter', stopSlider);
    sliderWrapper.addEventListener('mouseleave', startSlider);
    
    startSlider();
});
</script>