
const input = document.getElementById("searchInput");
const results = document.getElementById("searchResults");

input.addEventListener("keyup", function () {
    const query = input.value.trim();

    if (query.length < 2) {
        results.innerHTML = "";
        return;
    }

    fetch("search_live.php?q=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
            results.innerHTML = data;
        });
});

results.addEventListener("click", function (e) {
    if (e.target.classList.contains("suggestion")) {
        const productId = e.target.getAttribute("data-id");
        window.location.href = "product.php?id=" + productId;
    }
});

