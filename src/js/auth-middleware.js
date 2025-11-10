// auth-middleware.js
// ⚙ Versión SIN almacenamiento ni verificación de sesión

(function () {
  "use strict";

  // 🔐 PERMISOS POR PUESTO (solo como referencia)
  const PERMISOS = {
    gerente: {
      nombre: "Gerente",
      puedeAcceder: [
        "dashboard-gerente",
        "reportes",
        "empleados",
        "ventas",
        "inventario",
        "configuracion",
      ],
      dashboardPrincipal: "./dashboard-gerente.html",
    },
    venta: {
      nombre: "Vendedor",
      puedeAcceder: ["dashboard-venta", "ventas", "clientes", "inventario"],
      dashboardPrincipal: "./dashboard-venta.html",
    },
    almacen: {
      nombre: "Almacén",
      puedeAcceder: [
        "dashboard-almacen",
        "inventario",
        "pedidos",
        "proveedores",
      ],
      dashboardPrincipal: "./dashboard-almacen.html",
    },
    contador: {
      nombre: "Contador",
      puedeAcceder: ["dashboard-contador", "reportes", "finanzas", "nomina"],
      dashboardPrincipal: "./dashboard-contador.html",
    },
  };

  // 🚀 Sin verificaciones automáticas
  console.log("🔓 Sistema sin verificación de sesión persistente");

  // 🌐 Exponer solo permisos
  window.PERMISOS = PERMISOS;
})();