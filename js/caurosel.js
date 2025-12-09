
const slides = document.querySelector('.slides');
const slideElements = document.querySelectorAll('.slide');
const slideCount = slideElements.length;
const dotsContainer = document.querySelector('.dots');
let currentIndex = 0;

// Create dots
for (let i = 0; i < slideCount; i++) {
  const dot = document.createElement('span');
  dot.classList.add('dot');
  if (i === 0) dot.classList.add('active');
  dot.addEventListener('click', () => showSlide(i));
  dotsContainer.appendChild(dot);
}
const dots = document.querySelectorAll('.dot');

function showSlide(index) {
  slides.style.transform = `translateX(-${index * 100}%)`;
  dots.forEach(dot => dot.classList.remove('active'));
  dots[index].classList.add('active');
  currentIndex = index;
}

// Next/Prev
document.querySelector('.next').addEventListener('click', () => showSlide((currentIndex + 1) % slideCount));
document.querySelector('.prev').addEventListener('click', () => showSlide((currentIndex - 1 + slideCount) % slideCount));

// Auto-slide
let slideInterval = setInterval(() => showSlide((currentIndex + 1) % slideCount), 5000);

// Pause Button
const pauseBtn = document.querySelector('.pause-btn');
let isPaused = false;
pauseBtn.addEventListener('click', () => {
  isPaused = !isPaused;
  pauseBtn.textContent = isPaused ? '▶' : '⏸';
  if (isPaused) clearInterval(slideInterval);
  else slideInterval = setInterval(() => showSlide((currentIndex + 1) % slideCount), 5000);
});