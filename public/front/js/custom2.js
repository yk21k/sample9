const buttonOpen = document.getElementsByClassName('modalOpen')[0];
const modal = document.getElementsByClassName('modal')[0];
const buttonClose = document.getElementsByClassName('modalClose')[0];
const body = document.getElementsByTagName('body')[0];


if(typeof buttonOpen !== 'undefined'){
  // when the button is clicked
  buttonOpen.addEventListener('mouseenter', function(){
    modal.style.display = 'block';
    body.classList.add('open');
  });
}

if(typeof buttonOpen !== 'undefined'){
  // When the cross mark is clicked
  buttonClose.addEventListener('click',function(){
    modal.style.display = 'none';
    body.classList.remove('open');
  });
}  



document.addEventListener('DOMContentLoaded', function(event) {
  const targetButton = document.getElementById('submitButton');
  const triggerCheckbox = document.querySelector('input[name="agree"]');

  if(targetButton){
    targetButton.disabled = true;
    targetButton.classList.add('is-inactive');
  }

  if(targetButton){
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
  }
}, false);