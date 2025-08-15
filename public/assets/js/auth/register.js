// register.js

document.addEventListener("DOMContentLoaded", function () {
  // Variables for password visibility toggle
  let showPassword = false;
  let showConfirm = false;

  // Toggle password visibility for register page
  document
    .getElementById("togglePassword")
    ?.addEventListener("click", function () {
      showPassword = !showPassword;
      document.getElementById("password").type = showPassword
        ? "text"
        : "password";
    });

  // Toggle confirm password visibility
  document
    .getElementById("toggleConfirmPassword")
    ?.addEventListener("click", function () {
      showConfirm = !showConfirm;
      document.getElementById("confirmPassword").type = showConfirm
        ? "text"
        : "password";
    });
});
