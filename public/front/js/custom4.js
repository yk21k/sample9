/** @type {HTMLButtonElement} */
var openFormButton = document.querySelector(".open-form-button");
/** @type {HTMLElement} */
var modalOverlay = document.querySelector(".modal-overlay");
/** @type {HTMLFormElement} */
var form = document.querySelector(".modal-form");
var submitButton = document.querySelector(".submit-button");
var closeButton = document.querySelector(".close-button");
openFormButton.addEventListener("click", function() {
form.reset();
modalOverlay.style.display = "block";
});
submitButton.addEventListener("click", function() {
var formValues = {
    text1: form["text1"].value,
    text2: form["text2"].value,
    select1: form["select1"].value,
    radio1: form["radio1"].value,
    checkbox1: form["checkbox1"].checked,
};
console.log(formValues);
alert(JSON.stringify(formValues, null, 2));
});
closeButton.addEventListener("click", function() {
modalOverlay.style.display = "none";
});