function showError(message) {
  const errorDiv = document.getElementById("errorMessage");
  errorDiv.textContent = message;
  errorDiv.style.display = "block";
}

function hideError() {
  document.getElementById("errorMessage").style.display = "none";
}

document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const credenciales = document.getElementById("credenciales").value;
  const password = document.getElementById("password").value;

  fetch("login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ credenciales, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideError();
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        showError(data.error || "Credenciales incorrectas.");
      }
    })
    .catch(() => showError("Error de conexión con el servidor."));
});
