(function () {
  // delete area
  document.querySelectorAll('[data-action="delete-area"]').forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const id = btn.dataset.id;
      if (!ask("Hapus area ini?")) return;
      try {
        await http.json(`/admin/areas/${id}/delete`, { method: "POST" });
        toast("Area dihapus", "success");
        const row = btn.closest("tr");
        if (row) row.remove();
      } catch (err) {
        toast(err.message || "Gagal menghapus", "error");
      }
    });
  });

  // attach area -> form di vendor areas index sudah post biasa; ini opsional fetch
  document
    .querySelectorAll('form[data-action="attach-area"]')
    .forEach((form) => {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        try {
          await http.json(form.action, { method: "POST", body: fd });
          toast("Area attached", "success");
          location.reload();
        } catch (err) {
          toast(err.message, "error");
        }
      });
    });

  // detach area
  document
    .querySelectorAll('form[data-action="detach-area"]')
    .forEach((form) => {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!ask("Lepaskan area dari vendor?")) return;
        const fd = new FormData(form);
        try {
          await http.json(form.action, { method: "POST", body: fd });
          toast("Area detached", "success");
          location.reload();
        } catch (err) {
          toast(err.message, "error");
        }
      });
    });
})();
