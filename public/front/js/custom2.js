

const buttonOpen = document.getElementsByClassName('modalOpen')[0];
const modal = document.getElementsByClassName('modal')[0];
const buttonClose = document.getElementsByClassName('modalClose')[0];
const body = document.getElementsByTagName('body')[0];



// ボタンがクリックされた時
buttonOpen.addEventListener('mouseenter', function(){
  modal.style.display = 'block';
  body.classList.add('open');
});


// バツ印がクリックされた時
buttonClose.addEventListener('click',function(){
  modal.style.display = 'none';
  body.classList.remove('open');
});



document.addEventListener('DOMContentLoaded', function(event) {
  const targetButton = document.getElementById('submitButton');
  const triggerCheckbox = document.querySelector('input[name="agree"]');

  targetButton.disabled = true;
  targetButton.classList.add('is-inactive');

  triggerCheckbox.addEventListener('change', function() {
    if (this.checked) {
      targetButton.disabled = false;
      targetButton.classList.remove('is-inactive');
      targetButton.classList.add('is-active');
    } else {
      targetButton.disabled = true;
      targetButton.classList.remove('is-active');
      targetButton.classList.add('is-inactive');
    }
  }, false);
}, false);