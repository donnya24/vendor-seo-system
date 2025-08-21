// ===== CSRF helpers =====
window.csrf = (() => {
  const tokenEl = document.querySelector('meta[name="csrf-token"]');
  const headerEl = document.querySelector('meta[name="csrf-header"]');
  return {
    header: headerEl ? headerEl.getAttribute("content") : "X-CSRF-TOKEN",
    get token() {
      return tokenEl ? tokenEl.getAttribute("content") : "";
    },
    refresh(newToken) {
      if (tokenEl && newToken) tokenEl.setAttribute("content", newToken);
    },
  };
})();

// ===== fetch wrapper (auto CSRF & JSON) =====
window.http = {
  async json(url, { method = "GET", body = null, headers = {} } = {}) {
    const opts = {
      method,
      headers: { Accept: "application/json", ...headers },
    };
    if (body && !(body instanceof FormData)) {
      opts.headers["Content-Type"] = "application/json";
      opts.body = JSON.stringify(body);
    } else if (body instanceof FormData) {
      opts.body = body; // Content-Type auto
    }
    // tambahkan CSRF utk method state-changing
    if (!["GET", "HEAD", "OPTIONS"].includes(method.toUpperCase())) {
      opts.headers[csrf.header] = csrf.token;
    }
    const res = await fetch(url, opts);
    // update token jika server kirim header baru
    const newTok = res.headers.get("X-CSRF-RENEW");
    if (newTok) csrf.refresh(newTok);

    let data = null;
    try {
      data = await res.json();
    } catch (e) {
      /* ignore */
    }

    if (!res.ok) {
      const msg = data && data.message ? data.message : `HTTP ${res.status}`;
      throw new Error(msg);
    }
    return data;
  },
};

// ===== Toast sederhana =====
window.toast = (msg, type = "info") => {
  const wrap = document.createElement("div");
  wrap.className = `fixed z-50 top-4 right-4 max-w-sm px-4 py-3 rounded-md shadow
    ${
      type === "success"
        ? "bg-green-600 text-white"
        : type === "error"
        ? "bg-red-600 text-white"
        : type === "warn"
        ? "bg-yellow-500 text-white"
        : "bg-gray-800 text-white"
    }`;
  wrap.textContent = msg;
  document.body.appendChild(wrap);
  setTimeout(() => wrap.remove(), 3000);
};

// ===== Confirm dialog helper =====
window.ask = (message = "Yakin melakukan aksi ini?") => window.confirm(message);

// ===== Alpine store global (sidebar, modal, dll) =====
document.addEventListener("alpine:init", () => {
  Alpine.store("ui", {
    sidebarOpen: window.innerWidth > 768,
    setSidebar(v) {
      this.sidebarOpen = v;
      localStorage.setItem("sidebarOpen", v);
    },
  });
});
