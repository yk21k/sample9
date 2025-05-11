// // 商品データ（例）
// const products = <?php echo $strJsonNorm?>;
// console.log(products);

// // 商品の表示関数
// function displayProducts(sortedProducts) {
//     const productList = document.getElementById('productList');
//     productList.innerHTML = '';

//     sortedProducts.forEach(product => {
//         const card = document.createElement('div');
//         card.classList.add('product-card');
//         card.innerHTML = `
//             <img src="${product.image}" alt="${product.name}">
//             <div class="content">
//                 <h3>${product.name}</h3>
//                 <p>お気に入り数: ${product.favorites}</p>
//                 <p class="price">¥${product.price}</p>
//             </div>
//         `;
//         productList.appendChild(card);
//     });
// }

// // 並べ替え関数
// function sortItems() {
//     const sortBy = document.getElementById('sortBy').value;
    
//     let sortedProducts;
    
//     if (sortBy === 'sales') {
//         sortedProducts = [...products].sort((a, b) => b.sales - a.sales);
//     } else if (sortBy === 'favorites') {
//         sortedProducts = [...products].sort((a, b) => b.favorites - a.favorites);
//     }

//     displayProducts(sortedProducts);
// }

// // 初期表示（売上順で表示）
// window.onload = function() {
//     sortItems();
// };


// document.addEventListener('DOMContentLoaded', function () {
//     // Bladeから渡されたJSONデータ
//     const products = window.productsData;

//     const productList = document.getElementById('productList');
//     const sortBy = document.getElementById('sortBy');

//     if (!productList || !sortBy) {
//         console.warn('productListまたはsortByが見つかりませんでした。');
//         return;
//     }

//     // 商品表示
//     function displayProducts(sortedProducts) {
//         productList.innerHTML = '';

//         sortedProducts.forEach(product => {
//             const card = document.createElement('div');
//             card.classList.add('product-card');
//             card.innerHTML = `
//                 <img src="${product.image}" alt="${product.name}">
//                 <div class="content">
//                     <h3>${product.name}</h3>
//                     <p>お気に入り数: ${product.favorites}</p>
//                     <p class="price">¥${product.price}</p>
//                 </div>
//             `;
//             productList.appendChild(card);
//         });
//     }

//     // 並び替え
//     function sortItems() {
//         let sortedProducts;

//         if (sortBy.value === 'sales') {
//             sortedProducts = [...products].sort((a, b) => b.sales - a.sales);
//         } else if (sortBy.value === 'favorites') {
//             sortedProducts = [...products].sort((a, b) => b.favorites - a.favorites);
//         } else {
//             sortedProducts = [...products];
//         }

//         displayProducts(sortedProducts);
//     }

//     sortBy.addEventListener('change', sortItems);
//     sortItems();
// });

document.addEventListener('DOMContentLoaded', function () {
    // Bladeから渡されたJSONデータをグローバル変数経由で取得
    const products = window.productsData || [];

    // HTML要素がなければ何もしない
    const productList = document.getElementById('productList');
    const sortBy = document.getElementById('sortBy');

    if (!productList || !sortBy) {
        // 要素が存在しない場合は処理せずに終了
        // console.info('[custom6.js] productList または sortBy が存在しないため、商品表示をスキップします。');
        return;
    }

    // 商品表示関数
    function displayProducts(sortedProducts) {
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

    // 並び替え
    function sortItems() {
        let sortedProducts;

        if (sortBy.value === 'sales') {
            sortedProducts = [...products].sort((a, b) => b.sales - a.sales);
        } else if (sortBy.value === 'favorites') {
            sortedProducts = [...products].sort((a, b) => b.favorites - a.favorites);
        } else {
            sortedProducts = [...products];
        }

        displayProducts(sortedProducts);
    }

    // イベント登録
    sortBy.addEventListener('change', sortItems);

    // 初期表示
    sortItems();
});


