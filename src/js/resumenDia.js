document.addEventListener('DOMContentLoaded', () => {

    // URLs API - ajusta según tu backend
    const API_RESUMEN_URL = 'src/database/resumenDia.php';
    const API_VENTAS_URL = 'src/database/resumenVentas.php';

    // Elementos DOM
    const fechaResumenEl = document.getElementById('fechaResumen');
    const btnBuscar = document.getElementById('btnBuscar');
    const totalVendidoEl = document.getElementById('totalVendido');
    const ventasRealizadasEl = document.getElementById('ventasRealizadas');
    const productosVendidosEl = document.getElementById('productosVendidos');
    const tablaVentasBody = document.getElementById('tablaVentasBody');

    // Obtener fecha de hoy
    const hoy = new Date().toISOString().split('T')[0];
    fechaResumenEl.value = hoy;
    fechaResumenEl.max = hoy; // No permitir fechas futuras

    // Carga inicial
    cargarResumenDia(hoy);
    cargarVentas(hoy);

    // Eventos
    btnBuscar.addEventListener('click', () => {
        const fechaSeleccionada = fechaResumenEl.value;
        if (fechaSeleccionada) {
            cargarResumenDia(fechaSeleccionada);
            cargarVentas(fechaSeleccionada);
        } else {
            mostrarMensaje('Por favor selecciona una fecha', 'error');
        }
    });

    fechaResumenEl.addEventListener('change', () => {
        const fechaSeleccionada = fechaResumenEl.value;
        if (fechaSeleccionada) {
            cargarResumenDia(fechaSeleccionada);
            cargarVentas(fechaSeleccionada);
        }
    });

    // Funciones
    function cargarResumenDia(fecha) {
        fetch(`${API_RESUMEN_URL}?action=obtenerResumen&fecha=${fecha}&t=${Date.now()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    totalVendidoEl.textContent = `$${formatNumero(data.totalVendido)}`;
                    ventasRealizadasEl.textContent = data.ventasRealizadas;
                    productosVendidosEl.textContent = data.productosVendidos;
                } else {
                    mostrarMensaje('Error al cargar resumen: ' + (data.message || ''), 'error');
                }
            })
            .catch(err => {
                mostrarMensaje('Error de conexión: ' + err.message, 'error');
            });
    }

    function cargarVentas(fecha) {
        tablaVentasBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:30px;">Cargando ventas...</td></tr>';
        fetch(`${API_VENTAS_URL}?action=obtenerVentasDelDia&fecha=${fecha}&t=${Date.now()}`)
            .then(res => {
                if (!res.ok) throw new Error(res.statusText);
                return res.json();
            })
            .then(data => {
                if (data.success && data.ventas.length > 0) {
                    tablaVentasBody.innerHTML = '';
                    data.ventas.forEach(venta => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${venta.idVenta}</td>
                        <td>${venta.fecha}</td>
                        <td>${venta.cliente}</td>
                        <td>${venta.productos}</td>
                        <td>$${formatNumero(venta.total)}</td>
                    `;
                        tablaVentasBody.appendChild(tr);
                    });
                } else {
                    tablaVentasBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay ventas para esta fecha</td></tr>';
                }
            })
            .catch(err => {
                tablaVentasBody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">Error: ${err.message}</td></tr>`;
            });
    }

    function mostrarMensaje(texto, tipo) {
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje mensaje-' + tipo;
        mensaje.textContent = texto;
        mensaje.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 15px 25px; background-color: ' + (tipo === 'exito' ? '#4CAF50' : '#f44336') + '; color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; animation: slideIn 0.3s ease-out; max-width: 400px; font-weight: 500;';

        document.body.appendChild(mensaje);

        setTimeout(() => {
            mensaje.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => mensaje.remove(), 300);
        }, 4000);
    }

    function formatNumero(numero) {
        return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});

const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
`;
document.head.appendChild(style);