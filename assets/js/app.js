document.addEventListener('DOMContentLoaded', () => {
  // Smooth fade for page
  document.body.style.opacity = '0';
  setTimeout(()=>{ document.body.style.transition='opacity .25s ease'; document.body.style.opacity='1'; }, 0);
});

document.addEventListener('click', function(e) {
  const trigger = e.target.closest('button, a, input[type="submit"]');
  if (!trigger) return;
  const form = trigger.closest('form[data-confirm]');
  if (!form) return;
  const msg = form.getAttribute('data-confirm') || 'Are you sure?';
  if (!confirm(msg)) {
    e.preventDefault();
    e.stopPropagation();
  }
});

