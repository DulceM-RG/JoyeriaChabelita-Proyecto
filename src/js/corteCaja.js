document.addEventListener('DOMContentLoaded', () => {

    // URLs API - ajusta si tu backend cambia
    const API_RESUMEN_URL = '/JoyeriaChabelita-Proyecto/src/database/resumenCaja.php';
    const API_VENTAS_URL = '/JoyeriaChabelita-Proyecto/src/database/ventas.php';
    const API_CERRAR_DIA_URL = '/JoyeriaChabelita-Proyecto/src/database/corteCaja.php';

    // Elementos DOM
    const fechaCorteEl = document.getElementById('fechaCorte');
    const btnBuscar = document.getElementById('btnBuscar');
    
    const totalDiaEl = document.getElementById('totalDia');
    const efectivoEl = document.getElementById('efectivo');
    const tarjetaEl = document.getElementById('tarjeta');
    const ventasRealizadasEl = document.getElementById('ventasRealizadas');
    const productosVendidosEl = document.getElementById('productosVendidos');
    const empleadosActivosEl = document.getElementById('empleadosActivos');

    const tablaVentasBody = document.getElementById('tablaVentasBody');

    const btnGenerarReporte = document.getElementById('btnGenerarReporte');
    const btnCerrarDia = document.getElementById('btnCerrarDia');
    const modalCerrarDia = document.getElementById('modalCerrarDia');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnConfirmarCierre = document.getElementById('btnConfirmarCierre');

    const vistaPrevia = document.getElementById('vistaPrevia');
    const btnCerrarVistaPrevia = document.getElementById('btnCerrarVistaPrevia');
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');

    // Establecer fecha de hoy por defecto
    const hoy = new Date().toISOString().split('T')[0];
    fechaCorteEl.value = hoy;
    fechaCorteEl.max = hoy; // No permitir fechas futuras

    // Carga inicial
    cargarResumenCaja(hoy);
    cargarVentas(hoy);

    // Eventos
    btnBuscar.addEventListener('click', () => {
        const fechaSeleccionada = fechaCorteEl.value;
        if (fechaSeleccionada) {
            cargarResumenCaja(fechaSeleccionada);
            cargarVentas(fechaSeleccionada);
        } else {
            mostrarMensaje('Por favor selecciona una fecha', 'error');
        }
    });

    fechaCorteEl.addEventListener('change', () => {
        const fechaSeleccionada = fechaCorteEl.value;
        if (fechaSeleccionada) {
            cargarResumenCaja(fechaSeleccionada);
            cargarVentas(fechaSeleccionada);
        }
    });

    btnCerrarDia.addEventListener('click', () => {
        modalCerrarDia.classList.add('active');
    });

    btnCancelar.addEventListener('click', () => {
        modalCerrarDia.classList.remove('active');
    });

    btnConfirmarCierre.addEventListener('click', () => {
        cerrarDia();
    });

    btnGenerarReporte.addEventListener('click', () => {
        generarReporte();
    });

    btnCerrarVistaPrevia.addEventListener('click', () => {
        vistaPrevia.style.display = 'none';
    });

    btnDescargarPDF.addEventListener('click', () => {
        window.open('/JoyeriaChabelita-Proyecto/src/database/reporteDia.pdf', '_blank');
    });

    // Funciones
    function cargarResumenCaja(fecha) {
        fetch(`${API_RESUMEN_URL}?action=obtenerResumen&fecha=${fecha}&t=${Date.now()}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                totalDiaEl.textContent = `$${formatNumero(data.totalDia)}`;
                efectivoEl.textContent = `$${formatNumero(data.efectivo)}`;
                tarjetaEl.textContent = `$${formatNumero(data.tarjeta)}`;
                ventasRealizadasEl.textContent = data.ventasRealizadas;
                productosVendidosEl.textContent = data.productosVendidos;
                empleadosActivosEl.textContent = data.empleadosActivos;
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
            if(!res.ok) throw new Error(res.statusText);
            return res.json();
        })
        .then(data => {
            if(data.success && data.ventas.length > 0) {
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

    function cerrarDia() {
        const fechaSeleccionada = fechaCorteEl.value;
        btnConfirmarCierre.disabled = true;
        fetch(API_CERRAR_DIA_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'cerrarDia', fecha: fechaSeleccionada})
        })
        .then(res => {
            if(!res.ok) throw new Error(res.statusText);
            return res.json();
        })
        .then(data => {
            btnConfirmarCierre.disabled = false;
            if(data.success) {
                mostrarMensaje('El día se cerró correctamente', 'exito');
                modalCerrarDia.classList.remove('active');
                cargarResumenCaja(fechaSeleccionada);
                cargarVentas(fechaSeleccionada);
            } else {
                mostrarMensaje(data.message || 'Error al cerrar el día', 'error');
            }
        })
        .catch(err => {
            btnConfirmarCierre.disabled = false;
            mostrarMensaje('Error: ' + err.message, 'error');
        });
    }

    function generarReporte() {
        vistaPrevia.style.display = 'block';
        mostrarMensaje('Vista previa lista.', 'exito');
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