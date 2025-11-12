
function cerrarSesion() {
    // 1. Eliminar los datos de la sesión del almacenamiento local (SEGURIDAD)
    localStorage.removeItem("sesionUsuario");

    console.log("Sesión cerrada");

    // 2. Redirigir al usuario al login
    window.location.href = "login.html";
}

// 2. Función que maneja la confirmación
function confirmarYcerrar() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        cerrarSesion();
    }
}