document.addEventListener('DOMContentLoaded', () => {

    // URLs API
    const API_RESUMEN_URL = '/JoyeriaChabelita-Proyecto/src/database/resumenCaja.php';
    const API_VENTAS_URL = '/JoyeriaChabelita-Proyecto/src/database/ventas.php';

    const API_CANCELAR_URL = '/JoyeriaChabelita-Proyecto/src/database/corteCaja.php';

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


    const btnCancelar = document.getElementById('btnCancelar');
    const btnConfirmarCierre = document.getElementById('btnConfirmarCierre');

    const vistaPrevia = document.getElementById('vistaPrevia');
    const btnCerrarVistaPrevia = document.getElementById('btnCerrarVistaPrevia');
    const btnDescargarPDF = document.getElementById('btnDescargarPDF');

    // Establecer fecha de hoy por defecto
    const hoy = new Date().toISOString().split('T')[0];
    fechaCorteEl.value = hoy;
    fechaCorteEl.max = hoy;

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




    btnGenerarReporte.addEventListener('click', () => {
        generarReporte();
    });

    btnCerrarVistaPrevia.addEventListener('click', () => {
        vistaPrevia.style.display = 'none';
    });

    btnDescargarPDF.addEventListener('click', () => {
        window.open('/JoyeriaChabelita-Proyecto/src/database/reporteDia.pdf', '_blank');
    });

    // ========== FUNCIONES EXISTENTES ==========

    function cargarResumenCaja(fecha) {
        fetch(`${API_RESUMEN_URL}?action=obtenerResumen&fecha=${fecha}&t=${Date.now()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
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
        tablaVentasBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:30px;">Cargando ventas...</td></tr>';
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
                            <td>
                                <button class="btn-cancelar-venta" onclick="abrirModalCancelacion(${venta.idVenta})" title="Cancelar venta">
                                    ❌
                                </button>
                            </td>
                        `;
                        tablaVentasBody.appendChild(tr);
                    });
                } else {
                    tablaVentasBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay ventas para esta fecha</td></tr>';
                }
            })
            .catch(err => {
                tablaVentasBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">Error: ${err.message}</td></tr>`;
            });
    }



    function generarReporte() {
        const fecha = fechaCorteEl.value;

        if (!fecha) {
            mostrarMensaje('Por favor selecciona una fecha', 'error');
            return;
        }

        window.open(
            '/JoyeriaChabelita-Proyecto/src/database/reporteCaja.php?fecha=' + fecha,
            '_blank',
            'width=1200,height=800'
        );

        mostrarMensaje('✅ Reporte detallado abierto. Usa Ctrl+P para guardar como PDF.', 'exito');
    }

    // ========== NUEVAS FUNCIONES PARA CANCELACIÓN ==========

    window.abrirModalCancelacion = function (idVenta) {
        cargarDetallesCancelacion(idVenta);
    };

    function cargarDetallesCancelacion(idVenta) {
        const modal = document.getElementById('modalCancelacion');
        const btnConfirmarCancelacion = document.getElementById('btnConfirmarCancelacion');

        if (!modal) {
            console.error('Modal de cancelación no encontrado');
            return;
        }

        modal.classList.add('activo');
        btnConfirmarCancelacion.disabled = true;

        fetch(API_CANCELAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'obtenerDetallesVenta',
                idVenta: idVenta
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const venta = data.venta;
                    const productos = data.productos;

                    document.getElementById('cancelacion-idVenta').textContent = venta.idVenta;
                    document.getElementById('cancelacion-fecha').textContent = formatearFecha(venta.fechaVenta);
                    document.getElementById('cancelacion-dias').textContent = venta.diasTranscurridos + (venta.diasTranscurridos === 1 ? ' día' : ' días');
                    document.getElementById('cancelacion-cliente').textContent = venta.cliente || 'N/A';
                    document.getElementById('cancelacion-empleado').textContent = venta.empleado || 'N/A';
                    document.getElementById('cancelacion-metodo').textContent = venta.metodoPago ? venta.metodoPago.toUpperCase() : 'N/A';
                    document.getElementById('cancelacion-monto').textContent = '$' + formatNumero(venta.importeTotal);
                    document.getElementById('cancelacion-totalProductos').textContent = venta.totalProductos + ' producto' + (venta.totalProductos > 1 ? 's' : '');

                    llenarTablaProductosCancelacion(productos);

                    if (venta.diasTranscurridos <= 3) {
                        btnConfirmarCancelacion.disabled = false;
                        btnConfirmarCancelacion.textContent = '✓ Confirmar Cancelación';
                        btnConfirmarCancelacion.onclick = () => confirmarCancelacion(idVenta);
                    } else {
                        mostrarMensajeCancelacion('Esta venta no puede cancelarse. Solo se permiten cancelaciones dentro de 3 días.', 'error');
                    }
                } else {
                    mostrarMensajeCancelacion(data.message || 'Error al cargar detalles', 'error');
                }
            })
            .catch(err => {
                mostrarMensajeCancelacion('Error de conexión: ' + err.message, 'error');
            });
    }

    function llenarTablaProductosCancelacion(productos) {
        const tabla = document.getElementById('tablaProductosCancelacion');

        if (productos.length === 0) {
            tabla.innerHTML = '<div class="sin-productos">No hay productos en esta venta</div>';
            return;
        }

        let html = `
            <table class="tabla-productos-detalles">
                <thead>
                    <tr>
                        <th>ID Producto</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
        `;

        productos.forEach(prod => {
            html += `
                <tr>
                    <td>${prod.idProducto}</td>
                    <td>${prod.descripcion}</td>
                    <td>${prod.categoria || 'N/A'}</td>
                    <td class="cantidad"><strong>${prod.cantidad}</strong></td>
                    <td>$${formatNumero(prod.costo)}</td>
                    <td>$${formatNumero(prod.importe)}</td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        tabla.innerHTML = html;
    }

    function confirmarCancelacion(idVenta) {
        const confirmado = confirm(
            '⚠️ ADVERTENCIA: Esta acción eliminará completamente la venta y NO SE PUEDE DESHACER.\n\n' +
            'Se devolverán todos los productos al inventario.\n' +
            'Se eliminará el ingreso registrado.\n' +
            'Se recalculará el corte de caja.\n\n' +
            '¿Deseas continuar?'
        );

        if (!confirmado) return;

        const confirmado2 = confirm('⚠️ Esta es tu última oportunidad. ¿Realmente deseas cancelar esta venta?');
        if (!confirmado2) return;

        ejecutarCancelacion(idVenta);
    }

    function ejecutarCancelacion(idVenta) {
        const btnConfirmarCancelacion = document.getElementById('btnConfirmarCancelacion');
        const estadoOriginal = btnConfirmarCancelacion.textContent;

        btnConfirmarCancelacion.disabled = true;
        btnConfirmarCancelacion.textContent = '⏳ Cancelando...';

        fetch(API_CANCELAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'cancelarVenta',
                idVenta: idVenta
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarMensajeCancelacion('✓ ' + data.message, 'exito');

                    setTimeout(() => {
                        cerrarModalCancelacion();
                        const fechaSeleccionada = fechaCorteEl.value;
                        cargarResumenCaja(fechaSeleccionada);
                        cargarVentas(fechaSeleccionada);
                        mostrarMensaje('Venta cancelada exitosamente', 'exito');
                    }, 2000);
                } else {
                    mostrarMensajeCancelacion('✗ ' + data.message, 'error');
                    btnConfirmarCancelacion.disabled = false;
                    btnConfirmarCancelacion.textContent = estadoOriginal;
                }
            })
            .catch(err => {
                mostrarMensajeCancelacion('Error: ' + err.message, 'error');
                btnConfirmarCancelacion.disabled = false;
                btnConfirmarCancelacion.textContent = estadoOriginal;
            });
    }

    function cerrarModalCancelacion() {
        const modal = document.getElementById('modalCancelacion');
        if (modal) {
            modal.classList.remove('activo');
        }
    }

    window.cerrarModalCancelacion = cerrarModalCancelacion;

    function mostrarMensajeCancelacion(texto, tipo) {
        const mensaje = document.getElementById('mensajeCancelacion');
        if (mensaje) {
            mensaje.textContent = texto;
            mensaje.className = 'mensaje-cancelacion';
            mensaje.classList.add('mensaje-' + tipo);
            mensaje.style.display = 'block';
        }
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

    function formatearFecha(fecha) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-MX', options);
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