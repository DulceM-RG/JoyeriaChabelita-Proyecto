// auth-middleware.js
// ⚠ Incluir este script al inicio de TODAS las páginas protegidas

(function() {
    'use strict';
    
    // 🔐 PERMISOS POR PUESTO
    const PERMISOS = {
        'gerente': {
            nombre: 'Gerente',
            puedeAcceder: ['dashboard-gerente', 'reportes', 'empleados', 'ventas', 'inventario', 'configuracion'],
            dashboardPrincipal: './dashboard-gerente.html'
        },
        'venta': {
            nombre: 'Vendedor',
            puedeAcceder: ['dashboard-venta', 'ventas', 'clientes', 'inventario'],
            dashboardPrincipal: './dashboard-venta.html'
        },
        'almacen': {
            nombre: 'Almacén',
            puedeAcceder: ['dashboard-almacen', 'inventario', 'pedidos', 'proveedores'],
            dashboardPrincipal: './dashboard-almacen.html'
        },
        'contador': {
            nombre: 'Contador',
            puedeAcceder: ['dashboard-contador', 'reportes', 'finanzas', 'nomina'],
            dashboardPrincipal: './dashboard-contador.html'
        }
    };
    
    /**
     * 🔍 Obtener sesión actual
     */
    function obtenerSesion() {
        const sesionJSON = localStorage.getItem('sesionUsuario');
        return sesionJSON ? JSON.parse(sesionJSON) : null;
    }
    
    /**
     * 🚪 Redirigir al login
     */
    function redirigirAlLogin() {
        console.warn('⚠ No hay sesión activa. Redirigiendo al login...');
        localStorage.removeItem('sesionUsuario');
        window.location.href = '../login.html';
    }
    
    /**
     * 🔒 Verificar acceso a la página actual
     */
    function verificarAcceso() {
        const sesion = obtenerSesion();
        
        // Si no hay sesión, redirigir al login
        if (!sesion || !sesion.puesto) {
            redirigirAlLogin();
            return false;
        }
        
        // Obtener el nombre de la página actual
        const paginaActual = window.location.pathname.split('/').pop().replace('.html', '');
        
        console.log('🔍 Verificando acceso:', {
            usuario: sesion.nombreCompleto,
            puesto: sesion.puesto,
            pagina: paginaActual
        });
        
        // Obtener permisos del puesto
        const permisosPuesto = PERMISOS[sesion.puesto.toLowerCase()];
        
        if (!permisosPuesto) {
            console.error('❌ Puesto no reconocido:', sesion.puesto);
            redirigirAlLogin();
            return false;
        }
        
        // Verificar si el usuario puede acceder a esta página
        const tienePermiso = permisosPuesto.puedeAcceder.some(pagina => 
            paginaActual.includes(pagina)
        );
        
        if (!tienePermiso) {
            console.warn('⛔ Acceso denegado a:', paginaActual);
            alert(`⛔ Acceso denegado\n\nNo tienes permisos para acceder a esta página.\nSerás redirigido a tu dashboard.`);
            window.location.href = permisosPuesto.dashboardPrincipal;
            return false;
        }
        
        console.log('✅ Acceso permitido');
        return true;
    }
    
    /**
     * 👤 Mostrar información del usuario en el header
     */
    function mostrarInfoUsuario() {
        const sesion = obtenerSesion();
        
        if (sesion) {
            // Buscar elementos en el DOM para mostrar info del usuario
            const elemNombre = document.getElementById('usuarioNombre');
            const elemPuesto = document.getElementById('usuarioPuesto');
            const elemIdControl = document.getElementById('usuarioIdControl');
            
            if (elemNombre) elemNombre.textContent = sesion.nombreCompleto;
            if (elemPuesto) elemPuesto.textContent = sesion.puesto.toUpperCase();
            if (elemIdControl) elemIdControl.textContent = sesion.idControl;
        }
    }
    
    /**
     * 🚪 Función para cerrar sesión
     */
    function cerrarSesion() {
        if (confirm('¿Está seguro que desea cerrar sesión?')) {
            console.log('🚪 Cerrando sesión...');
            localStorage.removeItem('sesionUsuario');
            window.location.href = '../login.html';
        }
    }
    
    /**
     * ⏱ Verificar expiración de sesión (opcional)
     */
    function verificarExpiracionSesion() {
        const sesion = obtenerSesion();
        
        if (sesion && sesion.fechaLogin) {
            const fechaLogin = new Date(sesion.fechaLogin);
            const ahora = new Date();
            const horasTranscurridas = (ahora - fechaLogin) / (1000 * 60 * 60);
            
            // Sesión expira después de 8 horas
            if (horasTranscurridas > 8) {
                alert('⏱ Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
                redirigirAlLogin();
                return false;
            }
        }
        
        return true;
    }
    
    // 🚀 EJECUTAR AL CARGAR LA PÁGINA
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar sesión y permisos
        if (!verificarAcceso()) {
            return; // Detener ejecución si no hay acceso
        }
        
        // Verificar expiración
        verificarExpiracionSesion();
        
        // Mostrar info del usuario
        mostrarInfoUsuario();
        
        // Configurar botón de logout si existe
        const btnLogout = document.getElementById('btnCerrarSesion');
        if (btnLogout) {
            btnLogout.addEventListener('click', cerrarSesion);
        }
    });
    
    // 🌐 Exponer funciones globalmente
    window.obtenerSesion = obtenerSesion;
    window.cerrarSesion = cerrarSesion;
    window.PERMISOS = PERMISOS;
    
})();
