// login.js

const URL_BASE = "http://localhost/JoyeriaChabelita-Proyecto/src/database/";

// 🎯 MAPEO DE RUTAS POR PUESTO (SIN COMA EXTRA)
const RUTAS_POR_PUESTO = {
  gerente: "menuAdministracion.html",
  venta: "menuVentas.html",
  almacén: "menuAlmacen.html",
};

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const inputIdControl = document.getElementById("txtIdControl");
  const inputContrasena = document.getElementById("txtContrasena");

  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Obtener valores
      const idControl = inputIdControl.value.trim();
      const contrasena = inputContrasena.value;

      // Validaciones básicas
      if (!idControl || !contrasena) {
        mostrarError("Por favor, complete todos los campos.");
        return;
      }

      // Validar formato de ID Control (ej: G25102001)
      if (!/^[A-Z]\d{8,9}$/.test(idControl)) {
        mostrarError("El formato del ID de Control no es válido.");
        inputIdControl.focus();
        return;
      }

      // Deshabilitar botón mientras se procesa
      const btnLogin = loginForm.querySelector('button[type="submit"]');
      const textoOriginal = btnLogin.textContent;
      btnLogin.disabled = true;
      btnLogin.textContent = "Iniciando sesión...";

      try {
        console.log("📤 Enviando credenciales...");

        const response = await fetch(URL_BASE + "login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            idControl: idControl,
            contrasena: contrasena,
          }),
        });

        const contentType = response.headers.get("content-type");

        if (contentType && contentType.includes("application/json")) {
          const resultado = await response.json();

          console.log("📥 Respuesta del servidor:", resultado);

          if (resultado.success && resultado.usuario) {
            // ✅ LOGIN EXITOSO
            const puestoOriginal = resultado.usuario.puesto;
            console.log("✅ Login exitoso. Puesto recibido:", puestoOriginal);
            console.log("📊 Tipo de puesto:", typeof puestoOriginal);

            // Normalizar el puesto y verificar que existe en el mapeo
            const puestoNormalizado = puestoOriginal.toLowerCase().trim();
            console.log("🔄 Puesto normalizado:", puestoNormalizado);
            console.log("🗺️ Rutas disponibles:", Object.keys(RUTAS_POR_PUESTO));

            // Verificar si existe la ruta para este puesto
            if (RUTAS_POR_PUESTO[puestoNormalizado]) {
              redirigirSegunPuesto(puestoNormalizado);
            } else {
              console.error(
                "❌ Puesto no encontrado en RUTAS_POR_PUESTO:",
                puestoNormalizado
              );
              mostrarError(
                `Error: Puesto "${puestoOriginal}" no tiene ruta configurada. Contacte al administrador.`
              );
              btnLogin.disabled = false;
              btnLogin.textContent = textoOriginal;
            }
          } else {
            // ❌ ERROR DE LOGIN
            mostrarError(resultado.errorLogin || "Error al iniciar sesión");
            btnLogin.disabled = false;
            btnLogin.textContent = textoOriginal;
          }
        } else {
          // Error: respuesta no es JSON
          const textResponse = await response.text();
          console.error("❌ Respuesta no JSON:", textResponse);
          mostrarError("Error del servidor. Por favor, intente más tarde.");
          btnLogin.disabled = false;
          btnLogin.textContent = textoOriginal;
        }
      } catch (error) {
        console.error("❌ Error de conexión:", error);
        mostrarError(
          "Error de conexión. Verifique su red e intente nuevamente."
        );
        btnLogin.disabled = false;
        btnLogin.textContent = textoOriginal;
      }
    });
  }
});

// 🎯 Redirigir según el puesto
function redirigirSegunPuesto(puesto) {
  const puestoNormalizado = puesto.toLowerCase().trim();
  const ruta = RUTAS_POR_PUESTO[puestoNormalizado];

  if (ruta) {
    console.log(`🎯 Redirigiendo a: ${ruta}`);

    // Mostrar mensaje de bienvenida antes de redirigir
    mostrarExito(`¡Bienvenido! Redirigiendo al panel de ${puesto}...`);

    // Redirigir después de 1 segundo
    setTimeout(() => {
      console.log("🚀 Ejecutando redirección...");
      window.location.href = ruta;
    }, 1000);
  } else {
    console.error("❌ Puesto no reconocido:", puesto);
    console.error("🗺️ Rutas disponibles:", Object.keys(RUTAS_POR_PUESTO));
    mostrarError(
      `Error: Puesto "${puesto}" no válido. Contacte al administrador.`
    );
  }
}

// 🎨 Mostrar mensaje de error
function mostrarError(mensaje) {
  alert("❌ " + mensaje);
  console.error("Error:", mensaje);
}

// ✅ Mostrar mensaje de éxito
function mostrarExito(mensaje) {
  alert("✅ " + mensaje);
  console.log("Éxito:", mensaje);
}
