// Intercept inline onclick handlers that remove parent modal elements
// Convert them to use the unified hideClosestModal behavior
(function(){
  // Detect inline onclicks that directly remove parent nodes (legacy close handlers)
  function shouldReplaceInlineClose(attr){
    if(!attr) return false;
    return /parent(Node|Element)\.remove\(\)/.test(attr);
  }

  // Track recent user interaction to avoid hiding modals the user intentionally opened
  var lastUserAction = 0;
  var USER_ACTION_WINDOW = 1200; // ms

  function markUserAction(){ lastUserAction = Date.now(); }
  document.addEventListener('click', function(e){
    var btn = e.target.closest('button, a, .ab-btn, .btn, [data-action]');
    if(btn) markUserAction();
  }, true);
  document.addEventListener('touchstart', markUserAction, {passive:true});

  // Intercept inline onclick close handlers and route them to safe teardown
  document.addEventListener('click', function(e){
    var t = e.target;
    if(!t) return;
    var el = t.closest('[onclick]');
    if(!el) return;
    var onclick = el.getAttribute('onclick') || '';
    if(!shouldReplaceInlineClose(onclick)) return;

    e.stopImmediatePropagation();
    e.preventDefault();

    var m = el.closest('[class*=\"modal\"], [class*=\"dialog\"], .message-modal, .modal-overlay');
    if(typeof hideClosestModal === 'function'){
      try{ hideClosestModal(el); }catch(err){
        if(m && m.parentNode) m.parentNode.removeChild(m);
      }
    } else {
      if(m && m.parentNode) m.parentNode.removeChild(m);
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    }
  }, true);

  // Helper to hide a modal element using central helpers when available
  function hideModalElement(m){
    if(!m) return;
    try{
      if(typeof hideModalById === 'function' && m.id) { hideModalById(m.id); return; }
      if(typeof hideClosestModal === 'function') { hideClosestModal(m); return; }
    }catch(e){ /* fallthrough to fallback */ }
    try{ m.style.display = 'none'; }catch(e){}
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
  }

  // On DOMContentLoaded hide any existing modal overlays
  document.addEventListener('DOMContentLoaded', function(){
    var nodes = document.querySelectorAll('.modal, .modal-overlay, .dialog, .message-modal, [id$=\"Modal\"]');
    nodes.forEach(function(n){ hideModalElement(n); });

    // Observe for dynamically added or toggled modal elements and hide them
    var observer = new MutationObserver(function(mutations){
      mutations.forEach(function(mut){
        // New nodes
        mut.addedNodes && mut.addedNodes.forEach(function(node){
          if(!(node instanceof Element)) return;
          if(node.matches && node.matches('.modal, .modal-overlay, .dialog, .message-modal, [id$=\"Modal\"]')){
            // If the user acted recently, assume the show was intentional
            if(Date.now() - lastUserAction < USER_ACTION_WINDOW) return;
            var disp = window.getComputedStyle(node).display;
            if(disp !== 'none') hideModalElement(node);
          }
        });

        // Attribute changes (style/class) on existing nodes
        if(mut.type === 'attributes' && (mut.attributeName === 'style' || mut.attributeName === 'class')){
          var node = mut.target;
          if(node.matches && node.matches('.modal, .modal-overlay, .dialog, .message-modal, [id$=\"Modal\"]')){
            if(Date.now() - lastUserAction < USER_ACTION_WINDOW) return;
            var disp = window.getComputedStyle(node).display;
            if(disp !== 'none') hideModalElement(node);
          }
        }
      });
    });

    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style','class'] });
    // Disconnect observer after a short stabilization window to avoid long-term overhead
    setTimeout(function(){ observer.disconnect(); }, 5000);
  });
})();
