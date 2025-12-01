// CONFIGURACIÓN
const API_URL = './src/database/registrarProducto.php';

// VARIABLES GLOBALES
let proveedores = [];

// ============================================
// 1. CARGAR DATOS AL INICIAR
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    cargarProveedores();
    cargarCategorias();
    configurarEventos();
});

// ============================================
// 2. CARGAR PROVEEDORES DESDE LA BD
// ============================================
async function cargarProveedores() {
    try {
        const response = await fetch(`${API_URL}?action=proveedores`);
        const data = await response.json();

        if (data.success) {
            proveedores = data.data;
            console.log('Proveedores cargados:', proveedores.length);
        } else {
            mostrarError('Error al cargar proveedores');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
    }
}

// ============================================
// 3. CARGAR CATEGORÍAS DESDE LA BD
// ============================================
async function cargarCategorias() {
    try {
        const response = await fetch(`${API_URL}?action=categorias`);
        const data = await response.json();

        if (data.success) {
            const select = document.getElementById('selCategoria');
            select.innerHTML = '<option value="">Selecciona Categoría</option>';

            data.data.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.idCategoria;
                option.textContent = categoria.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}

// ============================================
// 4. CONFIGURAR EVENTOS
// ============================================
function configurarEventos() {
    // Evento de búsqueda de proveedor
    document.getElementById('txtProveedor').addEventListener('input', buscarProveedor);

    // Evento para cambiar proveedor
    document.getElementById('btnCambiarProveedor').addEventListener('click', cambiarProveedor);

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.icon-input')) {
            document.getElementById('sugerenciasProveedor').style.display = 'none';
        }
    });

    // Validación en tiempo real de descripción
    document.getElementById('txtDescripcion').addEventListener('input', function (e) {
        const maxLength = 100;
        const currentLength = e.target.value.length;

        if (currentLength > maxLength) {
            e.target.value = e.target.value.substring(0, maxLength);
            mostrarError(`La descripción no puede exceder ${maxLength} caracteres`);
        }
    });

    // Validación en tiempo real de gramos
    document.getElementById('txtGramos').addEventListener('input', function (e) {
        // Permitir solo números y punto decimal
        this.value = this.value.replace(/[^0-9.]/g, '');

        // Evitar múltiples puntos decimales
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }

        // Limitar a 2 decimales
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }

        // Validar máximo
        const valor = parseFloat(this.value);
        if (valor > 999.99) {
            this.value = '999.99';
            mostrarError('Los gramos no pueden exceder 999.99');
        }
    });

    // Validación en tiempo real de precio de compra
    document.getElementById('txtPrecioCompra').addEventListener('input', function (e) {
        // Permitir solo números y punto decimal
        this.value = this.value.replace(/[^0-9.]/g, '');

        // Evitar múltiples puntos decimales
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }

        // Limitar a 2 decimales
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }

        // Validar máximo
        const valor = parseFloat(this.value);
        if (valor > 999999.99) {
            this.value = '999999.99';
            mostrarError('El precio de compra no puede exceder $999,999.99');
        }

        validarMargenGanancia();
    });

    // Validación en tiempo real de precio de venta
    document.getElementById('txtPrecio').addEventListener('input', function (e) {
        // Permitir solo números y punto decimal
        this.value = this.value.replace(/[^0-9.]/g, '');

        // Evitar múltiples puntos decimales
        const partes = this.value.split('.');
        if (partes.length > 2) {
            this.value = partes[0] + '.' + partes.slice(1).join('');
        }

        // Limitar a 2 decimales
        if (partes[1] && partes[1].length > 2) {
            this.value = partes[0] + '.' + partes[1].substring(0, 2);
        }

        // Validar máximo
        const valor = parseFloat(this.value);
        if (valor > 999999.99) {
            this.value = '999999.99';
            mostrarError('El precio de venta no puede exceder $999,999.99');
        }

        validarMargenGanancia();
    });

    // Validación de stock (solo números enteros)
    document.getElementById('txtStock').addEventListener('input', function (e) {
        // Solo números enteros
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });

    // Validación de código de producto
    document.getElementById('txtCodigo').addEventListener('input', function (e) {
        const maxLength = 20;
        if (e.target.value.length > maxLength) {
            e.target.value = e.target.value.substring(0, maxLength);
            mostrarError(`El código no puede exceder ${maxLength} caracteres`);
        }
        // Solo permitir letras, números, guiones y guiones bajos
        e.target.value = e.target.value.replace(/[^A-Za-z0-9_-]/g, '');
    });

    // Enviar formulario
    document.getElementById('registroForm').addEventListener('submit', registrarProducto);
}

// ============================================
// 5. BUSCAR Y FILTRAR PROVEEDORES
// ============================================
function buscarProveedor(e) {
    const busqueda = e.target.value.toLowerCase().trim();
    const sugerenciasDiv = document.getElementById('sugerenciasProveedor');

    if (busqueda.length < 2) {
        sugerenciasDiv.style.display = 'none';
        return;
    }

    // Filtrar proveedores por RFC o Razón Social
    const resultados = proveedores.filter(p =>
        p.rfc.toLowerCase().includes(busqueda) ||
        p.razonSocial.toLowerCase().includes(busqueda)
    );

    if (resultados.length === 0) {
        sugerenciasDiv.innerHTML = '<div class="sugerencia-item" style="color: #999; cursor: default;">No se encontraron proveedores</div>';
        sugerenciasDiv.style.display = 'block';
        return;
    }

    // Mostrar resultados (máximo 5)
    sugerenciasDiv.innerHTML = '';
    resultados.slice(0, 5).forEach(proveedor => {
        const item = document.createElement('div');
        item.className = 'sugerencia-item';
        item.innerHTML = `
            <strong>${proveedor.rfc}</strong><br>
            <small>${proveedor.razonSocial}</small>
        `;

        item.addEventListener('click', () => seleccionarProveedor(proveedor));
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = 'block';
}

// ============================================
// 6. SELECCIONAR PROVEEDOR
// ============================================
function seleccionarProveedor(proveedor) {
    // Ocultar sugerencias
    document.getElementById('sugerenciasProveedor').style.display = 'none';

    // Limpiar y deshabilitar input
    document.getElementById('txtProveedor').value = '';
    document.getElementById('txtProveedor').disabled = true;

    // Mostrar información del proveedor seleccionado
    document.getElementById('rfcSeleccionado').textContent = proveedor.rfc;
    document.getElementById('razonSocialSeleccionada').textContent = proveedor.razonSocial;
    document.getElementById('telefonoSeleccionado').textContent = proveedor.telefono || 'N/A';
    document.getElementById('proveedorSeleccionado').style.display = 'block';

    // Guardar RFC en campo oculto
    document.getElementById('rfcProveedorHidden').value = proveedor.rfc;
}

// ============================================
// 7. CAMBIAR PROVEEDOR
// ============================================
function cambiarProveedor() {
    document.getElementById('txtProveedor').value = '';
    document.getElementById('txtProveedor').disabled = false;
    document.getElementById('proveedorSeleccionado').style.display = 'none';
    document.getElementById('rfcProveedorHidden').value = '';
    document.getElementById('txtProveedor').focus();
}

// ============================================
// 8. VALIDACIONES ANTES DE ENVIAR
// ============================================
function validarFormulario() {
    const errores = [];

    // 1. Código del producto
    const codigo = document.getElementById('txtCodigo').value.trim();
    if (!codigo) {
        errores.push('El código del producto es obligatorio');
    } else if (codigo.length > 20) {
        errores.push('El código no puede exceder 20 caracteres');
    } else if (!/^[A-Za-z0-9_-]+$/.test(codigo)) {
        errores.push('El código solo puede contener letras, números, guiones y guiones bajos');
    }

    // 2. Categoría
    const categoria = document.getElementById('selCategoria').value;
    if (!categoria) {
        errores.push('Debe seleccionar una categoría');
    }

    // 3. Kilataje
    const kilataje = document.getElementById('selKilataje').value;
    if (!kilataje) {
        errores.push('Debe seleccionar el kilataje');
    }

    // 4. Stock
    const stock = document.getElementById('txtStock').value.trim();
    if (!stock) {
        errores.push('El stock es obligatorio');
    } else if (isNaN(stock) || parseInt(stock) < 0) {
        errores.push('El stock debe ser un número positivo');
    }

    // 5. Precio de Compra
    const precioCompra = document.getElementById('txtPrecioCompra').value.trim();
    if (!precioCompra) {
        errores.push('El precio de compra es obligatorio');
    } else if (isNaN(precioCompra) || parseFloat(precioCompra) <= 0) {
        errores.push('El precio de compra debe ser mayor a 0');
    } else if (parseFloat(precioCompra) > 999999.99) {
        errores.push('El precio de compra no puede exceder $999,999.99');
    }

    // 6. Precio de Venta
    const precio = document.getElementById('txtPrecio').value.trim();
    if (!precio) {
        errores.push('El precio de venta es obligatorio');
    } else if (isNaN(precio) || parseFloat(precio) <= 0) {
        errores.push('El precio de venta debe ser mayor a 0');
    } else if (parseFloat(precio) > 999999.99) {
        errores.push('El precio de venta no puede exceder $999,999.99');
    }

    // 6.1 Validar que precio de venta sea mayor que precio de compra
    if (precioCompra && precio) {
        if (parseFloat(precio) <= parseFloat(precioCompra)) {
            errores.push('El precio de venta debe ser mayor al precio de compra');
        }
    }

    // 7. Gramos
    const gramos = document.getElementById('txtGramos').value.trim();
    if (!gramos) {
        errores.push('Los gramos son obligatorios');
    } else if (isNaN(gramos) || parseFloat(gramos) <= 0) {
        errores.push('Los gramos deben ser mayor a 0');
    } else if (parseFloat(gramos) > 999.99) {
        errores.push('Los gramos no pueden exceder 999.99');
    }

    // 8. Descripción
    const descripcion = document.getElementById('txtDescripcion').value.trim();
    if (!descripcion) {
        errores.push('La descripción es obligatoria');
    } else if (descripcion.length < 3) {
        errores.push('La descripción debe tener al menos 3 caracteres');
    } else if (descripcion.length > 100) {
        errores.push('La descripción no puede exceder 100 caracteres');
    }

    // 9. Proveedor
    const rfcProveedor = document.getElementById('rfcProveedorHidden').value;
    if (!rfcProveedor) {
        errores.push('Debe seleccionar un proveedor');
    }

    return errores;
}

// ============================================
// 9. REGISTRAR PRODUCTO
// ============================================
async function registrarProducto(e) {
    e.preventDefault();

    // Validar formulario
    const errores = validarFormulario();
    if (errores.length > 0) {
        mostrarError(errores.join('\n'));
        return;
    }

    // Preparar datos
    const datos = {
        idProducto: document.getElementById('txtCodigo').value.trim(),
        idCategoria: document.getElementById('selCategoria').value,
        rfcProveedor: document.getElementById('rfcProveedorHidden').value,
        stock: parseInt(document.getElementById('txtStock').value),
        kilataje: document.getElementById('selKilataje').value,
        descripcion: document.getElementById('txtDescripcion').value.trim(),
        precioCompra: parseFloat(document.getElementById('txtPrecioCompra').value),
        precioUnitario: parseFloat(document.getElementById('txtPrecio').value),
        gramos: parseFloat(document.getElementById('txtGramos').value)
    };

    try {
        // Mostrar indicador de carga
        const btnSubmit = document.querySelector('button[type="submit"]');
        if (btnSubmit) btnSubmit.disabled = true;

        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarExito(resultado.message);
            // Limpiar formulario después de 2 segundos
            setTimeout(() => {
                limpiarFormulario();
                if (btnSubmit) btnSubmit.disabled = false;
            }, 500);
        } else {
            mostrarError(resultado.message);
            if (btnSubmit) btnSubmit.disabled = false;
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
        const btnSubmit = document.querySelector('button[type="submit"]');
        if (btnSubmit) btnSubmit.disabled = false;
    }
}

// ============================================
// 10. LIMPIAR FORMULARIO
// ============================================
function limpiarFormulario() {
    document.getElementById('registroForm').reset();
    document.getElementById('txtProveedor').disabled = false;
    document.getElementById('proveedorSeleccionado').style.display = 'none';
    document.getElementById('rfcProveedorHidden').value = '';
}

// ============================================
// 11. MOSTRAR MENSAJES
// ============================================
function mostrarError(mensaje) {
    alert('❌ ERROR:\n\n' + mensaje);
}

function mostrarExito(mensaje) {
    alert('✅ ÉXITO:\n\n' + mensaje);
}

// ============================================
// 12. VALIDAR MARGEN DE GANANCIA
// ============================================
function validarMargenGanancia() {
    const precioCompra = parseFloat(document.getElementById('txtPrecioCompra').value);
    const precioVenta = parseFloat(document.getElementById('txtPrecio').value);

    if (!isNaN(precioCompra) && !isNaN(precioVenta) && precioCompra > 0 && precioVenta > 0) {
        const ganancia = precioVenta - precioCompra;
        const porcentaje = ((ganancia / precioCompra) * 100).toFixed(2);

        // Mostrar mensaje informativo
        console.log(`Ganancia: ${ganancia.toFixed(2)} (${porcentaje}%)`);

        if (precioVenta <= precioCompra) {
            mostrarError('⚠️ El precio de venta debe ser mayor al precio de compra');
        }
    }
}

// ============================================
// 13. CARGAR KILATAJES (Valores fijos del ENUM)
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const selKilataje = document.getElementById('selKilataje');
    const kilatajes = ['8K', '10K', '14K', '18k'];

    kilatajes.forEach(k => {
        const option = document.createElement('option');
        option.value = k;
        option.textContent = k;
        selKilataje.appendChild(option);
    });
});