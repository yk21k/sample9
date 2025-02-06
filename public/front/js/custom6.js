// 商品データ（例）
const products = <?php echo $strJsonNorm?>;
console.log(products);

// 商品の表示関数
function displayProducts(sortedProducts) {
    const productList = document.getElementById('productList');
    productList.innerHTML = '';

    sortedProducts.forEach(product => {
        const card = document.createElement('div');
        card.classList.add('product-card');
        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <div class="content">
                <h3>${product.name}</h3>
                <p>お気に入り数: ${product.favorites}</p>
                <p class="price">¥${product.price}</p>
            </div>
        `;
        productList.appendChild(card);
    });
}

// 並べ替え関数
function sortItems() {
    const sortBy = document.getElementById('sortBy').value;
    
    let sortedProducts;
    
    if (sortBy === 'sales') {
        sortedProducts = [...products].sort((a, b) => b.sales - a.sales);
    } else if (sortBy === 'favorites') {
        sortedProducts = [...products].sort((a, b) => b.favorites - a.favorites);
    }

    displayProducts(sortedProducts);
}

// 初期表示（売上順で表示）
window.onload = function() {
    sortItems();
};
