// Minimal modal utilities: show/hide modal and manage body scroll-lock
(function(){
  function isVisible(el){
    if(!el) return false;
    var s = window.getComputedStyle(el);
    return s.display !== 'none' && s.visibility !== 'hidden' && s.opacity !== '0';
  }

  window.showModalById = function(id){
    var el = typeof id === 'string' ? document.getElementById(id) : id;
    if(!el) return;
    // ensure overlay uses flex for centering
    el.style.display = 'flex';
    el.style.alignItems = el.style.alignItems || 'center';
    el.style.justifyContent = el.style.justifyContent || 'center';
    document.body.classList.add('modal-open');
    // prevent small layout shifts
    document.body.style.overflow = 'hidden';
  }

  window.hideModalById = function(id){
    var el = typeof id === 'string' ? document.getElementById(id) : id;
    if(!el) return;
    el.style.display = 'none';
    // if no other visible modal overlays remain, clear scroll lock
    var anyVisible = Array.prototype.slice.call(document.querySelectorAll('.modal, .modal-overlay, .dialog, .dialog-overlay')).some(function(m){
      return m !== el && window.getComputedStyle(m).display !== 'none';
    });
    if(!anyVisible){
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    }
  }

  // Click on overlay (outside modal-content) closes modal
  document.addEventListener('click', function(e){
    var target = e.target;
    if(target && (target.classList && (target.classList.contains('modal') || target.classList.contains('modal-overlay') || target.classList.contains('dialog-overlay') || target.classList.contains('dialog')))){
      // find nearest modal id
      var id = target.id;
      if(id){ hideModalById(id); }
    }
  }, true);
  
  // Helper to hide the closest modal overlay for dynamic modals
  window.hideClosestModal = function(el){
    if(!el) return;
    var m = el.closest('.modal, .modal-overlay, .dialog, .dialog-overlay');
    if(!m) return;
    if(m.id && typeof hideModalById === 'function') return hideModalById(m.id);
    // fallback: hide and remove
    m.style.display = 'none';
    if (m.parentNode) m.parentNode.removeChild(m);
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
  }
})();
