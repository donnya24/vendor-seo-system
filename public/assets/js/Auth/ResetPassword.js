// public/assets/js/Auth/reset-password.js
document.addEventListener("DOMContentLoaded", () => {
  function wireToggle(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    if (!input || !btn) return;

    btn.addEventListener("click", () => {
      const isHidden = input.type === "password";
      input.type = isHidden ? "text" : "password";
      btn.textContent = isHidden ? "Sembunyikan" : "Tampilkan";
      btn.setAttribute(
        "aria-label",
        isHidden ? "Sembunyikan password" : "Tampilkan password"
      );
    });
  }

  wireToggle("password", "togglePassword");
  wireToggle("password_confirm", "togglePasswordConfirm");
});
