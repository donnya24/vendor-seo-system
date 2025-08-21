(function(){
  // contoh: autofill vendor berdasarkan service (kalau kamu mau dependen)
  const serviceSel = document.querySelector('select[name="service_id"]');
  const vendorSel  = document.querySelector('select[name="vendor_id"]');

  if (serviceSel && vendorSel) {
    serviceSel.addEventListener('change', async ()=>{
      const sid = serviceSel.value;
      if (!sid) return;
      try {
        // endpoint opsional: return [{id, name}]
        const list = await http.json(`/admin/services/${sid}/vendors`, { method:'GET' });
        vendorSel.innerHTML = '';
        list.forEach(v=>{
          const opt = document.createElement('option');
          opt.value = v.id;
          opt.textContent = v.name;
          vendorSel.appendChild(opt);
        });
      } catch(e){ /* silent */ }
    });
  }

  // status badge preview (opsional)
  const statusSel = document.querySelector('select[name="status"]');
  const badgeEl = document.querySelector('[data-lead-status-preview]');
  if (statusSel && badgeEl) {
    const mapClass = {
      new: 'bg-yellow-100 text-yellow-800',
      in_progress: 'bg-blue-100 text-blue-800',
      closed: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
    };
    const apply = ()=>{
      badgeEl.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + (mapClass[statusSel.value]||'bg-gray-100 text-gray-800');
      badgeEl.textContent = statusSel.options[statusSel.selectedIndex].text;
    };
    statusSel.addEventListener('change', apply);
    apply();
  }
})();
