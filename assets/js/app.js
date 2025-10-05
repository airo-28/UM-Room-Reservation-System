(function(){
  if (window.AOS){ AOS.init({ once:true, duration:550, easing:'ease-out-cubic' }); }

  document.querySelectorAll('form[data-confirm]').forEach(function(f){
    f.addEventListener('submit', function(e){
      var msg = f.getAttribute('data-confirm') || 'Are you sure?';
      if(!confirm(msg)){ e.preventDefault(); }
    });
  });

  var flashes = document.querySelectorAll('.alert.alert-success');
  setTimeout(function(){
    flashes.forEach(function(el){
      el.style.transition='opacity .6s'; el.style.opacity='0';
      setTimeout(function(){ el.remove(); }, 600);
    });
  }, 3000);

  document.querySelectorAll('.btn-primary, .btn-accent').forEach(function(btn){
    btn.addEventListener('click', function(e){
      var r=document.createElement('span');
      var d=Math.max(btn.clientWidth, btn.clientHeight);
      r.style.width=r.style.height=d+'px';
      r.style.position='absolute';
      r.style.left=(e.offsetX - d/2)+'px';
      r.style.top=(e.offsetY - d/2)+'px';
      r.style.background='rgba(255,255,255,.35)';
      r.style.borderRadius='50%';
      r.style.pointerEvents='none';
      r.style.transform='scale(0)';
      r.style.transition='transform .35s ease, opacity .6s';
      btn.style.position='relative';
      btn.appendChild(r);
      requestAnimationFrame(function(){ r.style.transform='scale(1)'; r.style.opacity='0'; });
      setTimeout(function(){ r.remove(); }, 600);
    });
  });
})();
