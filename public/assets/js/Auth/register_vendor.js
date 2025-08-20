// public/assets/js/Auth/register.js
document.addEventListener("DOMContentLoaded", () => {
  function setupPasswordToggle(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    if (!input || !btn) return;

    const iconShow = btn.querySelector(".icon-show");
    const iconHide = btn.querySelector(".icon-hide");

    btn.addEventListener("click", () => {
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";

      // Tukar ikon
      if (iconShow) iconShow.classList.toggle("hidden", !isPassword);
      if (iconHide) iconHide.classList.toggle("hidden", isPassword);

      // Optional: perbarui aria-label
      const label = isPassword ? "Sembunyikan password" : "Tampilkan password";
      btn.setAttribute("aria-label", label);
    });
  }

  setupPasswordToggle("password", "btnTogglePassword");
  setupPasswordToggle("pass_confirm", "btnTogglePassConfirm");
});
