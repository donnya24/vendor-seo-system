// Jalankan setelah DOM siap (aman walau tanpa defer)
document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.getElementById("togglePassword");
  const password = document.getElementById("password");
  if (!togglePassword || !password) return;

  togglePassword.addEventListener("click", () => {
    const isHidden = password.getAttribute("type") === "password";
    password.setAttribute("type", isHidden ? "text" : "password");
    togglePassword.textContent = isHidden ? "Sembunyikan" : "Tampilkan";
  });
});
