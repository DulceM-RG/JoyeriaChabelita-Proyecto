// ===== CLIENTES MAYORISTAS - INTERACTIVIDAD =====
// Archivo: dataFormClientesMayoristas.js
// Descripción: Gestión completa de clientes mayoristas con búsqueda, creación, edición y eliminación

document.addEventListener('DOMContentLoaded', function () {
    // ==================== CONFIGURACIÓN ====================

    // Variable para rastrear la fila que está editando
    let filaEnEdicion = null;


    const API_URL = './src/database/clientes.php';

    console.log('✅ Iniciando carga de clientes...');
    console.log('📍 API URL:', API_URL);

    // Cargar los datos al iniciar
    cargarClientesDesdeDB();

    // ==================== EVENTOS DE BÚSQUEDA ====================

    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const inputBusqueda = document.getElementById('inputBusqueda');

    if (btnBuscar) {
        btnBuscar.addEventListener('click', realizarBusqueda);
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarBusqueda);
    }

    if (inputBusqueda) {
        inputBusqueda.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                realizarBusqueda();
            }
        });
    }

    // ==================== FUNCIONES DE CARGA ====================

    /**
     * Carga todos los clientes desde la base de datos
     */
    function cargarClientesDesdeDB() {
        const tbody = document.getElementById('tbodyClientes');

        if (!tbody) {
            console.error('❌ No se encontró el elemento tbodyClientes');
            return;
        }

        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px;">Cargando clientes...</td></tr>';

        console.log('📡 Enviando solicitud: obtenerTodos');

        // Realizar petición AJAX con JSON
        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'obtenerTodos'
            })
        })
            .then(response => {
                console.log('📩 Respuesta recibida:', response.status);
                console.log('🌐 URL:', response.url);

                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Datos recibidos:', data);

                if (data.success) {
                    if (data.clientes && data.clientes.length > 0) {
                        cargarClientesEnTabla(data.clientes);
                        mostrarMensaje(data.clientes.length + ' clientes cargados', 'exito');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No hay clientes</td></tr>';
                    }
                } else {
                    mostrarError(tbody, data.error || 'Error al cargar datos');
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                mostrarError(tbody, 'Error de conexión: ' + error.message);
            });
    }

    /**
     * Realiza una búsqueda de clientes
     */
    function realizarBusqueda() {
        const inputBusqueda = document.getElementById('inputBusqueda');
        const busqueda = inputBusqueda.value.trim();

        if (!busqueda) {
            mostrarMensaje('Por favor ingresa un término de búsqueda', 'error');
            return;
        }

        const tbody = document.getElementById('tbodyClientes');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px;">Buscando clientes...</td></tr>';

        console.log('🔍 Búsqueda iniciada:', busqueda);

        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'buscar',
                busqueda: busqueda
            })
        })
            .then(response => {
                console.log('📩 Respuesta búsqueda:', response.status);

                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Resultados de búsqueda:', data);

                if (data.success) {
                    if (data.clientes && data.clientes.length > 0) {
                        cargarClientesEnTabla(data.clientes);
                        mostrarMensaje(data.clientes.length + ' cliente(s) encontrado(s)', 'exito');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px;">No se encontraron clientes</td></tr>';
                        mostrarMensaje('No hay resultados para: ' + busqueda, 'error');
                    }
                } else {
                    mostrarError(tbody, data.error || 'Error en la búsqueda');
                }
            })
            .catch(error => {
                console.error('❌ Error en búsqueda:', error);
                mostrarError(tbody, 'Error de conexión: ' + error.message);
            });
    }

    /**
     * Limpia la búsqueda y recarga todos los clientes
     */
    function limpiarBusqueda() {
        const inputBusqueda = document.getElementById('inputBusqueda');
        inputBusqueda.value = '';
        inputBusqueda.focus();

        console.log('🔄 Búsqueda limpiada, recargando clientes...');
        cargarClientesDesdeDB();
    }

    /**
     * Carga clientes en la tabla
     * @param {Array} clientes - Array de objetos cliente
     */
    function cargarClientesEnTabla(clientes) {
        const tbody = document.getElementById('tbodyClientes');
        tbody.innerHTML = '';

        clientes.forEach((cliente) => {
            const fila = crearFilaCliente(cliente);
            tbody.appendChild(fila);
        });
    }

    /**
     * Crea una fila de cliente para la tabla
     * @param {Object} cliente - Objeto con datos del cliente
     * @returns {HTMLTableRowElement} Fila de tabla
     */
    function crearFilaCliente(cliente) {
        const fila = document.createElement('tr');

        const nombre = cliente.nombre || '';
        const apellidoPaterno = cliente.apellidoPaterno || '';
        const apellidoMaterno = cliente.apellidoMaterno || '';
        const telefono = cliente.telefono || '';

        fila.innerHTML = `
            <td>${nombre}</td>
            <td>${apellidoPaterno}</td>
            <td>${apellidoMaterno}</td>
            <td>${telefono}</td>
            <td class="celda-acciones">
                <div class="botones-accion">
                    <button class="btn-editar" data-id="${cliente.idCliente}" title="Editar">
                        <img src="./src/assets/icon/22.png" alt="Editar">
                    </button>
                    <button class="btn-eliminar" data-id="${cliente.idCliente}" title="Eliminar">
                        <img src="./src/assets/icon/borrar126.png" alt="Eliminar">
                    </button>
                </div>
            </td>
        `;

        fila.dataset.idCliente = cliente.idCliente;
        fila.dataset.idTipoCliente = cliente.idTipoCliente;

        agregarEventosFila(fila, cliente.idCliente);

        return fila;
    }

    /**
     * Agrega eventos de click a los botones de una fila
     * @param {HTMLTableRowElement} fila - Fila de la tabla
     * @param {number} idCliente - ID del cliente
     */
    function agregarEventosFila(fila, idCliente) {
        const btnEditar = fila.querySelector('.btn-editar');
        const btnEliminar = fila.querySelector('.btn-eliminar');

        if (btnEditar) {
            btnEditar.addEventListener('click', () => manejarEdicion(fila));
        }

        if (btnEliminar) {
            btnEliminar.addEventListener('click', () => eliminarCliente(fila, idCliente));
        }
    }

    // ==================== FUNCIONES DE EDICIÓN ====================

    /**
     * Maneja el inicio/cancelación de edición de una fila
     * @param {HTMLTableRowElement} fila - Fila a editar
     */
    function manejarEdicion(fila) {
        // Si hay otra fila en edición, cancelarla
        if (filaEnEdicion && filaEnEdicion !== fila) {
            cancelarEdicion(filaEnEdicion);
        }

        // Alternar modo edición
        if (fila.classList.contains('modo-edicion')) {
            cancelarEdicion(fila);
        } else {
            activarModoEdicion(fila);
            filaEnEdicion = fila;
        }
    }

    /**
     * Activa el modo de edición en una fila
     * @param {HTMLTableRowElement} fila - Fila a editar
     */
    function activarModoEdicion(fila) {
        console.log('✏️ Activando modo edición para fila:', fila.dataset.idCliente);

        fila.classList.add('modo-edicion');
        const celdas = fila.querySelectorAll('td');

        // Guardar valores originales
        const valoresOriginales = {
            nombre: celdas[0].textContent,
            apellidoPaterno: celdas[1].textContent,
            apellidoMaterno: celdas[2].textContent,
            telefono: celdas[3].textContent
        };

        // Almacenar valores originales en dataset
        Object.entries(valoresOriginales).forEach(([key, value]) => {
            fila.dataset['valorOriginal' + key.charAt(0).toUpperCase() + key.slice(1)] = value;
        });

        // Convertir celdas a inputs
        celdas[0].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.nombre + '" placeholder="Nombre">';
        celdas[1].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.apellidoPaterno + '" placeholder="Apellido Paterno">';
        celdas[2].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.apellidoMaterno + '" placeholder="Apellido Materno">';
        celdas[3].innerHTML = '<input type="tel" class="input-editar" value="' + valoresOriginales.telefono + '" placeholder="Telefono" maxlength="10">';

        // Cambiar botones
        celdas[4].innerHTML = `
            <div class="botones-accion">
                <button class="btn-guardar" title="Guardar">
                    <img src="./src/assets/icon/guardar.png" alt="Guardar">
                </button>
                <button class="btn-cancelar" title="Cancelar">
                    <img src="./src/assets/icon/cancelar.png" alt="Cancelar">
                </button>
            </div>
        `;

        // Agregar eventos a nuevos botones
        celdas[4].querySelector('.btn-guardar').addEventListener('click', () => guardarCambios(fila));
        celdas[4].querySelector('.btn-cancelar').addEventListener('click', () => cancelarEdicion(fila));

        // Dar focus al primer input
        celdas[0].querySelector('input').focus();
    }

    /**
     * Guarda los cambios de un cliente editado
     * @param {HTMLTableRowElement} fila - Fila editada
     */
    function guardarCambios(fila) {
        console.log('💾 Guardando cambios...');

        const celdas = fila.querySelectorAll('td');

        // Obtener nuevos datos
        const datosNuevos = {
            nombre: celdas[0].querySelector('input').value.trim(),
            apellidoPaterno: celdas[1].querySelector('input').value.trim(),
            apellidoMaterno: celdas[2].querySelector('input').value.trim(),
            telefono: celdas[3].querySelector('input').value.trim()
        };

        // Validar datos
        const validacion = validarDatosCliente(datosNuevos);
        if (!validacion.valido) {
            mostrarMensaje(validacion.mensaje, 'error');
            return;
        }

        // Deshabilitar botón
        const btnGuardar = celdas[4].querySelector('.btn-guardar');
        btnGuardar.disabled = true;
        btnGuardar.style.opacity = '0.6';

        // Preparar datos para enviar
        const datosActualizados = {
            accion: 'actualizar',
            idCliente: fila.dataset.idCliente,
            ...datosNuevos
        };

        console.log('📤 Enviando datos:', datosActualizados);

        // Enviar al servidor
        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosActualizados)
        })
            .then(response => {
                console.log('📩 Respuesta actualización:', response.status);

                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Respuesta del servidor:', data);

                if (data.success) {
                    // Actualizar tabla
                    celdas[0].textContent = datosNuevos.nombre;
                    celdas[1].textContent = datosNuevos.apellidoPaterno;
                    celdas[2].textContent = datosNuevos.apellidoMaterno;
                    celdas[3].textContent = datosNuevos.telefono;

                    // Restaurar botones
                    restaurarBotonesAccion(celdas[4], fila.dataset.idCliente);
                    agregarEventosFila(fila, fila.dataset.idCliente);

                    // Remover modo edición
                    fila.classList.remove('modo-edicion');
                    filaEnEdicion = null;

                    mostrarMensaje('✅ Cliente actualizado exitosamente', 'exito');
                } else {
                    mostrarMensaje(data.error || data.message || 'Error al guardar', 'error');
                    btnGuardar.disabled = false;
                    btnGuardar.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                mostrarMensaje('Error de conexión: ' + error.message, 'error');
                btnGuardar.disabled = false;
                btnGuardar.style.opacity = '1';
            });
    }

    /**
     * Valida los datos de un cliente
     * @param {Object} datos - Datos a validar
     * @returns {Object} Objeto con propiedades: valido (boolean) y mensaje (string)
     */
    function validarDatosCliente(datos) {
        if (!datos.nombre || datos.nombre === '') {
            return { valido: false, mensaje: 'El nombre no puede estar vacío' };
        }
        if (!datos.apellidoPaterno || datos.apellidoPaterno === '') {
            return { valido: false, mensaje: 'El apellido paterno no puede estar vacío' };
        }
        if (!datos.apellidoMaterno || datos.apellidoMaterno === '') {
            return { valido: false, mensaje: 'El apellido materno no puede estar vacío' };
        }
        if (!datos.telefono || datos.telefono === '') {
            return { valido: false, mensaje: 'El teléfono no puede estar vacío' };
        }
        if (!/^\d{10}$/.test(datos.telefono)) {
            return { valido: false, mensaje: 'El teléfono debe tener exactamente 10 dígitos' };
        }
        return { valido: true };
    }

    /**
     * Cancela la edición de una fila
     * @param {HTMLTableRowElement} fila - Fila a cancelar
     */
    function cancelarEdicion(fila) {
        console.log('❌ Cancelando edición');

        const celdas = fila.querySelectorAll('td');
        const idCliente = fila.dataset.idCliente;

        // Restaurar valores originales
        celdas[0].textContent = fila.dataset.valorOriginalNombre;
        celdas[1].textContent = fila.dataset.valorOriginalApellidoPaterno;
        celdas[2].textContent = fila.dataset.valorOriginalApellidoMaterno;
        celdas[3].textContent = fila.dataset.valorOriginalTelefono;

        // Restaurar botones
        restaurarBotonesAccion(celdas[4], idCliente);
        agregarEventosFila(fila, idCliente);

        // Remover modo edición
        fila.classList.remove('modo-edicion');
        filaEnEdicion = null;
    }

    /**
     * Restaura los botones de acción de una celda
     * @param {HTMLTableCellElement} celda - Celda con botones
     * @param {number} idCliente - ID del cliente
     */
    function restaurarBotonesAccion(celda, idCliente) {
        celda.innerHTML = `
            <div class="botones-accion">
                <button class="btn-editar" data-id="${idCliente}" title="Editar">
                    <img src="./src/assets/icon/22.png" alt="Editar">
                </button>
                <button class="btn-eliminar" data-id="${idCliente}" title="Eliminar">
                    <img src="./src/assets/icon/borrar126.png" alt="Eliminar">
                </button>
            </div>
        `;
    }

    // ==================== FUNCIONES DE ELIMINACIÓN ====================

    /**
     * Elimina un cliente después de confirmación
     * @param {HTMLTableRowElement} fila - Fila del cliente a eliminar
     * @param {number} idCliente - ID del cliente
     */
    function eliminarCliente(fila, idCliente) {
        console.log('🗑️ Iniciando eliminación para cliente ID:', idCliente);

        const celdas = fila.querySelectorAll('td');
        const nombre = celdas[0].textContent;
        const apellido = celdas[1].textContent;
        const nombreCompleto = nombre + ' ' + apellido;

        // Pedir confirmación
        if (!confirm('¿Estás seguro de eliminar al cliente?\n\n' + nombreCompleto + '\n\nEsta acción no se puede deshacer.')) {
            return;
        }

        // Deshabilitar botón
        const btnEliminar = fila.querySelector('.btn-eliminar');
        btnEliminar.disabled = true;
        btnEliminar.style.opacity = '0.6';

        console.log('Enviando solicitud de eliminación...');

        // Enviar solicitud
        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'eliminar',
                idCliente: idCliente
            })
        })
            .then(response => {
                console.log('📩 Respuesta eliminación:', response.status);

                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Respuesta del servidor:', data);

                if (data.success) {
                    // Animar eliminación
                    fila.style.transition = 'all 0.3s ease';
                    fila.style.opacity = '0';
                    fila.style.transform = 'translateX(-100%)';

                    // Remover fila
                    setTimeout(() => {
                        fila.remove();
                        mostrarMensaje('✅ Cliente ' + nombreCompleto + ' eliminado', 'exito');
                    }, 300);
                } else {
                    mostrarMensaje(data.error || data.message || 'Error al eliminar', 'error');
                    btnEliminar.disabled = false;
                    btnEliminar.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                mostrarMensaje('Error de conexión: ' + error.message, 'error');
                btnEliminar.disabled = false;
                btnEliminar.style.opacity = '1';
            });
    }

    // ==================== FUNCIONES DE UI ====================

    /**
     * Muestra un mensaje flotante
     * @param {string} texto - Texto del mensaje
     * @param {string} tipo - Tipo: 'exito' o 'error'
     */
    function mostrarMensaje(texto, tipo) {
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje mensaje-' + tipo;
        mensaje.textContent = texto;

        const backgroundColor = tipo === 'exito' ? '#4CAF50' : '#f44336';

        mensaje.style.cssText = `
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 15px 25px; 
            background-color: ${backgroundColor}; 
            color: white; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            z-index: 10000; 
            animation: slideIn 0.3s ease-out; 
            max-width: 400px; 
            font-weight: 500;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        `;

        document.body.appendChild(mensaje);

        // Auto remover después de 4 segundos
        setTimeout(() => {
            mensaje.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => mensaje.remove(), 300);
        }, 4000);
    }

    /**
     * Muestra un mensaje de error en la tabla
     * @param {HTMLTableSectionElement} tbody - Body de la tabla
     * @param {string} mensaje - Mensaje a mostrar
     */
    function mostrarError(tbody, mensaje) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: grey; padding: 30px;">
                    ${mensaje}
                    <br><br>
                   
                </td>
            </tr>
        `;
    }

    // ==================== ESTILOS INYECTADOS ====================

    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { 
                transform: translateX(100%); 
                opacity: 0; 
            }
            to { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }

        @keyframes slideOut {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(100%); 
                opacity: 0; 
            }
        }

        .modo-edicion {
            background-color: #fff3cd !important;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.3);
        }

        .input-editar {
            width: 100%;
            padding: 8px;
            border: 2px solid #667eea;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .input-editar:focus {
            outline: none;
            border-color: #5568d3;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .mensaje {
            font-size: 14px;
        }
    `;
    document.head.appendChild(style);

    console.log('✅ Script de clientes cargado exitosamente');
});