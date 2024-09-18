
// Dom  元画像Box
let baseImgBox1 = document.querySelector(".base_img_box1");

baseImgBox1.onload = setImgClone();

function setImgClone() {
    // Dom コンテント
    let content1 = document.querySelector(".content-detail1");

    // Dom 拡大画像Box(元画像Box のクローン)
    let lensImgBox1 = baseImgBox1.cloneNode(true);
    // クラス名「lens_img_box」に変更
    lensImgBox1.className = "lens_img_box1";

    // Dom 拡大画像(lensImgBoxの子要素)
    let lensImg1 = lensImgBox1.firstElementChild;
    // クラス名「lens_img」に変更
    lensImg1.className = "lens_img1";
    // 二倍に拡大
    lensImg1.style.transform = "scale(2)";

    // 元画像のクローンを配置
    content1.appendChild(lensImgBox1);

    // Dom  元画像
    let baseImg1 = document.querySelector(".base_img1");

    // 拡大比率　今回は2倍
    let ratio = 2;

    // 元画像の上をマウスポインタが通った場合、虫眼鏡を表示
    baseImg1.addEventListener("mousemove", function (event) {

        // 拡大画像Box(虫眼鏡...元画像の座標から100引いた値を設定)
        lensImgBox1.style.opacity = 1;
        lensImgBox1.style.top = (event.offsetY - 100) + "px";
        lensImgBox1.style.left = (event.offsetX - 100) + "px";

        //　拡大画像 (元画像の座標から反転して100足した値を設定)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg1.style.top = (newLensOffsetY) + "px";
        lensImg1.style.left = (newLensOffsetX) + "px";

    }, false);

    // 元画像の上にマウスポインタがない場合、虫眼鏡を非表示
    baseImg1.addEventListener("mouseout", function () {

        lensImgBox1.style.opacity = 0;

    }, false);
}



// Dom  元画像Box
let baseImgBox2 = document.querySelector(".base_img_box2");

baseImgBox2.onload = setImgClone2();

function setImgClone2() {
    // Dom コンテント
    let content2 = document.querySelector(".content-detail2");

    // Dom 拡大画像Box(元画像Box のクローン)
    let lensImgBox2 = baseImgBox2.cloneNode(true);
    // クラス名「lens_img_box」に変更
    lensImgBox2.className = "lens_img_box2";

    // Dom 拡大画像(lensImgBoxの子要素)
    let lensImg2 = lensImgBox2.firstElementChild;
    // クラス名「lens_img」に変更
    lensImg2.className = "lens_img2";
    // 二倍に拡大
    lensImg2.style.transform = "scale(2)";

    // 元画像のクローンを配置
    content2.appendChild(lensImgBox2);

    // Dom  元画像
    let baseImg2 = document.querySelector(".base_img2");

    // 拡大比率　今回は2倍
    let ratio = 2;

    // 元画像の上をマウスポインタが通った場合、虫眼鏡を表示
    baseImg2.addEventListener("mousemove", function (event) {

        // 拡大画像Box(虫眼鏡...元画像の座標から100引いた値を設定)
        lensImgBox2.style.opacity = 1;
        lensImgBox2.style.top = (event.offsetY - 100) + "px";
        lensImgBox2.style.left = (event.offsetX - 100) + "px";

        //　拡大画像 (元画像の座標から反転して100足した値を設定)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg2.style.top = (newLensOffsetY) + "px";
        lensImg2.style.left = (newLensOffsetX) + "px";

    }, false);

    // 元画像の上にマウスポインタがない場合、虫眼鏡を非表示
    baseImg2.addEventListener("mouseout", function () {

        lensImgBox2.style.opacity = 0;

    }, false);
}



// Dom  元画像Box
let baseImgBox3 = document.querySelector(".base_img_box3");

baseImgBox3.onload = setImgClone3();

function setImgClone3() {
    // Dom コンテント
    let content3 = document.querySelector(".content-detail3");

    // Dom 拡大画像Box(元画像Box のクローン)
    let lensImgBox3 = baseImgBox3.cloneNode(true);
    // クラス名「lens_img_box」に変更
    lensImgBox3.className = "lens_img_box3";

    // Dom 拡大画像(lensImgBoxの子要素)
    let lensImg3 = lensImgBox3.firstElementChild;
    // クラス名「lens_img」に変更
    lensImg3.className = "lens_img3";
    // 二倍に拡大
    lensImg3.style.transform = "scale(2)";

    // 元画像のクローンを配置
    content3.appendChild(lensImgBox3);

    // Dom  元画像
    let baseImg3 = document.querySelector(".base_img3");

    // 拡大比率　今回は2倍
    let ratio = 2;

    // 元画像の上をマウスポインタが通った場合、虫眼鏡を表示
    baseImg3.addEventListener("mousemove", function (event) {

        // 拡大画像Box(虫眼鏡...元画像の座標から100引いた値を設定)
        lensImgBox3.style.opacity = 1;
        lensImgBox3.style.top = (event.offsetY - 100) + "px";
        lensImgBox3.style.left = (event.offsetX - 100) + "px";

        //　拡大画像 (元画像の座標から反転して100足した値を設定)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg3.style.top = (newLensOffsetY) + "px";
        lensImg3.style.left = (newLensOffsetX) + "px";

    }, false);

    // 元画像の上にマウスポインタがない場合、虫眼鏡を非表示
    baseImg3.addEventListener("mouseout", function () {

        lensImgBox3.style.opacity = 0;

    }, false);
}


