const dropdown = document.querySelector('.dropdown');
const dropbtn = document.querySelector('.dropbtn');
const overlay = document.getElementById('overlay');

dropbtn.addEventListener('click', e => {
  e.preventDefault();
  dropdown.classList.toggle('active');
  overlay.style.display = dropdown.classList.contains('active') ? 'block' : 'none';
});

document.addEventListener('click', e => {
  if (!dropdown.contains(e.target) && !dropbtn.contains(e.target)) {
    dropdown.classList.remove('active');
    overlay.style.display = 'none';
  }
});

overlay.addEventListener('click', () => {
  dropdown.classList.remove('active');
  overlay.style.display = 'none';
});