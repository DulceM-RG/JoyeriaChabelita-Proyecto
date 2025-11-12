const URL_BASE = 'http://localhost/JoyeriaChabelita-Proyecto/src/database/';
let empleadoActual = null;

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

// Cargar empleado al iniciar
document.addEventListener('DOMContentLoaded', async () => {
    await cargarDatosEmpleado();
    actualizarFechaHora();
    setInterval(actualizarFechaHora, 1000);
});

// Función para actualizar la fecha y hora de CDMX
function actualizarFechaHora() {
    const ahora = new Date();

    // Configurar para zona horaria de CDMX (America/Mexico_City)
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

// Actualizar cada segundo
setInterval(actualizarFechaHora, 1000);
actualizarFechaHora(); // Llamar inmediatamente

// Referencias a elementos
const btnPublico = document.getElementById('btnPublico');
const btnMayorista = document.getElementById('btnMayorista');
const acordeonBuscar = document.getElementById('acordeonBuscar');
const acordeonNuevo = document.getElementById('acordeonNuevo');
const btnNuevoCliente = document.getElementById('btnNuevoCliente');
const btnCancelar = document.querySelector('.btn-cancelar');
const btnGuardar = document.querySelector('.btn-guardar');
const btnBuscar = document.querySelector('.btn-buscar');

// Estado actual
let tipoClienteActual = null;

// ============================================
// VALIDACIÓN EN TIEMPO REAL DEL TELÉFONO
// ============================================
const inputTelefono = document.getElementById('nuevoTelefono');

// Evitar que se escriban letras o caracteres especiales
inputTelefono.addEventListener('input', function (e) {
    // Eliminar cualquier caracter que no sea número
    this.value = this.value.replace(/[^0-9]/g, '');

    // Limitar a 10 dígitos máximo
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }

    // Feedback visual (opcional)
    if (this.value.length === 10) {
        this.style.borderColor = '#4CAF50'; // Verde cuando tiene 10 dígitos
    } else if (this.value.length > 0) {
        this.style.borderColor = '#FF9800'; // Naranja cuando está incompleto
    } else {
        this.style.borderColor = '#D4CFC4'; // Color por defecto
    }
});

// Prevenir pegar texto que no sea numérico
inputTelefono.addEventListener('paste', function (e) {
    e.preventDefault();
    const pasteData = e.clipboardData.getData('text');
    const numericData = pasteData.replace(/[^0-9]/g, '').slice(0, 10);
    this.value = numericData;

    // Disparar evento input para aplicar validaciones
    this.dispatchEvent(new Event('input'));
});

// Prevenir arrastrar y soltar
inputTelefono.addEventListener('drop', function (e) {
    e.preventDefault();
});

// Prevenir teclas que no sean números
inputTelefono.addEventListener('keypress', function (e) {
    // Permitir: backspace, delete, tab, escape, enter
    if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1) {
        return;
    }
    // Verificar que sea un número (0-9)
    if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});

// Event listener para el botón Público general
btnPublico.addEventListener('click', function () {
    tipoClienteActual = 'publico';
    btnPublico.classList.add('active');
    btnMayorista.classList.remove('active');
    acordeonBuscar.classList.remove('show');
    acordeonNuevo.classList.remove('show');

    // ⚠️ AGREGAR ESTAS LÍNEAS:
    window.clienteSeleccionado = {
        idCliente: 1,
        nombreCompleto: 'Público General',
        tipoCliente: 'Publico'
    };
    console.log('✅ Cliente: Público General');
});

// Event listener para el botón Mayorista
/*btnMayorista.addEventListener('click', function () {
    tipoClienteActual = 'mayorista';
    btnMayorista.classList.add('active');
    btnPublico.classList.remove('active');
    acordeonBuscar.classList.add('show');
    acordeonNuevo.classList.remove('show');
});*/
btnMayorista.addEventListener('click', function () {
    tipoClienteActual = 'mayorista';
    btnMayorista.classList.add('active');
    btnPublico.classList.remove('active');
    acordeonBuscar.classList.add('show');
    acordeonNuevo.classList.remove('show');

    // ⚠️ AGREGAR ESTAS LÍNEAS:
    window.clienteSeleccionado = null;
    limpiarResultadosClientes();
});

// ============================================
// BOTON BUSCAR CLIENTES
// ============================================
// 
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
            mostrarTablaClientes(resultado.clientes); // 🔹 NUEVA FUNCIÓN
        } else {
            alert('❌ No se encontraron clientes con ese criterio.');
            ocultarTablaClientes(); // 🔹 NUEVA FUNCIÓN
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error al buscar cliente. Intente nuevamente.');
    }
});

// ============================================
// MOSTRAR TABLA DE CLIENTES
// ============================================
function mostrarTablaClientes(clientes) {
    const container = document.getElementById('tablaClientesContainer');
    const tbody = document.getElementById('tablaClientesBody');

    // Limpiar tabla
    tbody.innerHTML = '';

    // Agregar cada cliente como fila
    clientes.forEach((cliente) => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${cliente.telefono}</td>
            <td>${cliente.nombreCompleto}</td>
            <td>
                <button class="btn-seleccionar-cliente" onclick="seleccionarCliente(${cliente.idCliente}, '${cliente.nombreCompleto}', '${cliente.telefono}')">
                    Seleccionar
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });

    // Mostrar tabla
    container.style.display = 'block';

    console.log(`✅ ${clientes.length} cliente(s) encontrado(s)`);
}

// ============================================
// OCULTAR TABLA DE CLIENTES
// ============================================
function ocultarTablaClientes() {
    const container = document.getElementById('tablaClientesContainer');
    container.style.display = 'none';
}

// ============================================
// SELECCIONAR CLIENTE DE LA TABLA
// ============================================
window.seleccionarCliente = function (idCliente, nombreCompleto, telefono) {
    // Guardar cliente seleccionado
    window.clienteSeleccionado = {
        idCliente: idCliente,
        nombreCompleto: nombreCompleto,
        telefono: telefono,
        tipoCliente: 'Mayorista',
        idTipoCliente: 2
    };

    console.log('✅ Cliente seleccionado:', window.clienteSeleccionado);
    alert(`✅ Cliente seleccionado:\n${nombreCompleto}\nTeléfono: ${telefono}`);

    // Opcional: Ocultar tabla después de seleccionar
    ocultarTablaClientes();

    // Limpiar input de búsqueda
    document.getElementById('inputBuscar').value = '';
};



// Event listener para el botón Nuevo cliente
btnNuevoCliente.addEventListener('click', function () {
    acordeonNuevo.classList.add('show');
});

// Event listener para el botón Cancelar
btnCancelar.addEventListener('click', function () {
    acordeonNuevo.classList.remove('show');
    limpiarFormularioNuevoCliente();
});

// ============================================
// VALIDACIÓN ROBUSTA AL GUARDAR CLIENTE LISTO PARA GUARDAR LISTO
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

    // ============================================
    // VALIDACIÓN ROBUSTA DEL TELÉFONO
    // ============================================

    // 1. Verificar que solo contiene números
    if (!/^[0-9]+$/.test(telefono)) {
        alert('❌ El teléfono solo puede contener números\n\nPor favor, elimina letras o caracteres especiales.');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // 2. Verificar que tiene exactamente 10 dígitos
    if (telefono.length !== 10) {
        if (telefono.length < 10) {
            alert(`❌ El teléfono está incompleto\n\nActualmente tiene ${telefono.length} dígitos.\nDebe tener exactamente 10 dígitos.`);
        } else {
            alert(`❌ El teléfono es muy largo\n\nActualmente tiene ${telefono.length} dígitos.\nDebe tener exactamente 10 dígitos.`);
        }
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // 3. Verificar que no empiece con 0 o 1 (reglas de numeración en México)
    if (telefono.charAt(0) === '0' || telefono.charAt(0) === '1') {
        alert('❌ El teléfono no puede comenzar con 0 o 1\n\nEn México, los números de teléfono celular comienzan con dígitos del 2 al 9.');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // 4. Verificar que no sean todos números iguales
    if (/^(\d)\1{9}$/.test(telefono)) {
        alert('❌ El teléfono no es válido\n\nNo puede tener todos los dígitos iguales (ej: 1111111111).');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // 5. Verificar patrones sospechosos (opcional)
    const patronesSospechosos = [
        '1234567890',
        '0987654321',
        '0000000000',
        '9999999999'
    ];

    if (patronesSospechosos.includes(telefono)) {
        const confirmar = confirm('⚠️ El teléfono ingresado parece sospechoso\n\n¿Estás seguro de que es correcto?');
        if (!confirmar) {
            document.getElementById('nuevoTelefono').focus();
            return;
        }
    }

    // 6. Validación final con regex (por si acaso)
    if (!/^\d{10}$/.test(telefono)) {
        alert('❌ El teléfono debe tener exactamente 10 dígitos numéricos');
        document.getElementById('nuevoTelefono').focus();
        return;
    }

    // ============================================
    // SI PASA TODAS LAS VALIDACIONES
    // ============================================
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
            alert(`✅ Cliente creado exitosamente!\n\n${resultado.cliente.nombreCompleto}`);

            // Mostrar en campos de búsqueda
            document.getElementById('inputTelefono').value = resultado.cliente.telefono;
            document.getElementById('inputNombreCompleto').value = resultado.cliente.nombreCompleto;

            // Guardar para la venta
            window.clienteSeleccionado = resultado.cliente;

            acordeonNuevo.classList.remove('show');
            limpiarFormularioNuevoCliente();
        } else {
            alert('❌ Error: ' + resultado.error);
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error al crear cliente');
    }


});

// Función para limpiar el formulario de nuevo cliente
function limpiarFormularioNuevoCliente() {
    document.getElementById('nuevoNombre').value = '';
    document.getElementById('nuevoApellidoP').value = '';
    document.getElementById('nuevoApellidoM').value = '';
    document.getElementById('nuevoTelefono').value = '';

    // Restablecer el color del borde del teléfono
    document.getElementById('nuevoTelefono').style.borderColor = '#D4CFC4';
}

// SECCIÓN: SALIR SALIR SALIR SALIR
// Event listener para el botón Salir
/*document.querySelector('.btn-salir').addEventListener('click', function () {
    if (confirm('¿Estás seguro de que deseas salir?')) {
        // Aquí puedes agregar la lógica para cerrar sesión
        window.location.href = 'login.html'; // O la ruta que corresponda futuro index.html
    }
});*/

// ============================================
// SECCIÓN: SELECCIONAR JOYA Y CARRITO DE VENTA
// ============================================
let productosEnVenta = [];


// Array para almacenar los productos en la venta


// Datos de ejemplo de joyas (esto se reemplazará con consulta a BD)


// Referencias a elementos
const inputCodigoJoya = document.getElementById('inputCodigoJoya');
const btnBuscarJoya = document.getElementById('btnBuscarJoya');
const tablaResultadosContainer = document.getElementById('tablaResultadosContainer');
const tablaResultadosBody = document.getElementById('tablaResultadosBody');
const tablaProductosVentaBody = document.getElementById('tablaProductosVentaBody');
const totalMonto = document.getElementById('totalMonto');
const btnCobrarVenta = document.getElementById('btnCobrarVenta');

// ============================================
// FUNCIÓN: BUSCAR JOYA
// ============================================

function buscarJoya() {
    const codigoBusqueda = inputCodigoJoya.value.trim();

    if (!codigoBusqueda) {
        alert("⚠️ Ingresa un código de producto");
        return;
    }

    console.log('🔍 Iniciando búsqueda de producto:', codigoBusqueda);
    console.log('📡 URL:', URL_BASE + 'buscarProducto.php');

    fetch(URL_BASE + 'buscarProducto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigoProducto: codigoBusqueda })
    })
        .then(res => {
            console.log('📥 Response status:', res.status);
            console.log('📥 Response ok:', res.ok);
            return res.json();
        })
        .then(data => {
            console.log('📦 Datos recibidos:', data);

            if (data.success && data.productos && data.productos.length > 0) {
                console.log('✅ Productos encontrados:', data.productos.length);
                mostrarResultadosBusqueda(data.productos);
            } else {
                console.warn('⚠️ Sin resultados:', data.error);
                alert("❌ " + (data.error || "No se encontraron productos"));
                tablaResultadosContainer.style.display = "none";
            }
        })
        .catch(error => {
            console.error('❌ Error completo:', error);
            console.error('❌ Stack:', error.stack);
            alert('Error al buscar producto. Revisa la consola (F12)');
        });
}
// ============================================
// FUNCIÓN: MOSTRAR RESULTADOS DE BÚSQUEDA
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
            <td>$${parseFloat(joya.precioUnitario).toFixed(2)}</td>
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
// FUNCIÓN: AGREGAR PRODUCTO A LA VENTA
// ============================================

window.agregarProductoAVenta = function (idProducto, categoria, descripcion, precio, stockDisponible) {
    // Convertir precio a número
    precio = parseFloat(precio);
    stockDisponible = parseInt(stockDisponible);

    // Verificar si ya está en el carrito
    const productoExistente = productosEnVenta.find(p => p.codigo === idProducto);

    if (productoExistente) {
        // Si ya existe, aumentar cantidad
        if (productoExistente.cantidad < stockDisponible) {
            productoExistente.cantidad++;
            productoExistente.subtotal = productoExistente.cantidad * productoExistente.precio;
        } else {
            alert(`⚠️ Stock insuficiente\n\nSolo hay ${stockDisponible} unidades disponibles.`);
            return;
        }
    } else {
        // Si no existe, agregarlo
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

    // Actualizar la tabla y el total
    actualizarTablaVenta();
    actualizarTotal();

    console.log('✅ Producto agregado:', descripcion);
};

// ============================================
// FUNCIÓN: ACTUALIZAR TABLA DE VENTA
// ============================================
// ============================================
// FUNCIÓN: ACTUALIZAR TABLA DE VENTA
// ============================================

function actualizarTablaVenta() {
    // Limpiar TODA la tabla
    tablaProductosVentaBody.innerHTML = '';

    if (productosEnVenta.length === 0) {
        // Si no hay productos, mostrar mensaje
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

    // Si hay productos, NO mostrar el mensaje y habilitar botón
    btnCobrarVenta.disabled = false;

    // Agregar cada producto
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
            <td>$${producto.precio.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</td>
            <td>$${producto.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</td>
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
// FUNCIÓN: CAMBIAR CANTIDAD
// ============================================
window.cambiarCantidad = function (index, cambio) {
    const producto = productosEnVenta[index];
    const nuevaCantidad = producto.cantidad + cambio;

    // Validar límites
    if (nuevaCantidad < 1) {
        return;
    }

    if (nuevaCantidad > producto.stockDisponible) {
        alert(`⚠️ Stock insuficiente\n\nSolo hay ${producto.stockDisponible} unidades disponibles.`);
        return;
    }

    // Actualizar cantidad y subtotal
    producto.cantidad = nuevaCantidad;
    producto.subtotal = producto.cantidad * producto.precio;

    // Actualizar tabla y total
    actualizarTablaVenta();
    actualizarTotal();
};

// ============================================
// FUNCIÓN: ELIMINAR PRODUCTO
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
// FUNCIÓN: ACTUALIZAR TOTAL
// ============================================
function actualizarTotal() {
    const total = productosEnVenta.reduce((sum, producto) => sum + producto.subtotal, 0);
    totalMonto.textContent = `$${total.toLocaleString('es-MX', { minimumFractionDigits: 1 })}`;
}

// ============================================
// FUNCIÓN: COBRAR VENTA (Por implementar)
// ============================================
btnCobrarVenta.addEventListener('click', function () {
    if (productosEnVenta.length === 0) {
        alert('⚠️ No hay productos en la venta');
        return;
    }

    const total = productosEnVenta.reduce((sum, p) => sum + p.subtotal, 0);

    console.log('💰 Procesando venta:', {
        productos: productosEnVenta,
        total: total
    });

    alert(`💰 Venta procesada\n\nTotal: $${total.toLocaleString('es-MX', { minimumFractionDigits: 1 })}\n\n(Funcionalidad de cobro pendiente)`);

    // Aquí se implementará la lógica de cobro
});

// ============================================
// EVENT LISTENERS
// ============================================

// Buscar al hacer clic en el botón
btnBuscarJoya.addEventListener('click', buscarJoya);

// Buscar al presionar Enter
inputCodigoJoya.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        buscarJoya();
    }
});

// Limpiar búsqueda al escribir
inputCodigoJoya.addEventListener('input', function () {
    if (this.value.trim() === '') {
        tablaResultadosContainer.style.display = 'none';
    }
});

// Inicializar el botón Cobrar Venta como deshabilitado
btnCobrarVenta.disabled = true;

console.log('✅ Sistema de venta de joyas inicializado');

// ============================================
// MODAL: COBRAR VENTA
// ============================================

// Referencias al modal
const modalCobrarVenta = document.getElementById('modalCobrarVenta');
const btnCerrarModal = document.getElementById('btnCerrarModal');
const btnCancelarModal = document.getElementById('btnCancelarModal');
const btnConfirmarVenta = document.getElementById('btnConfirmarVenta');

// Referencias a elementos del modal
const modalCliente = document.getElementById('modalCliente');
const modalProductos = document.getElementById('modalProductos');
const modalTotal = document.getElementById('modalTotal');
const btnEfectivo = document.getElementById('btnEfectivo');
const btnTarjeta = document.getElementById('btnTarjeta');
const efectivoSection = document.getElementById('efectivoSection');
const cambioSection = document.getElementById('cambioSection');
const inputEfectivoRecibido = document.getElementById('inputEfectivoRecibido');
const cambioMonto = document.getElementById('cambioMonto');

// Variables del modal
let metodoPagoSeleccionado = null;
let totalVenta = 0;

// ============================================
// FUNCIÓN: ABRIR MODAL DE COBRAR VENTA
// ============================================
/*function abrirModalCobrar() {
    if (productosEnVenta.length === 0) {
        alert('⚠️ No hay productos en la venta');
        return;
    }

    // Calcular total
    totalVenta = productosEnVenta.reduce((sum, p) => sum + p.subtotal, 0);

    // Obtener tipo de cliente
    let tipoCliente = 'Público';
    if (tipoClienteActual === 'mayorista') {
        tipoCliente = 'Mayorista';
        // Aquí podrías agregar el nombre del cliente seleccionado si lo guardaste
    }

    // Llenar datos del modal
    modalCliente.textContent = tipoCliente;
    modalProductos.textContent = productosEnVenta.length;
    modalTotal.textContent = `$${totalVenta.toLocaleString('es-MX', { minimumFractionDigits: 1 })}`;

    // Resetear formulario
    resetearModalCobrar();

    // Mostrar modal
    modalCobrarVenta.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevenir scroll
}*/
function abrirModalCobrar() {
    if (productosEnVenta.length === 0) {
        alert('⚠️ No hay productos en la venta');
        return;
    }

    // ⚠️ VALIDAR CLIENTE MAYORISTA
    if (tipoClienteActual === 'mayorista' && !window.clienteSeleccionado) {
        alert('⚠️ Por favor, seleccione un cliente mayorista');
        return;
    }

    totalVenta = productosEnVenta.reduce((sum, p) => sum + p.subtotal, 0);

    // ⚠️ CAMBIAR: Obtener NOMBRE del cliente
    let nombreCliente = 'Público General';
    if (window.clienteSeleccionado) {
        nombreCliente = window.clienteSeleccionado.nombreCompleto;
    }

    // Llenar datos del modal
    modalCliente.textContent = nombreCliente;  // ← AHORA USA EL NOMBRE
    modalProductos.textContent = productosEnVenta.length;
    modalTotal.textContent = `$${totalVenta.toLocaleString('es-MX', { minimumFractionDigits: 1 })}`;

    // Resetear formulario
    resetearModalCobrar();

    // Mostrar modal
    modalCobrarVenta.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// ============================================
// FUNCIÓN: CERRAR MODAL
// ============================================
function cerrarModalCobrar() {
    modalCobrarVenta.classList.remove('show');
    document.body.style.overflow = 'auto'; // Restaurar scroll
    resetearModalCobrar();
}

// ============================================
// FUNCIÓN: RESETEAR MODAL
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
// FUNCIÓN: SELECCIONAR MÉTODO DE PAGO
// ============================================
window.seleccionarMetodo = function (metodo) {
    metodoPagoSeleccionado = metodo;

    // Resetear selección
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
// CALCULAR CAMBIO EN TIEMPO REAL
// ============================================
// Validación del input de efectivo (agregar después de las referencias del modal)
inputEfectivoRecibido.addEventListener('input', function (e) {
    // Eliminar todo lo que no sea número o punto decimal
    this.value = this.value.replace(/[^0-9.]/g, '');

    // Permitir solo un punto decimal
    const parts = this.value.split('.');
    if (parts.length > 2) {
        this.value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Calcular cambio
    const efectivoRecibido = parseFloat(this.value) || 0;

    if (efectivoRecibido >= totalVenta) {
        const cambio = efectivoRecibido - totalVenta;
        cambioMonto.textContent = `$${cambio.toLocaleString('es-MX', { minimumFractionDigits: 1 })}`;
        cambioSection.style.display = 'block';
        btnConfirmarVenta.disabled = false;
    } else {
        cambioSection.style.display = 'none';
        btnConfirmarVenta.disabled = true;
    }
});

// ============================================
// FUNCIÓN: CONFIRMAR VENTA Y GENERAR TICKET
// ============================================

btnConfirmarVenta.addEventListener('click', async function () {
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

    // Llamar a guardar venta
    await guardarVentaBD();
});

// Guardar venta en BD (aquí conectarás con tu backend)

async function guardarVentaBD() {
    // Determinar ID del cliente
    let idCliente = 1;
    let nombreClienteParaTicket = 'Público General';

    if (window.clienteSeleccionado) {
        idCliente = window.clienteSeleccionado.idCliente;
        nombreClienteParaTicket = window.clienteSeleccionado.nombreCompleto;
    }

    // Preparar productos
    const productos = productosEnVenta.map(p => ({
        idProducto: p.codigo,
        cantidad: p.cantidad
    }));

    // Preparar efectivo y cambio
    let efectivoRecibido = null;
    let cambio = null;

    if (metodoPagoSeleccionado === 'efectivo') {
        efectivoRecibido = parseFloat(inputEfectivoRecibido.value);
        cambio = efectivoRecibido - totalVenta;
    }

    // Preparar datos
    const datosVenta = {
        idCliente: idCliente,
        productos: productos,
        metodoPago: metodoPagoSeleccionado,
        efectivoRecibido: efectivoRecibido,
        cambio: cambio
    };

    console.log('💾 Guardando venta:', datosVenta);

    try {
        const response = await fetch(URL_BASE + 'registrarVenta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosVenta)
        });

        console.log('📡 Response status:', response.status);

        const resultado = await response.json();
        console.log('📥 Resultado:', resultado);

        if (resultado.success) {
            alert(`✅ Venta registrada exitosamente!\n\nID Venta: ${resultado.venta.idVenta}\nTotal: $${resultado.venta.montoTotal}\nCliente: ${nombreClienteParaTicket}`);

            // Cerrar modal
            cerrarModalCobrar();

            // Limpiar carrito
            productosEnVenta = [];
            actualizarTablaVenta();
            actualizarTotal();

            // Resetear a público si era mayorista
            if (tipoClienteActual === 'mayorista') {
                limpiarResultadosClientes();
                btnPublico.click(); // Volver a público
            }
        } else {
            alert('❌ Error al guardar venta:\n\n' + resultado.error);
            console.error('Error del servidor:', resultado);
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        alert('Error al registrar la venta. Revisa la consola (F12)');
    }
}

// Función helper para limpiar resultados de clientes
function limpiarResultadosClientes() {
    document.getElementById('inputTelefono').value = '';
    document.getElementById('inputNombreCompleto').value = '';
    document.getElementById('inputAcciones').value = '';
    window.clienteSeleccionado = null;
}

// Cerrar modal
cerrarModalCobrar();
});

// ============================================
// FUNCIÓN: GENERAR TICKET 
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

    // Crear contenido del ticket
    let ticketHTML = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta - Joyería Chabelita</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: 'Courier New', monospace; 
            width: 350px; 
            margin: 20px auto;
            padding: 20px;
            background: white;
        }
        .ticket-container {
            border: 2px dashed #333;
            padding: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h1 { 
            margin: 10px 0 5px 0; 
            font-size: 22px;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .info { 
            margin-bottom: 15px; 
            font-size: 12px; 
            line-height: 1.6;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .info-label {
            font-weight: bold;
        }
        .divider { 
            border-top: 1px dashed #333; 
            margin: 15px 0; 
        }
        .divider-double { 
            border-top: 2px solid #333; 
            margin: 15px 0; 
        }
        .productos { 
            margin: 15px 0; 
        }
        .productos-header {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
            text-decoration: underline;
        }
        .producto-item { 
            margin: 8px 0;
            font-size: 11px;
            line-height: 1.5;
        }
        .producto-codigo {
            font-weight: bold;
            display: block;
        }
        .producto-desc {
            display: block;
            margin-left: 10px;
            color: #333;
        }
        .producto-detalle {
            display: flex;
            justify-content: space-between;
            margin-left: 10px;
            margin-top: 3px;
        }
        .total-section { 
            margin-top: 15px;
        }
        .total-row { 
            display: flex; 
            justify-content: space-between; 
            margin: 5px 0;
            font-size: 12px;
        }
        .total-final { 
            font-weight: bold; 
            font-size: 16px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .pago-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .footer { 
            text-align: center; 
            margin-top: 20px;
            font-size: 11px;
            line-height: 1.6;
        }
        .footer-bold {
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .ticket-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <!-- LOGO - Cambia la ruta por la tuya -->
            <img src="src/assets/image/chabelitanegro.png" alt="Logo Joyería Chabelita" class="logo">
            <h1>JOYERÍA CHABELITA</h1>
            <div class="subtitle">Ticket de Venta</div>
        </div>
        
        <div class="divider"></div>
        
        <div class="info">
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span>${fecha}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Hora:</span>
                <span>${hora}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span>${modalCliente.textContent}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Atendió:</span>
                <span>${document.getElementById('empleadoNombre').textContent}</span>
            </div>
        </div>
        
        <div class="divider-double"></div>
        
        <div class="productos">
            <div class="productos-header">PRODUCTOS</div>
`;

    // Agregar productos con más detalle
    productosEnVenta.forEach((producto, index) => {
        ticketHTML += `
            <div class="producto-item">
                <span class="producto-codigo">${index + 1}. [${producto.codigo}] ${producto.categoria}</span>
                <span class="producto-desc">${producto.descripcion}</span>
                <div class="producto-detalle">
                    <span>${producto.cantidad} x $${producto.precio.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</span>
                    <span>$${producto.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</span>
                </div>
            </div>
`;
    });

    ticketHTML += `
        </div>
        
        <div class="divider-double"></div>
        
        <div class="total-section">
            <div class="total-row total-final">
                <span>TOTAL A PAGAR:</span>
                <span>$${totalVenta.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</span>
            </div>
        </div>
        
        <div class="pago-info">
            <div class="total-row">
                <span class="info-label">Método de pago:</span>
                <span>${metodoPagoSeleccionado === 'efectivo' ? 'EFECTIVO' : 'TARJETA'}</span>
            </div>
`;

    // Si es efectivo, agregar detalles de pago
    if (metodoPagoSeleccionado === 'efectivo') {
        const efectivoRecibido = parseFloat(inputEfectivoRecibido.value);
        const cambio = efectivoRecibido - totalVenta;
        ticketHTML += `
            <div class="total-row">
                <span>Efectivo recibido:</span>
                <span>$${efectivoRecibido.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</span>
            </div>
            <div class="total-row">
                <span>Cambio:</span>
                <span>$${cambio.toLocaleString('es-MX', { minimumFractionDigits: 1 })}</span>
            </div>
`;
    }

    ticketHTML += `
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <div class="footer-bold">¡Gracias por su compra!</div>
            <div>Conserve este ticket como comprobante</div>
            <div>Visítenos pronto</div>
        </div>
    </div>
</body>
</html>
`;

    // Abrir ticket en nueva ventana
    const ventanaTicket = window.open('', '_blank', 'width=400,height=700');
    ventanaTicket.document.write(ticketHTML);
    ventanaTicket.document.close();

    // Imprimir automáticamente después de cargar
    ventanaTicket.onload = function () {
        setTimeout(() => {
            ventanaTicket.print();
        }, 250);
    };
}

// ============================================
// FUNCIÓN: GUARDAR VENTA EN BASE DE DATOS
// ============================================
// 🔹 REEMPLAZAR COMPLETAMENTE la función guardarVentaBD() (línea ~783)
async function guardarVentaBD() {
    // Determinar ID del cliente
    let idCliente = 1; // Por defecto público
    let nombreClienteParaTicket = 'Público General';

    if (tipoClienteActual === 'mayorista' && window.clienteSeleccionado) {
        idCliente = window.clienteSeleccionado.idCliente;
        nombreClienteParaTicket = window.clienteSeleccionado.nombreCompleto;
    }

    // Preparar productos
    const productos = productosEnVenta.map(p => ({
        idProducto: p.codigo,
        cantidad: p.cantidad
    }));

    // Preparar efectivo y cambio
    let efectivoRecibido = null;
    let cambio = null;

    if (metodoPagoSeleccionado === 'efectivo') {
        efectivoRecibido = parseFloat(inputEfectivoRecibido.value);
        cambio = efectivoRecibido - totalVenta;
    }

    // Preparar datos
    const datosVenta = {
        idCliente: idCliente,
        productos: productos,
        metodoPago: metodoPagoSeleccionado,
        efectivoRecibido: efectivoRecibido,
        cambio: cambio
    };

    console.log('💾 Intentando guardar venta:', datosVenta);

    try {
        const response = await fetch(URL_BASE + 'registrarVenta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosVenta)
        });

        console.log('📡 Response status:', response.status);

        const resultado = await response.json();
        console.log('📥 Resultado:', resultado);

        if (resultado.success) {
            alert(`✅ Venta registrada exitosamente!\n\nID Venta: ${resultado.venta.idVenta}\nTotal: $${resultado.venta.montoTotal}\nCliente: ${nombreClienteParaTicket}`);

            // Actualizar el modal con el nombre correcto del cliente
            modalCliente.textContent = nombreClienteParaTicket;

            // Limpiar carrito
            productosEnVenta = [];
            actualizarTablaVenta();
            actualizarTotal();

            // Resetear cliente si es mayorista
            if (tipoClienteActual === 'mayorista') {
                limpiarResultadosClientes();
            }
        } else {
            alert('❌ Error al guardar venta:\n\n' + resultado.error);
            console.error('Error del servidor:', resultado);
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        alert('Error al registrar la venta. Revisa la consola (F12)');
    }
}

// Función helper para limpiar resultados de clientes
function limpiarResultadosClientes() {
    document.getElementById('inputTelefono').value = '';
    document.getElementById('inputNombreCompleto').value = '';
    document.getElementById('inputAcciones').value = '';
    window.clienteSeleccionado = null;
}

// ============================================
// EVENT LISTENERS DEL MODAL
// ============================================

// Modificar el botón Cobrar Venta existente
btnCobrarVenta.removeEventListener('click', btnCobrarVenta.onclick);
btnCobrarVenta.addEventListener('click', abrirModalCobrar);

// Cerrar modal con botón X
btnCerrarModal.addEventListener('click', cerrarModalCobrar);

// Cerrar modal con botón Cancelar
btnCancelarModal.addEventListener('click', cerrarModalCobrar);

// Cerrar modal al hacer clic fuera
modalCobrarVenta.addEventListener('click', function (e) {
    if (e.target === modalCobrarVenta) {
        cerrarModalCobrar();
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modalCobrarVenta.classList.contains('show')) {
        cerrarModalCobrar();
    }
});

console.log('✅ Modal de cobrar venta inicializado');
// AGREGAR DESPUÉS DE limpiarFormularioNuevoCliente()

function limpiarResultadosClientes() {
    document.getElementById('inputTelefono').value = '';
    document.getElementById('inputNombreCompleto').value = '';
    document.getElementById('inputAcciones').value = '';
    window.clienteSeleccionado = null;
}