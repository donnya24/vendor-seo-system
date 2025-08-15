// login.js

document.addEventListener("DOMContentLoaded", function () {
  // Variable for password visibility toggle
  let showPassword = false;

  // Toggle password visibility for login page
  document
    .getElementById("togglePassword")
    ?.addEventListener("click", function () {
      showPassword = !showPassword;
      document.getElementById("password").type = showPassword
        ? "text"
        : "password";
    });
});
