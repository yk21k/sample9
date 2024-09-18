
const identification_1 = document.getElementById('identification_1');

identification_1.addEventListener('change', (e) => {
  const file_1 = document.getElementById('file_1');
  file_1.innerHTML = e.target.textContent;
  document.getElementById('output1').textContent = `${e.target.value} *(Compatible with jpg, jpeg, png, pdf)`;
});

const identification_2 = document.getElementById('identification_2');
identification_2.addEventListener('change', (e) => {
  const file_2 = document.getElementById('file_2');
  file_2.innerHTML = e.target.textContent;
  document.getElementById('output2').textContent = `${e.target.value} *(Compatible with PDF only)`;
});	


const identification_3 = document.getElementById('identification_3');
identification_3.addEventListener('change', (e) => {
  const file_3 = document.getElementById('file_3');
  file_3.innerHTML = e.target.textContent;
  document.getElementById('output3').textContent = `${e.target.value} *(Compatible with jpg, jpeg, png, pdf)`;
});




  
