(function () {
  // contoh: refresh kartu statistik dari endpoint ringan
  const elTotalVendors = document.querySelector('[data-stat="totalVendors"]');
  if (!elTotalVendors) return;

  http
    .json("/admin/api/stats")
    .then(({ totalVendors, todayLeads, monthlyDeals, topKeywords }) => {
      const map = {
        totalVendors: document.querySelector('[data-stat="totalVendors"]'),
        todayLeads: document.querySelector('[data-stat="todayLeads"]'),
        monthlyDeals: document.querySelector('[data-stat="monthlyDeals"]'),
        topKeywords: document.querySelector('[data-stat="topKeywords"]'),
      };
      if (map.totalVendors) map.totalVendors.textContent = totalVendors ?? "-";
      if (map.todayLeads) map.todayLeads.textContent = todayLeads ?? "-";
      if (map.monthlyDeals) map.monthlyDeals.textContent = monthlyDeals ?? "-";
      if (map.topKeywords) map.topKeywords.textContent = topKeywords ?? "-";
    })
    .catch(() => {
      /* silent */
    });
})();
