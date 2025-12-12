// assets/js/app.js
document.addEventListener('submit', function(e){
  const form = e.target;
  const reqs = form.querySelectorAll('[required]');
  let valid = true;
  reqs.forEach(el=>{
    if (!el.value || el.value.trim() === '') {
      valid = false;
      el.style.outline = '2px solid #f99';
    } else {
      el.style.outline = '';
    }
  });
  if (!valid) {
    e.preventDefault();
    alert('Please fill required fields.');
  }
});
