let currentSlideIndex = 0;
const slides = document.querySelectorAll('.carousel-item');
const dots = document.querySelectorAll('.dot');

function showSlide(index) {
    if (index >= slides.length) currentSlideIndex = 0;
    if (index < 0) currentSlideIndex = slides.length - 1;

    slides.forEach((slide, i) => {
        slide.style.transform = `translateX(-${currentSlideIndex * 100}%)`;
        dots[i].classList.remove('active');
    });

    dots[currentSlideIndex].classList.add('active');
}

function nextSlide() {
    currentSlideIndex++;
    showSlide(currentSlideIndex);
}

function currentSlide(index) {
    currentSlideIndex = index - 1;
    showSlide(currentSlideIndex);
}

// Cambiar de imagen cada 5 segundos
setInterval(nextSlide, 5000);
