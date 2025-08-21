(function () {
  // delete service
  document.querySelectorAll('[data-action="delete-service"]').forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const id = btn.dataset.id;
      if (!ask("Hapus service ini?")) return;
      try {
        await http.json(`/admin/services/${id}/delete`, { method: "POST" });
        toast("Service dihapus", "success");
        const row = btn.closest("tr");
        if (row) row.remove();
      } catch (err) {
        toast(err.message || "Gagal menghapus", "error");
      }
    });
  });

  // live filter (opsional)
  const input = document.querySelector(
    'input[name="q"][data-scope="services"]'
  );
  if (input) {
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        const q = (input.value || "").trim();
        const url = new URL(location.href);
        if (q) url.searchParams.set("q", q);
        else url.searchParams.delete("q");
        location.href = url.toString();
      }
    });
  }

  // auto-calc total rate (kalau nanti kamu aktifkan komisi di service)
  const vendorRate = document.querySelector('input[name="vendor_rate"]');
  const companyRate = document.querySelector('input[name="company_rate"]');
  const totalRate = document.querySelector('[data-field="total_rate"]');
  if (vendorRate && companyRate && totalRate) {
    const recalc = () => {
      const v = parseFloat(vendorRate.value || "0");
      const c = parseFloat(companyRate.value || "0");
      totalRate.textContent = `${(v + c).toFixed(0)}%`;
    };
    vendorRate.addEventListener("input", recalc);
    companyRate.addEventListener("input", recalc);
    recalc();
  }
})();
