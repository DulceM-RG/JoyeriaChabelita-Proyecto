// CONFIGURACIÓN
const API_URL = './src/database/empleadoCRUD.php';

// ============================================
// 1. CARGAR AL INICIAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    cargarEmpleados();
    configurarEventos();
});

// ============================================
// 2. CONFIGURAR EVENTOS
// ============================================
function configurarEventos() {
    // Botón buscar
    document.getElementById('btnBuscar').addEventListener('click', function () {
        const busqueda = document.getElementById('txtBuscar').value.trim();
        const inactivos = document.getElementById('chkMostrarInactivos').checked;
        cargarEmpleados(busqueda, inactivos);
    });

    // Buscar al presionar Enter
    document.getElementById('txtBuscar').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const busqueda = this.value.trim();
            const inactivos = document.getElementById('chkMostrarInactivos').checked;
            cargarEmpleados(busqueda, inactivos);
        }
    });

    // Botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function () {
        document.getElementById('txtBuscar').value = '';
        document.getElementById('chkMostrarInactivos').checked = false;
        cargarEmpleados();
    });

    // Checkbox de inactivos
    document.getElementById('chkMostrarInactivos').addEventListener('change', function () {
        const busqueda = document.getElementById('txtBuscar').value.trim();
        cargarEmpleados(busqueda, this.checked);
    });

    // Validaciones en tiempo real del modal
    validacionesModal();

    // Submit del formulario de edición
    document.getElementById('formEditar').addEventListener('submit', actualizarEmpleado);
}

// ============================================
// 3. VALIDACIONES DEL MODAL
// ============================================
function validacionesModal() {
    // Nombre: solo letras
    document.getElementById('editNombre').addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (this.value.length > 30) {
            this.value = this.value.substring(0, 30);
        }
    });

    // Apellido paterno: solo letras
    document.getElementById('editApellidoPaterno').addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (this.value.length > 30) {
            this.value = this.value.substring(0, 30);
        }
    });

    // Apellido materno: solo letras
    document.getElementById('editApellidoMaterno').addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (this.value.length > 30) {
            this.value = this.value.substring(0, 30);
        }
    });

    // Teléfono: solo números, 10 dígitos
    document.getElementById('editTelefono').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
        }
    });

    // Código postal: solo números, 5 dígitos
    document.getElementById('editCodigoPostal').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 5) {
            this.value = this.value.substring(0, 5);
        }
    });

    // Número de calle: solo números
    document.getElementById('editNumeroCalle').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
}

// ============================================
// 4. CARGAR EMPLEADOS
// ============================================
async function cargarEmpleados(busqueda = '', mostrarInactivos = false) {
    try {
        let url = API_URL;
        const params = new URLSearchParams();

        if (busqueda) {
            params.append('busqueda', busqueda);
        }
        if (mostrarInactivos) {
            params.append('inactivos', '1');
        }

        if (params.toString()) {
            url += '?' + params.toString();
        }

        const response = await fetch(url);
        const resultado = await response.json();

        if (resultado.success) {
            mostrarEmpleados(resultado.data);
        } else {
            mostrarError('Error al cargar empleados');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
    }
}

// ============================================
// 5. MOSTRAR EMPLEADOS EN LA TABLA
// ============================================
function mostrarEmpleados(empleados) {
    const tbody = document.getElementById('tbodyEmpleado');
    const noResultados = document.getElementById('noResultados');

    tbody.innerHTML = '';

    if (empleados.length === 0) {
        noResultados.style.display = 'block';
        return;
    }

    noResultados.style.display = 'none';

    empleados.forEach(empleado => {
        const tr = document.createElement('tr');

        // Formatear nombre completo
        const nombreCompleto = `${empleado.nombre} ${empleado.apellidoPaterno} ${empleado.apellidoMaterno}`;

        // Formatear teléfono
        const telefonoFormateado = formatearTelefono(empleado.telefono);

        // Formatear dirección
        const direccion = `${empleado.nombreCalle} #${empleado.numeroCalle}, ${empleado.localidad}, CP ${empleado.codigoPostal}`;

        // Estado con badge
        const estadoBadge = empleado.activo === 'Activo'
            ? '<span class="badge badge-activo">Activo</span>'
            : '<span class="badge badge-inactivo">Baja</span>';

        // Deshabilitar edición/eliminación si está inactivo
        const botonesAccion = empleado.activo === 'Activo'
            ? `<button class="btn-accion btn-editar" onclick="abrirModalEditar(${empleado.idEmpleado})" title="Editar">
                    <img src="./src/assets/icon/editar.png" alt="Editar" width="20" height="20">
               </button>
               <button class="btn-accion btn-eliminar" onclick="confirmarEliminar(${empleado.idEmpleado}, '${nombreCompleto}')" title="Eliminar">
                    <img src="./src/assets/icon/borrar126.png" alt="Eliminar" width="20" height="20">
               </button>`
            : `<button class="btn-accion btn-editar" onclick="abrirModalEditar(${empleado.idEmpleado})" title="Editar">
                    <img src="./src/assets/icon/editar.png" alt="Editar" width="20" height="20">
               </button>
               <span style="color: #999; font-size: 12px;">Sin eliminar</span>`;

        tr.innerHTML = `
            <td>${empleado.idControl || 'N/A'}</td>
            <td>${nombreCompleto}</td>
            <td>${telefonoFormateado}</td>
            <td>${empleado.nombrePuesto}</td>
            <td style="font-size: 12px;">${direccion}</td>
            <td>${estadoBadge}</td>
            <td class="acciones">
                ${botonesAccion}
            </td>
        `;

        // Resaltar fila si está inactivo
        if (empleado.activo === 'Baja') {
            tr.style.backgroundColor = '#f5f5f5';
            tr.style.opacity = '0.7';
        }

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
async function abrirModalEditar(idEmpleado) {
    try {
        const response = await fetch(`${API_URL}?busqueda=${idEmpleado}`);
        const resultado = await response.json();

        if (resultado.success && resultado.data.length > 0) {
            const empleado = resultado.data.find(e => e.idEmpleado == idEmpleado);

            if (!empleado) {
                mostrarError('No se encontró el empleado');
                return;
            }

            // Llenar campos del formulario
            document.getElementById('editIdEmpleado').value = empleado.idEmpleado;
            document.getElementById('editIdDireccion').value = empleado.idDireccion;
            document.getElementById('editIdControl').value = empleado.idControl;
            document.getElementById('editNombre').value = empleado.nombre;
            document.getElementById('editApellidoPaterno').value = empleado.apellidoPaterno;
            document.getElementById('editApellidoMaterno').value = empleado.apellidoMaterno;
            document.getElementById('editTelefono').value = empleado.telefono;
            document.getElementById('editPuesto').value = empleado.nombrePuesto;
            document.getElementById('editEstado').value = empleado.activo;

            // Dirección
            document.getElementById('editCalle').value = empleado.nombreCalle;
            document.getElementById('editNumeroCalle').value = empleado.numeroCalle;
            document.getElementById('editLocalidad').value = empleado.localidad;
            document.getElementById('editCodigoPostal').value = empleado.codigoPostal;

            document.getElementById('modalEditar').style.display = 'flex';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo cargar la información del empleado');
    }
}

// ============================================
// 8. CERRAR MODAL
// ============================================
function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
}

// ============================================
// 9. ACTUALIZAR EMPLEADO
// ============================================
async function actualizarEmpleado(e) {
    e.preventDefault();

    const datos = {
        idEmpleado: document.getElementById('editIdEmpleado').value,
        nombre: document.getElementById('editNombre').value.trim(),
        apellidoPaterno: document.getElementById('editApellidoPaterno').value.trim(),
        apellidoMaterno: document.getElementById('editApellidoMaterno').value.trim(),
        telefono: document.getElementById('editTelefono').value.trim(),
        idDireccion: document.getElementById('editIdDireccion').value,
        nombreCalle: document.getElementById('editCalle').value.trim(),
        numeroCalle: document.getElementById('editNumeroCalle').value.trim(),
        localidad: document.getElementById('editLocalidad').value.trim(),
        codigoPostal: document.getElementById('editCodigoPostal').value.trim(),
        activo: document.getElementById('editEstado').value
    };

    // Validaciones
    if (!datos.nombre || !datos.apellidoPaterno || !datos.apellidoMaterno) {
        mostrarError('Todos los campos de nombre son obligatorios');
        return;
    }

    if (datos.telefono.length !== 10) {
        mostrarError('El teléfono debe tener exactamente 10 dígitos');
        return;
    }

    if (datos.codigoPostal.length !== 5) {
        mostrarError('El código postal debe tener exactamente 5 dígitos');
        return;
    }

    if (!datos.activo) {
        mostrarError('Debe seleccionar un estado válido');
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
            cargarEmpleados();
        } else {
            mostrarError(resultado.message);
        }

        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo actualizar el empleado');

        const btnSubmit = document.querySelector('#formEditar button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    }
}

// ============================================
// 10. CONFIRMAR ELIMINACIÓN
// ============================================
function confirmarEliminar(idEmpleado, nombreCompleto) {
    const confirmacion = confirm(
        `⚠️ ADVERTENCIA: Esta acción ELIMINARÁ PERMANENTEMENTE al empleado\n\n` +
        `Empleado: ${nombreCompleto}\n` +
        `ID: ${idEmpleado}\n\n` +
        `Se eliminarán:\n` +
        `- Datos del empleado\n` +
        `- Credenciales de acceso\n` +
        `- Dirección asociada\n\n` +
        `¿Está COMPLETAMENTE SEGURO de continuar?\n` +
        `Esta acción NO se puede deshacer.`
    );

    if (confirmacion) {
        eliminarEmpleado(idEmpleado);
    }
}

// ============================================
// 11. ELIMINAR EMPLEADO
// ============================================
async function eliminarEmpleado(idEmpleado) {
    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idEmpleado: idEmpleado })
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarExito(resultado.message);
            cargarEmpleados();
        } else {
            mostrarError(resultado.message);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo eliminar el empleado');
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