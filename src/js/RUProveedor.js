// CONFIGURACIÓN
const API_URL = './src/database/crudProveedores.php';
// CONFIGURACIÓN

// ============================================
// 1. CARGAR AL INICIAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function () {
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
        cargarProveedores(busqueda);
    });

    // Buscar al presionar Enter
    document.getElementById('txtBuscar').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const busqueda = this.value.trim();
            cargarProveedores(busqueda);
        }
    });

    // Botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function () {
        document.getElementById('txtBuscar').value = '';
        cargarProveedores();
    });

    // Validación del RFC en modal de edición
    document.getElementById('editRfc').addEventListener('input', function () {
        // Convertir a mayúsculas
        this.value = this.value.toUpperCase();

        // Permitir solo letras, números, Ñ y &
        this.value = this.value.replace(/[^A-ZÑ&0-9]/g, '');

        // Limitar a 13 caracteres
        if (this.value.length > 13) {
            this.value = this.value.substring(0, 13);
        }

        // Validación visual
        validarRfcVisual(this.value);
    });

    // Validación del teléfono en modal de edición
    document.getElementById('editTelefono').addEventListener('input', function () {
        // Solo números
        this.value = this.value.replace(/[^0-9]/g, '');

        // Máximo 10 dígitos
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
        }
    });

    // Validación de razón social en modal
    document.getElementById('editRazonSocial').addEventListener('input', function () {
        const maxLength = 100;
        if (this.value.length > maxLength) {
            this.value = this.value.substring(0, maxLength);
        }
        // Permitir solo caracteres válidos
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&\-]/g, '');
    });

    // Submit del formulario de edición
    document.getElementById('formEditar').addEventListener('submit', actualizarProveedor);
}

// ============================================
// 3. VALIDAR RFC VISUAL
// ============================================
function validarRfcVisual(rfc) {
    const inputRfc = document.getElementById('editRfc');
    const longitud = rfc.length;

    if (longitud === 0) {
        inputRfc.style.borderColor = '';
        return;
    }

    if (longitud === 12 || longitud === 13) {
        inputRfc.style.borderColor = '#4caf50'; // Verde
    } else {
        inputRfc.style.borderColor = '#ff9800'; // Naranja
    }
}

// ============================================
// 4. CARGAR PROVEEDORES
// ============================================
async function cargarProveedores(busqueda = '') {
    try {
        const url = busqueda ? `${API_URL}?busqueda=${encodeURIComponent(busqueda)}` : API_URL;

        const response = await fetch(url);
        const resultado = await response.json();

        if (resultado.success) {
            mostrarProveedores(resultado.data);
        } else {
            mostrarError('Error al cargar proveedores');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
    }
}

// ============================================
// 5. MOSTRAR PROVEEDORES EN LA TABLA
// ============================================
function mostrarProveedores(proveedores) {
    const tbody = document.getElementById('tbodyProveedor');
    const noResultados = document.getElementById('noResultados');

    tbody.innerHTML = '';

    if (proveedores.length === 0) {
        noResultados.style.display = 'block';
        return;
    }

    noResultados.style.display = 'none';

    proveedores.forEach(proveedor => {
        const tr = document.createElement('tr');

        const telefonoFormateado = proveedor.telefono
            ? formatearTelefono(proveedor.telefono)
            : 'Sin teléfono';

        tr.innerHTML = `
            <td>${proveedor.rfc}</td>
            <td>${proveedor.razonSocial}</td>
            <td>${telefonoFormateado}</td>
            <td class="acciones">
                <button class="btn-accion btn-editar" onclick="abrirModalEditar('${proveedor.rfc}')" title="Editar">
                    <img src="./src/assets/icon/editar.png" alt="Editar" width="20" height="20">
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

// ============================================
// 6. FORMATEAR TELÉFONO
// ============================================
function formatearTelefono(telefono) {
    const tel = telefono.toString();
    if (tel.length === 10) {
        return `${tel.substring(0, 3)}-${tel.substring(3, 6)}-${tel.substring(6)}`;
    }
    return tel;
}

// ============================================
// 7. ABRIR MODAL DE EDICIÓN
// ============================================
async function abrirModalEditar(rfc) {
    try {
        // Verificar si tiene productos
        const responseVerificar = await fetch(`${API_URL}?verificarProductos=${rfc}`);
        const verificacion = await responseVerificar.json();

        // Obtener datos del proveedor
        const response = await fetch(`${API_URL}?busqueda=${rfc}`);
        const resultado = await response.json();

        if (resultado.success && resultado.data.length > 0) {
            const proveedor = resultado.data[0];

            document.getElementById('editRfcOriginal').value = proveedor.rfc;
            document.getElementById('editRfc').value = proveedor.rfc;
            document.getElementById('editRazonSocial').value = proveedor.razonSocial;
            document.getElementById('editTelefono').value = proveedor.telefono || '';

            // Configurar campo RFC según si tiene productos
            const inputRfc = document.getElementById('editRfc');
            const advertenciaDiv = document.getElementById('advertenciaRfc');

            if (verificacion.tieneProductos) {
                // NO puede editar RFC
                inputRfc.disabled = true;
                inputRfc.style.backgroundColor = '#f5f5f5';
                inputRfc.style.cursor = 'not-allowed';

                // Mostrar advertencia
                if (advertenciaDiv) {
                    advertenciaDiv.innerHTML = `
                        <small style="color: #ff9800; display: block; margin-top: 5px;">
                            ⚠️ RFC no editable: tiene ${verificacion.totalProductos} producto(s) asociado(s)
                        </small>
                    `;
                }
            } else {
                // SÍ puede editar RFC
                inputRfc.disabled = false;
                inputRfc.style.backgroundColor = '';
                inputRfc.style.cursor = '';

                // Mostrar mensaje informativo
                if (advertenciaDiv) {
                    advertenciaDiv.innerHTML = `
                        <small style="color: #4caf50; display: block; margin-top: 5px;">
                            ✅ Puede editar el RFC porque no tiene productos asociados
                        </small>
                    `;
                }
            }

            document.getElementById('modalEditar').style.display = 'flex';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo cargar la información del proveedor');
    }
}

// ============================================
// 8. CERRAR MODAL
// ============================================
function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
    document.getElementById('editRfc').style.borderColor = '';
}

// ============================================
// 9. ACTUALIZAR PROVEEDOR
// ============================================
async function actualizarProveedor(e) {
    e.preventDefault();

    const rfcOriginal = document.getElementById('editRfcOriginal').value;
    const rfcNuevo = document.getElementById('editRfc').value.trim().toUpperCase();
    const razonSocial = document.getElementById('editRazonSocial').value.trim();
    const telefono = document.getElementById('editTelefono').value.trim();

    // Validaciones
    if (!rfcNuevo) {
        mostrarError('El RFC es obligatorio');
        return;
    }

    const longitudRfc = rfcNuevo.length;
    if (longitudRfc < 12 || longitudRfc > 13) {
        mostrarError('El RFC debe tener 12 o 13 caracteres');
        return;
    }

    if (!razonSocial) {
        mostrarError('La razón social es obligatoria');
        return;
    }

    if (razonSocial.length < 3) {
        mostrarError('La razón social debe tener al menos 3 caracteres');
        return;
    }

    if (telefono && telefono.length !== 10) {
        mostrarError('El teléfono debe tener exactamente 10 dígitos');
        return;
    }

    // Confirmar si el RFC cambió
    if (rfcOriginal !== rfcNuevo) {
        const confirmacion = confirm(
            `⚠️ ATENCIÓN: Está cambiando el RFC\n\n` +
            `RFC actual: ${rfcOriginal}\n` +
            `RFC nuevo: ${rfcNuevo}\n\n` +
            `¿Está seguro de continuar?`
        );

        if (!confirmacion) {
            return;
        }
    }

    const datos = {
        rfcOriginal: rfcOriginal,
        rfc: rfcNuevo,
        razonSocial: razonSocial,
        telefono: telefono
    };

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
            cargarProveedores();
        } else {
            mostrarError(resultado.message);
        }

        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo actualizar el proveedor');

        const btnSubmit = document.querySelector('#formEditar button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    }
}



// ============================================
// 12. MOSTRAR MENSAJES
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