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
                    <img src="<?php echo (!empty($slide['thumbnail_path'])) ? '../assets/thumbnail/' . htmlspecialchars($slide['thumbnail_path']) : 'https://via.placeholder.com/1200x400'; ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>">
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
<script>
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