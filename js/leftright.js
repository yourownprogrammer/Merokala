const sections = document.querySelectorAll(".product-section");

sections.forEach(section => {
  const products = section.querySelectorAll(".product-card");
  const visibleCount = 4;
  let startIndex = 0;

  function updateProducts() {
    products.forEach((p, i) => {
      p.style.display = (i >= startIndex && i < startIndex + visibleCount) ? "flex" : "none";
    });
  }

  updateProducts();

  section.querySelector(".next").addEventListener("click", () => {
    if (startIndex + visibleCount < products.length) {
      startIndex += visibleCount;
      updateProducts();
    }
  });

  section.querySelector(".prev").addEventListener("click", () => {
    if (startIndex - visibleCount >= 0) {
      startIndex -= visibleCount;
      updateProducts();
    }
  });
});
