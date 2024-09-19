// Dom Original Image Box
let baseImgBox1 = document.querySelector(".base_img_box1");

if(baseImgBox1){
    baseImgBox1.onload = setImgClone();
}

function setImgClone() {
    // DOM Content
    let content1 = document.querySelector(".content-detail1");
    
    // Dom Enlarged Image Box (Clone of Original Image Box)
    let lensImgBox1 = baseImgBox1.cloneNode(true); 

    // Changed the class name to "lens_img_box"
    lensImgBox1.className = "lens_img_box1";

    // Dom magnified image (child element of lensImgBox)
    let lensImg1 = lensImgBox1.firstElementChild;
    // Changed class name to "lens_img"
    lensImg1.className = "lens_img1";
    // Double magnification
    lensImg1.style.transform = "scale(2)";

    // Place a clone of the original image
    content1.appendChild(lensImgBox1);

    // DOM Original Image
    let baseImg1 = document.querySelector(".base_img1");

    // Expansion ratio: 2 times this time
    let ratio = 2;

    // When the mouse pointer hovers over the original image, a magnifying glass is displayed.
    baseImg1.addEventListener("mousemove", function (event) {

        // Enlarged Image Box (magnifying glass... Set the value of the original image by subtracting 100 from the coordinates)
        lensImgBox1.style.opacity = 1;
        lensImgBox1.style.top = (event.offsetY - 100) + "px";
        lensImgBox1.style.left = (event.offsetX - 100) + "px";

        //　Enlarged image (inverted from the coordinates of the original image and set by adding 100)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg1.style.top = (newLensOffsetY) + "px";
        lensImg1.style.left = (newLensOffsetX) + "px";

    }, false);

    // Hide the magnifying glass when there is no mouse pointer over the original image
    baseImg1.addEventListener("mouseout", function () {

        lensImgBox1.style.opacity = 0;

    }, false);
}



// Dom Original Image Box
let baseImgBox2 = document.querySelector(".base_img_box2");

if(baseImgBox2){
    baseImgBox2.onload = setImgClone2();
}

function setImgClone2() {
    // DOM Content
    let content2 = document.querySelector(".content-detail2");

    // Dom Enlarged Image Box (Clone of Original Image Box)
    let lensImgBox2 = baseImgBox2.cloneNode(true);

    // Changed the class name to "lens_img_box"
    lensImgBox2.className = "lens_img_box2";

    // Dom magnified image (child element of lensImgBox)
    let lensImg2 = lensImgBox2.firstElementChild;
    // Changed class name to "lens_img"
    lensImg2.className = "lens_img2";
    // Double magnification
    lensImg2.style.transform = "scale(2)";

    // Place a clone of the original image
    content2.appendChild(lensImgBox2);

    // DOM Original Image
    let baseImg2 = document.querySelector(".base_img2");

    // Expansion ratio: 2 times this time
    let ratio = 2;

    // Hide the magnifying glass when there is no mouse pointer over the original image
    baseImg2.addEventListener("mousemove", function (event) {

        // Enlarged Image Box (magnifying glass... Set the value of the original image by subtracting 100 from the coordinates)
        lensImgBox2.style.opacity = 1;
        lensImgBox2.style.top = (event.offsetY - 100) + "px";
        lensImgBox2.style.left = (event.offsetX - 100) + "px";

        //　Enlarged image (inverted from the coordinates of the original image and set by adding 100)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg2.style.top = (newLensOffsetY) + "px";
        lensImg2.style.left = (newLensOffsetX) + "px";

    }, false);

    // Hide the magnifying glass when there is no mouse pointer over the original image
    baseImg2.addEventListener("mouseout", function () {

        lensImgBox2.style.opacity = 0;

    }, false);
}



// Dom Original Image Box
let baseImgBox3 = document.querySelector(".base_img_box3");

if(baseImgBox3){
    baseImgBox3.onload = setImgClone3();
}

function setImgClone3() {
    // DOM Content
    let content3 = document.querySelector(".content-detail3");

    // Dom Enlarged Image Box (Clone of Original Image Box)
    let lensImgBox3 = baseImgBox3.cloneNode(true);

    // Changed the class name to "lens_img_box"
    lensImgBox3.className = "lens_img_box3";

    // Dom magnified image (child element of lensImgBox)
    let lensImg3 = lensImgBox3.firstElementChild;
    // Changed class name to "lens_img"
    lensImg3.className = "lens_img3";
    // Double magnification
    lensImg3.style.transform = "scale(2)";

    // Place a clone of the original image
    content3.appendChild(lensImgBox3);

    // DOM Original Image
    let baseImg3 = document.querySelector(".base_img3");

    // Expansion ratio: 2 times this time
    let ratio = 2;

    // Hide the magnifying glass when there is no mouse pointer over the original image
    baseImg3.addEventListener("mousemove", function (event) {

        // Enlarged Image Box (magnifying glass... Set the value of the original image by subtracting 100 from the coordinates)
        lensImgBox3.style.opacity = 1;
        lensImgBox3.style.top = (event.offsetY - 100) + "px";
        lensImgBox3.style.left = (event.offsetX - 100) + "px";

        //　Enlarged image (inverted from the coordinates of the original image and set by adding 100)
        let newLensOffsetY = event.offsetY * ratio * -1 + 100;
        let newLensOffsetX = event.offsetX * ratio * -1 + 100;

        lensImg3.style.top = (newLensOffsetY) + "px";
        lensImg3.style.left = (newLensOffsetX) + "px";

    }, false);

    // Hide the magnifying glass when there is no mouse pointer over the original image
    baseImg3.addEventListener("mouseout", function () {

        lensImgBox3.style.opacity = 0;

    }, false);
}


