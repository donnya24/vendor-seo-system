(function () {
  // tombol delete (data-action="delete-vendor" data-id="123")
  document.querySelectorAll('[data-action="delete-vendor"]').forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const id = btn.dataset.id;
      if (!id) return;
      if (!ask("Hapus vendor ini?")) return;
      try {
        await http.json(`/admin/vendors/${id}/delete`, { method: "POST" });
        toast("Vendor dihapus", "success");
        // hapus baris tabel
        const row = btn.closest("tr");
        if (row) row.remove();
      } catch (err) {
        toast(err.message || "Gagal menghapus", "error");
      }
    });
  });

  // verify / unverify
  document.querySelectorAll('[data-action="verify-vendor"]').forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      try {
        await http.json(`/admin/vendors/${id}/verify`, { method: "POST" });
        toast("Vendor diverifikasi", "success");
        location.reload();
      } catch (err) {
        toast(err.message, "error");
      }
    });
  });

  document
    .querySelectorAll('[data-action="unverify-vendor"]')
    .forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        try {
          await http.json(`/admin/vendors/${id}/unverify`, { method: "POST" });
          toast("Status verifikasi dibatalkan", "success");
          location.reload();
        } catch (err) {
          toast(err.message, "error");
        }
      });
    });

  // search sederhana (optional): input[name="q"] -> ?q=
  const search = document.querySelector(
    'input[name="q"][data-scope="vendors"]'
  );
  if (search) {
    search.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        const q = (search.value || "").trim();
        const url = new URL(window.location.href);
        if (q) url.searchParams.set("q", q);
        else url.searchParams.delete("q");
        window.location.href = url.toString();
      }
    });
  }
})();
