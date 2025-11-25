// CONFIGURACIÓN
const API_URL = './src/database/crudProducto.php';

let categorias = [];
let proveedores = [];

// ============================================
// 1. CARGAR AL INICIAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    cargarProductos();
    cargarCategorias();
    cargarProveedores();
    configurarEventos();
});

// ============================================
// 2. CONFIGURAR EVENTOS
// ============================================
function configurarEventos() {
    // Botón buscar
    document.getElementById('btnBuscar').addEventListener('click', function () {
        const busqueda = document.getElementById('txtBuscar').value.trim();
        cargarProductos(busqueda);
    });

    // Buscar al presionar Enter
    document.getElementById('txtBuscar').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const busqueda = this.value.trim();
            cargarProductos(busqueda);
        }
    });

    // Botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function () {
        document.getElementById('txtBuscar').value = '';
        cargarProductos();
    });

    // Validaciones en tiempo real para el modal
    validacionesModal();

    // Submit del formulario de edición
    document.getElementById('formEditar').addEventListener('submit', actualizarProducto);
}

// ============================================
// 3. VALIDACIONES DEL MODAL
// ============================================
function validacionesModal() {
    // Código del producto
    document.getElementById('editCodigo').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
        this.value = this.value.replace(/[^A-Za-z0-9_-]/g, '');
        if (this.value.length > 20) {
            this.value = this.value.substring(0, 20);
        }
    });

    // Stock
    document.getElementById('editStock').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Precio de compra
    document.getElementById('editCosto').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }
        if (parseFloat(this.value) > 999999.99) {
            this.value = '999999.99';
        }
    });

    // Precio de venta
    document.getElementById('editPrecioVenta').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }
        if (parseFloat(this.value) > 999999.99) {
            this.value = '999999.99';
        }
    });

    // Gramos
    document.getElementById('editGramos').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }
        if (parseFloat(this.value) > 999.99) {
            this.value = '999.99';
        }
    });

    // Descripción
    document.getElementById('editDescripcion').addEventListener('input', function () {
        if (this.value.length > 100) {
            this.value = this.value.substring(0, 100);
        }
    });
}

// ============================================
// 4. CARGAR PRODUCTOS
// ============================================
async function cargarProductos(busqueda = '') {
    try {
        const url = busqueda ? `${API_URL}?busqueda=${encodeURIComponent(busqueda)}` : API_URL;

        const response = await fetch(url);
        const resultado = await response.json();

        if (resultado.success) {
            mostrarProductos(resultado.data);
        } else {
            mostrarError('Error al cargar productos');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
    }
}

// ============================================
// 5. CARGAR CATEGORÍAS
// ============================================
async function cargarCategorias() {
    try {
        const response = await fetch(`${API_URL}?obtenerCategorias=1`);
        const resultado = await response.json();

        if (resultado.success) {
            categorias = resultado.data;
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}

// ============================================
// 6. CARGAR PROVEEDORES
// ============================================
async function cargarProveedores() {
    try {
        const response = await fetch(`${API_URL}?obtenerProveedores=1`);
        const resultado = await response.json();

        if (resultado.success) {
            proveedores = resultado.data;
        }
    } catch (error) {
        console.error('Error al cargar proveedores:', error);
    }
}

// ============================================
// 7. MOSTRAR PRODUCTOS EN LA TABLA
// ============================================
function mostrarProductos(productos) {
    const tbody = document.getElementById('tbodyProducto');
    const noResultados = document.getElementById('noResultados');

    tbody.innerHTML = '';

    if (productos.length === 0) {
        noResultados.style.display = 'block';
        return;
    }

    noResultados.style.display = 'none';

    productos.forEach(producto => {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${producto.idProducto}</td>
            <td>${producto.nombreCategoria || 'Sin categoría'}</td>
            <td>${producto.kilataje}</td>
            <td>${producto.stock}</td>
            <td>$${parseFloat(producto.precioUnitario).toFixed(2)}</td>
            <td>${parseFloat(producto.gramos).toFixed(2)} gr</td>
            <td>${producto.descripcion}</td>
            <td>$${parseFloat(producto.precioCompra).toFixed(2)}</td>
            <td>${producto.nombreProveedor || 'Sin proveedor'}</td>
            <td class="acciones">
                <button class="btn-accion btn-editar" onclick="abrirModalEditar('${producto.idProducto}')" title="Editar">
                    <img src="./src/assets/icon/editar.png" alt="Editar" width="20" height="20">
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

// ============================================
// 8. ABRIR MODAL DE EDICIÓN
// ============================================
async function abrirModalEditar(idProducto) {
    try {
        const response = await fetch(`${API_URL}?busqueda=${idProducto}`);
        const resultado = await response.json();

        if (resultado.success && resultado.data.length > 0) {
            const producto = resultado.data[0];

            // Llenar campos del formulario
            document.getElementById('editProductoOriginal').value = producto.idProducto;
            document.getElementById('editCodigo').value = producto.idProducto;
            document.getElementById('editStock').value = producto.stock;
            document.getElementById('editKilateje').value = producto.kilataje;
            document.getElementById('editDescripcion').value = producto.descripcion;
            document.getElementById('editCosto').value = parseFloat(producto.precioCompra).toFixed(2);
            document.getElementById('editPrecioVenta').value = parseFloat(producto.precioUnitario).toFixed(2);
            document.getElementById('editGramos').value = parseFloat(producto.gramos).toFixed(2);

            // Llenar select de categorías
            llenarSelectCategorias(producto.idCategoria);

            // Llenar select de proveedores
            llenarSelectProveedores(producto.rfcProveedor);

            document.getElementById('modalEditar').style.display = 'flex';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo cargar la información del producto');
    }
}

// ============================================
// 9. LLENAR SELECT DE CATEGORÍAS
// ============================================
function llenarSelectCategorias(idSeleccionado) {
    const select = document.getElementById('editCategoria');
    select.innerHTML = '<option value="">Seleccione categoría</option>';

    categorias.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.idCategoria;
        option.textContent = cat.nombre;
        if (cat.idCategoria == idSeleccionado) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

// ============================================
// 10. LLENAR SELECT DE PROVEEDORES
// ============================================
function llenarSelectProveedores(rfcSeleccionado) {
    const container = document.getElementById('editProveedorContainer');
    if (!container) {
        // Crear contenedor si no existe
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group';
        formGroup.id = 'editProveedorContainer';
        formGroup.innerHTML = `
            <label>Proveedor</label>
            <select id="editProveedor" class="form-input" required>
                <option value="">Seleccione proveedor</option>
            </select>
        `;

        // Insertar antes del último form-group (botones)
        const modalFooter = document.querySelector('.modal-footer');
        modalFooter.parentNode.insertBefore(formGroup, modalFooter);
    }

    const select = document.getElementById('editProveedor');
    select.innerHTML = '<option value="">Seleccione proveedor</option>';

    proveedores.forEach(prov => {
        const option = document.createElement('option');
        option.value = prov.rfc;
        option.textContent = prov.razonSocial;
        if (prov.rfc === rfcSeleccionado) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

// ============================================
// 11. CERRAR MODAL
// ============================================
function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
}

// ============================================
// 12. ACTUALIZAR PRODUCTO
// ============================================
async function actualizarProducto(e) {
    e.preventDefault();

    const datos = {
        idProductoOriginal: document.getElementById('editProductoOriginal').value,
        idProducto: document.getElementById('editCodigo').value.trim(),
        idCategoria: document.getElementById('editCategoria').value,
        rfcProveedor: document.getElementById('editProveedor').value,
        stock: document.getElementById('editStock').value,
        kilataje: document.getElementById('editKilateje').value,
        descripcion: document.getElementById('editDescripcion').value.trim(),
        precioCompra: document.getElementById('editCosto').value,
        precioUnitario: document.getElementById('editPrecioVenta').value,
        gramos: document.getElementById('editGramos').value
    };

    // Validaciones
    if (!datos.idProducto) {
        mostrarError('El código del producto es obligatorio');
        return;
    }

    if (!datos.idCategoria) {
        mostrarError('Debe seleccionar una categoría');
        return;
    }

    if (!datos.rfcProveedor) {
        mostrarError('Debe seleccionar un proveedor');
        return;
    }

    if (parseFloat(datos.precioUnitario) <= parseFloat(datos.precioCompra)) {
        mostrarError('El precio de venta debe ser mayor al precio de compra');
        return;
    }

    try {
        const btnSubmit = document.querySelector('#formEditar button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
        }

        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarExito(resultado.message);
            cerrarModal();
            cargarProductos();
        } else {
            mostrarError(resultado.message);
        }

        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo actualizar el producto');

        const btnSubmit = document.querySelector('#formEditar button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    }
}

// ============================================
// 15. MOSTRAR MENSAJES
// ============================================
function mostrarError(mensaje) {
    alert('❌ ERROR:\n\n' + mensaje);
}

function mostrarExito(mensaje) {
    alert('✅ ÉXITO:\n\n' + mensaje);
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function (event) {
    const modal = document.getElementById('modalEditar');
    if (event.target === modal) {
        cerrarModal();
    }
}