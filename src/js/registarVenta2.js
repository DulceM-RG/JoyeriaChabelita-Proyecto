// registrarventa.js - Sistema completo de ventas con conexión a BD
// 🔹 URL BASE PARA CONEXIÓN CON PHP
const URL_BASE = 'http://localhost/JoyeriaChabelita-Proyecto/src/database/';

// ==================== VARIABLES GLOBALES ====================
let empleadoActual = null;
let tipoClienteActual = null;
let productosEnVenta = [];
let metodoPagoSeleccionado = null;
let totalVenta = 0;

// ==================== CARGAR DATOS DEL EMPLEADO ====================
async function cargarDatosEmpleado() {
    try {
        const response = await fetch(URL_BASE + 'getEmpleadoSesion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const resultado = await response.json();

        if (resultado.success && resultado.empleado) {
            empleadoActual = resultado.empleado;
            document.getElementById('empleadoNombre').textContent = resultado.empleado.nombreCompleto;
            console.log('✅ Empleado cargado:', resultado.empleado);
        } else {
            console.error('❌ Error al cargar empleado');
            alert('Error al cargar datos del empleado. Redirigiendo al login...');
            window.location.href = '../login.html';
        }
    } catch (error) {
        console.error('❌ Error de conexión:', error);
        alert('Error de conexión. Por favor, intente nuevamente.');
    }
}

// ==================== FUNCIÓN PARA ACTUALIZAR FECHA Y HORA ====================
function actualizarFechaHora() {
    const ahora = new Date();

    const opcionesFecha = {
        timeZone: 'America/Mexico_City',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };

    const opcionesHora = {
        timeZone: 'America/Mexico_City',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    };

    const fecha = ahora.toLocaleDateString('es-MX', opcionesFecha);
    const hora = ahora.toLocaleTimeString('es-MX', opcionesHora);

    document.getElementById('fechaHora').textContent = `Fecha y hora del sistema: ${fecha} ${hora}`;
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('🚀 Iniciando sistema de ventas...');

    // Cargar datos del empleado
    await cargarDatosEmpleado();

    // Actualizar fecha y hora
    actualizarFechaHora();
    setInterval(actualizarFechaHora, 1000);
});

// ==================== REFERENCIAS A ELEMENTOS ====================
const btnPublico = document.getElementById('btnPublico');
const btnMayorista = document.getElementById('btnMayorista');
const acordeonBuscar = document.getElementById('acordeonBuscar');
const acordeonNuevo = document.getElementById('acordeonNuevo');
const btnNuevoCliente = document.getElementById('btnNuevoCliente');
const btnCancelar = document.querySelector('.btn-cancelar');
const btnGuardar = document.querySelector('.btn-guardar');
const btnBuscar = document.querySelector('.btn-buscar');

// Referencias joya
const inputCodigoJoya = document.getElementById('inputCodigoJoya');
const btnBuscarJoya = document.getElementById('btnBuscarJoya');
const tablaResultadosContainer = document.getElementById('tablaResultadosContainer');
const tablaResultadosBody = document.getElementById('tablaResultadosBody');
const tablaProductosVentaBody = document.getElementById('tablaProductosVentaBody');
const totalMonto = document.getElementById('totalMonto');
const btnCobrarVenta = document.getElementById('btnCobrarVenta');

// Referencias modal
const modalCobrarVenta = document.getElementById('modalCobrarVenta');
const btnCerrarModal = document.getElementById('btnCerrarModal');
const btnCancelarModal = document.getElementById('btnCancelarModal');
const btnConfirmarVenta = document.getElementById('btnConfirmarVenta');
const modalCliente = document.getElementById('modalCliente');
const modalProductos = document.getElementById('modalProductos');
const modalTotal = document.getElementById('modalTotal');
const btnEfectivo = document.getElementById('btnEfectivo');
const btnTarjeta = document.getElementById('btnTarjeta');
const efectivoSection = document.getElementById('efectivoSection');
const cambioSection = document.getElementById('cambioSection');
const inputEfectivoRecibido = document.getElementById('inputEfectivoRecibido');
const cambioMonto = document.getElementById('cambioMonto');

// ============================================
// VALIDACIÓN EN TIEMPO REAL DEL TELÉFONO
// ============================================
const inputTelefono = document.getElementById('nuevoTelefono');

inputTelefono.addEventListener('input', function (e) {
    this.value = this.value.replace(/[^0-9]/g, '');

    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }

    if (this.value.length === 10) {
        this.style.borderColor = '#4CAF50';
    } else if (this.value.length > 0) {
        this.style.borderColor = '#FF9800';
    } else {
        this.style.borderColor = '#D4CFC4';
    }
});

inputTelefono.addEventListener('paste', function (e) {
    e.preventDefault();
    const pasteData = e.clipboardData.getData('text');
    const numericData = pasteData.replace(/[^0-9]/g, '').slice(0, 10);
    this.value = numericData;
    this.dispatchEvent(new Event('input'));
});

inputTelefono.addEventListener('drop', function (e) {
    e.preventDefault();
});

inputTelefono.addEventListener('keypress', function (e) {
    if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1) {
        return;
    }
    if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});

// ============================================
// EVENTOS: TIPO DE CLIENTE
// ============================================
btnPublico.addEventListener('click', function () {
    tipoClienteActual = 'publico';
    btnPublico.classList.add('active');
    btnMayorista.classList.remove('active');
    acordeonBuscar.classList.remove('show');
    acordeonNuevo.classList.remove('show');
    window.clienteSeleccionado = null;
});

btnMayorista.addEventListener('click', function () {
    tipoClienteActual = 'mayorista';
    btnMayorista.classList.add('active');
    btnPublico.classList.remove('active');
    acordeonBuscar.classList.add('show');
    acordeonNuevo.classList.remove('show');
});

// ============================================
// BUSCAR CLIENTE (MAYORISTA)
// ============================================
btnBuscar.addEventListener('click', async function () {
    const inputBuscar = document.getElementById('inputBuscar').value.trim();

    if (!inputBuscar) {
        alert('Por favor, ingresa un criterio de búsqueda (teléfono o nombre)');
        return;
    }

    try {
        const response = await fetch(URL_BASE + 'clientes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'buscar',
                busqueda: inputBuscar
            })
        });

        const resultado = await response.json();

        if (resultado.success && resultado.clientes.length > 0) {
            const cliente = resultado.clientes[0];
            document.getElementById('inputTelefono').value = cliente.telefono;
            document.getElementById('inputNombreCompleto').value = cliente.nombreCompleto;
            document.getElementById('inputAcciones').value = 'Disponible';

            window.clienteSeleccionado = cliente;
            console.log('✅ Cliente encontrado:', cliente);
            alert(`✅ Cliente encontrado:\n${cliente.nombreCompleto}`);
        } else {
            alert('❌ No se encontraron clientes con ese criterio.');
            limpiarResultadosClientes();
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error al buscar cliente. Intente nuevamente.');
    }
});

// Seleccionar cliente
document.querySelector('.btn-seleccionar').addEventListener('click', function () {
    if (!window.clienteSeleccionado) {
        alert('⚠️ No hay cliente seleccionado. Por favor, busque un cliente primero.');
        return;
    }
    alert(`✅ Cliente seleccionado:\n${window.clienteSeleccionado.nombreCompleto}`);
});

// ============================================
// NUEVO CLIENTE
// ============================================
btnNuevoCliente.addEventListener('click', function () {
    acordeonNuevo.classList.add('show');
});

btnCancelar.addEventListener('click', function () {
    acordeonNuevo.classList.remove('show');
    limpiarFormularioNuevoCliente();
});

// ============================================
// GUARDAR NUEVO CLIENTE (CONECTADO CON PHP)
// ============================================
btnGuardar.addEventListener('click', async function () {
    const nombre = document.getElementById('nuevoNombre').value.trim();
    const apellidoP = document.getElementById('nuevoApellidoP').value.trim();
    const apellidoM = document.getElementById('nuevoApellidoM').value.trim();
    const telefono = document.getElementById('nuevoTelefono').value.trim();

    // Validar campos obligatorios
    if (!nombre) {
        alert('⚠️ El campo Nombre es obligatorio');
        document.getElementById('nuevoNombre').focus();
        return;
    }

    if (!apellidoP) {
        alert('⚠️ El campo Apellido Paterno es obligatorio');
        document.getElementById('nuevoApellidoP').focus();
        return;
    }

    if (!telefono) {
        alert('⚠️ El campo Teléfono es obligatorio');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // Validación del teléfono
    if (!/^[0-9]+$/.test(telefono)) {
        alert('❌ El teléfono solo puede contener números');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    if (telefono.length !== 10) {
        if (telefono.length < 10) {
            alert(`❌ El teléfono está incompleto\n\nActualmente tiene ${telefono.length} dígitos.\nDebe tener exactamente 10 dígitos.`);
        } else {
            alert(`❌ El teléfono es muy largo\n\nActualmente tiene ${telefono.length} dígitos.\nDebe tener exactamente 10 dígitos.`);
        }
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    if (telefono.charAt(0) === '0' || telefono.charAt(0) === '1') {
        alert('❌ El teléfono no puede comenzar con 0 o 1');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    if (/^(\d)\1{9}$/.test(telefono)) {
        alert('❌ El teléfono no es válido\n\nNo puede tener todos los dígitos iguales.');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    const patronesSospechosos = ['1234567890', '0987654321', '0000000000', '9999999999'];
    if (patronesSospechosos.includes(telefono)) {
        const confirmar = confirm('⚠️ El teléfono ingresado parece sospechoso\n\n¿Estás seguro de que es correcto?');
        if (!confirmar) {
            document.getElementById('nuevoTelefono').focus();
            return;
        }
    }

    // 🔹 GUARDAR EN BASE DE DATOS
    try {
        const response = await fetch(URL_BASE + 'clientes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'crear',
                nombre: nombre,
                apellidoPaterno: apellidoP,
                apellidoMaterno: apellidoM,
                telefono: telefono
            })
        });

        const resultado = await response.json();

        if (resultado.success) {
            alert(`✅ Cliente creado exitosamente!\n\n${resultado.cliente.nombreCompleto}\nTeléfono: ${resultado.cliente.telefono}`);

            // Mostrar en campos de búsqueda
            document.getElementById('inputTelefono').value = resultado.cliente.telefono;
            document.getElementById('inputNombreCompleto').value = resultado.cliente.nombreCompleto;
            document.getElementById('inputAcciones').value = 'Disponible';

            // Guardar para la venta
            window.clienteSeleccionado = resultado.cliente;

            acordeonNuevo.classList.remove('show');
            limpiarFormularioNuevoCliente();
        } else {
            alert('❌ Error: ' + resultado.error);
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error al crear cliente. Intente nuevamente.');
    }
});

function limpiarFormularioNuevoCliente() {
    document.getElementById('nuevoNombre').value = '';
    document.getElementById('nuevoApellidoP').value = '';
    document.getElementById('nuevoApellidoM').value = '';
    document.getElementById('nuevoTelefono').value = '';
    document.getElementById('nuevoTelefono').style.borderColor = '#D4CFC4';
}

function limpiarResultadosClientes() {
    document.getElementById('inputTelefono').value = '';
    document.getElementById('inputNombreCompleto').value = '';
    document.getElementById('inputAcciones').value = '';
    window.clienteSeleccionado = null;
}

// ============================================
// BUSCAR JOYA (CONECTADO CON PHP)
// ============================================
function buscarJoya() {
    const codigoBusqueda = inputCodigoJoya.value.trim();

    if (!codigoBusqueda) {
        alert("⚠️ Ingresa un código de producto");
        return;
    }

    // 🔹 CONECTAR CON PHP
    fetch(URL_BASE + 'buscarProducto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigoProducto: codigoBusqueda })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.productos.length > 0) {
                mostrarResultadosBusqueda(data.productos);
            } else {
                alert("❌ No se encontraron productos con ese código");
                tablaResultadosContainer.style.display = "none";
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            alert('Error al buscar producto. Intente nuevamente.');
        });
}

// ============================================
// MOSTRAR RESULTADOS DE BÚSQUEDA
// ============================================
function mostrarResultadosBusqueda(resultados) {
    tablaResultadosBody.innerHTML = '';

    resultados.forEach(joya => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${joya.idProducto}</td>
            <td>${joya.categoria}</td>
            <td>${joya.descripcion}</td>
            <td>${joya.stock}</td>
            <td>$${parseFloat(joya.precioUnitario).toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
            <td>
                <button class="btn-agregar-producto" onclick="agregarProductoAVenta('${joya.idProducto}', '${joya.categoria}', '${joya.descripcion}', ${joya.precioUnitario}, ${joya.stock})">
                    Agregar
                </button>
            </td>
        `;
        tablaResultadosBody.appendChild(fila);
    });

    tablaResultadosContainer.style.display = 'block';
}

// ============================================
// AGREGAR PRODUCTO A LA VENTA
// ============================================
window.agregarProductoAVenta = function (idProducto, categoria, descripcion, precio, stockDisponible) {
    precio = parseFloat(precio);
    stockDisponible = parseInt(stockDisponible);

    const productoExistente = productosEnVenta.find(p => p.codigo === idProducto);

    if (productoExistente) {
        if (productoExistente.cantidad < stockDisponible) {
            productoExistente.cantidad++;
            productoExistente.subtotal = productoExistente.cantidad * productoExistente.precio;
        } else {
            alert(`⚠️ Stock insuficiente\n\nSolo hay ${stockDisponible} unidades disponibles.`);
            return;
        }
    } else {
        productosEnVenta.push({
            codigo: idProducto,
            categoria: categoria,
            descripcion: descripcion,
            precio: precio,
            cantidad: 1,
            subtotal: precio,
            stockDisponible: stockDisponible
        });
    }

    actualizarTablaVenta();
    actualizarTotal();

    console.log('✅ Producto agregado:', descripcion);
};

// ============================================
// ACTUALIZAR TABLA DE VENTA
// ============================================
function actualizarTablaVenta() {
    tablaProductosVentaBody.innerHTML = '';

    if (productosEnVenta.length === 0) {
        tablaProductosVentaBody.innerHTML = `
            <tr id="mensajeSinProductos">
                <td colspan="7" class="sin-productos">
                    No hay productos agregados a la venta
                </td>
            </tr>
        `;
        btnCobrarVenta.disabled = true;
        return;
    }

    btnCobrarVenta.disabled = false;

    productosEnVenta.forEach((producto, index) => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${producto.codigo}</td>
            <td>${producto.categoria}</td>
            <td>${producto.descripcion}</td>
            <td>
                <div class="cantidad-container">
                    <button class="btn-cantidad" onclick="cambiarCantidad(${index}, -1)" ${producto.cantidad <= 1 ? 'disabled' : ''}>
                        -
                    </button>
                    <span class="cantidad-numero">${producto.cantidad}</span>
                    <button class="btn-cantidad" onclick="cambiarCantidad(${index}, 1)" ${producto.cantidad >= producto.stockDisponible ? 'disabled' : ''}>
                        +
                    </button>
                </div>
            </td>
            <td>$${producto.precio.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
            <td>$${producto.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
            <td>
                <button class="btn-eliminar-producto" onclick="eliminarProducto(${index})">
                    ✕
                </button>
            </td>
        `;
        tablaProductosVentaBody.appendChild(fila);
    });
}

// ============================================
// CAMBIAR CANTIDAD
// ============================================
window.cambiarCantidad = function (index, cambio) {
    const producto = productosEnVenta[index];
    const nuevaCantidad = producto.cantidad + cambio;

    if (nuevaCantidad < 1) {
        return;
    }

    if (nuevaCantidad > producto.stockDisponible) {
        alert(`⚠️ Stock insuficiente\n\nSolo hay ${producto.stockDisponible} unidades disponibles.`);
        return;
    }

    producto.cantidad = nuevaCantidad;
    producto.subtotal = producto.cantidad * producto.precio;

    actualizarTablaVenta();
    actualizarTotal();
};

// ============================================
// ELIMINAR PRODUCTO
// ============================================
window.eliminarProducto = function (index) {
    const producto = productosEnVenta[index];

    const confirmar = confirm(`¿Eliminar "${producto.descripcion}" de la venta?`);

    if (confirmar) {
        productosEnVenta.splice(index, 1);
        actualizarTablaVenta();
        actualizarTotal();
        console.log('🗑️ Producto eliminado');
    }
};

// ============================================
// ACTUALIZAR TOTAL
// ============================================
function actualizarTotal() {
    const total = productosEnVenta.reduce((sum, producto) => sum + producto.subtotal, 0);
    totalMonto.textContent = `$${total.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
}

// ============================================
// EVENT LISTENERS - BÚSQUEDA DE JOYAS
// ============================================
btnBuscarJoya.addEventListener('click', buscarJoya);

inputCodigoJoya.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        buscarJoya();
    }
});

inputCodigoJoya.addEventListener('input', function () {
    if (this.value.trim() === '') {
        tablaResultadosContainer.style.display = 'none';
    }
});

btnCobrarVenta.disabled = true;

// ============================================
// MODAL: ABRIR
// ============================================
function abrirModalCobrar() {
    if (productosEnVenta.length === 0) {
        alert('⚠️ No hay productos en la venta');
        return;
    }

    // Validar que haya cliente seleccionado
    if (tipoClienteActual === 'mayorista' && !window.clienteSeleccionado) {
        alert('⚠️ Por favor, seleccione un cliente mayorista');
        return;
    }

    totalVenta = productosEnVenta.reduce((sum, p) => sum + p.subtotal, 0);

    let tipoCliente = 'Público General';
    if (tipoClienteActual === 'mayorista' && window.clienteSeleccionado) {
        tipoCliente = window.clienteSeleccionado.nombreCompleto;
    }

    modalCliente.textContent = tipoCliente;
    modalProductos.textContent = productosEnVenta.length;
    modalTotal.textContent = `$${totalVenta.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;

    resetearModalCobrar();

    modalCobrarVenta.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// ============================================
// MODAL: CERRAR
// ============================================
function cerrarModalCobrar() {
    modalCobrarVenta.classList.remove('show');
    document.body.style.overflow = 'auto';
    resetearModalCobrar();
}

// ============================================
// MODAL: RESETEAR
// ============================================
function resetearModalCobrar() {
    metodoPagoSeleccionado = null;
    btnEfectivo.classList.remove('selected');
    btnTarjeta.classList.remove('selected');
    efectivoSection.style.display = 'none';
    cambioSection.style.display = 'none';
    inputEfectivoRecibido.value = '';
    cambioMonto.textContent = '$0';
    btnConfirmarVenta.disabled = true;
}

// ============================================
// SELECCIONAR MÉTODO DE PAGO
// ============================================
window.seleccionarMetodo = function (metodo) {
    metodoPagoSeleccionado = metodo;

    btnEfectivo.classList.remove('selected');
    btnTarjeta.classList.remove('selected');

    if (metodo === 'efectivo') {
        btnEfectivo.classList.add('selected');
        efectivoSection.style.display = 'block';
        btnConfirmarVenta.disabled = true;
        inputEfectivoRecibido.focus();
    } else if (metodo === 'tarjeta') {
        btnTarjeta.classList.add('selected');
        efectivoSection.style.display = 'none';
        cambioSection.style.display = 'none';
        btnConfirmarVenta.disabled = false;
    }
};

// ============================================
// CALCULAR CAMBIO
// ============================================
inputEfectivoRecibido.addEventListener('input', function (e) {
    this.value = this.value.replace(/[^0-9.]/g, '');

    const parts = this.value.split('.');
    if (parts.length > 2) {
        this.value = parts[0] + '.' + parts.slice(1).join('');
    }

    const efectivoRecibido = parseFloat(this.value) || 0;

    if (efectivoRecibido >= totalVenta) {
        const cambio = efectivoRecibido - totalVenta;
        cambioMonto.textContent = `$${cambio.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
        cambioSection.style.display = 'block';
        btnConfirmarVenta.disabled = false;
    } else {
        cambioSection.style.display = 'none';
        btnConfirmarVenta.disabled = true;
    }
});

// ============================================
// CONFIRMAR VENTA Y GENERAR TICKET
// ============================================
btnConfirmarVenta.addEventListener('click', function () {
    if (!metodoPagoSeleccionado) {
        alert('⚠️ Por favor, selecciona un método de pago');
        return;
    }

    if (metodoPagoSeleccionado === 'efectivo') {
        const efectivoRecibido = parseFloat(inputEfectivoRecibido.value) || 0;
        if (efectivoRecibido < totalVenta) {
            alert('⚠️ El efectivo recibido es insuficiente');
            return;
        }
    }

    // Generar ticket primero
    generarTicket();

    // Guardar venta en BD
    guardarVentaBD();

    // Cerrar modal
    cerrarModalCobrar();
});

// ============================================
// GENERAR TICKET
// ============================================
function generarTicket() {
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString('es-MX', {
        timeZone: 'America/Mexico_City',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
    const hora = ahora.toLocaleTimeString('es-MX', {
        timeZone: 'America/Mexico_City',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    let ticketHTML = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta - Joyería Chabelita</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            width: 350px; 
            margin: 20px auto;
            padding: 20px;
            background: white;
        }
        .ticket-container { border: 2px dashed #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 120px; height: auto; margin-bottom: 10px; }
        .header h1 { margin: 10px 0 5px 0; font-size: 22px; font-weight: bold; }
        .header .subtitle { font-size: 14px; color: #666; margin-bottom: 5px; }
        .info { margin-bottom: 15px; font-size: 12px; line-height: 1.6; }
        .info-row { display: flex; justify-content: space-between; margin: 3px 0; }
        .info-label { font-weight: bold; }
        .divider { border-top: 1px dashed #333; margin: 15px 0; }
        .divider-double { border-top: 2px solid #333; margin: 15px 0; }
        .productos { margin: 15px 0; }
        .productos-header { font-weight: bold; font-size: 13px; margin-bottom: 10px; text-align: center; text-decoration: underline; }
        .producto-item { margin: 8px 0; font-size: 11px; line-height: 1.5; }
        .producto-codigo { font-weight: bold; display: block; }
        .producto-desc { display: block; margin-left: 10px; color: #333; }
        .producto-detalle { display: flex; justify-content: space-between; margin-left: 10px; margin-top: 3px; }
        .total-section { margin-top: 15px; }
        .total-row { display: flex; justify-content: space-between; margin: 5px 0; font-size: 12px; }
        .total-final { font-weight: bold; font-size: 16px; margin-top: 10px; padding-top: 10px; border-top: 2px solid #333; }
        .pago-info { margin-top: 10px;