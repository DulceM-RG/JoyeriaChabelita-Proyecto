// ===== CLIENTES MAYORISTAS - INTERACTIVIDAD =====
// Archivo: dataFormClientesMayoristas.js

document.addEventListener('DOMContentLoaded', function () {

    // Variable para rastrear la fila que esta editando
    let filaEnEdicion = null;

    // Configuracion de la API
    const API_URL = '/JoyeriaChabelita-Proyecto/src/database/clientesMayoristas.php';
    
    console.log('Iniciando carga de clientes...');
    console.log('API URL:', API_URL);

    // Cargar los datos al iniciar
    cargarClientesDesdeDB();

    // ==================== FUNCIONES DE CARGA ====================

    function cargarClientesDesdeDB() {
        const tbody = document.getElementById('tbodyClientes');
        
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px;">Cargando clientes...</td></tr>';

        // Realizar peticion AJAX
        fetch(`${API_URL}?action=obtenerClientes&t=${Date.now()}`)
            .then(response => {
                console.log('Respuesta recibida:', response.status);
                console.log('URL:', response.url);
                
                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    if (data.clientes && data.clientes.length > 0) {
                        cargarClientesEnTabla(data.clientes);
                        mostrarMensaje(data.clientes.length + ' clientes cargados', 'exito');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No hay clientes</td></tr>';
                    }
                } else {
                    mostrarError(tbody, data.message || 'Error al cargar datos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError(tbody, 'Error de conexion: ' + error.message);
            });
    }

    function mostrarError(tbody, mensaje) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red; padding: 30px;">' + mensaje + '<br><br><button onclick="location.reload()">Reintentar</button></td></tr>';
    }

    function cargarClientesEnTabla(clientes) {
        const tbody = document.getElementById('tbodyClientes');
        tbody.innerHTML = '';

        clientes.forEach((cliente) => {
            const fila = crearFilaCliente(cliente);
            tbody.appendChild(fila);
        });
    }

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
                        <img src="./src/assets/icon/eliminar.png" alt="Eliminar">
                    </button>
                </div>
            </td>
        `;
        
        fila.dataset.idCliente = cliente.idCliente;
        fila.dataset.idTipoCliente = cliente.idTipoCliente;
        
        agregarEventosFila(fila, cliente.idCliente);
        
        return fila;
    }

    function agregarEventosFila(fila, idCliente) {
        const btnEditar = fila.querySelector('.btn-editar');
        const btnEliminar = fila.querySelector('.btn-eliminar');

        btnEditar.addEventListener('click', () => manejarEdicion(fila));
        btnEliminar.addEventListener('click', () => eliminarCliente(fila, idCliente));
    }

    // ==================== FUNCIONES DE EDICION ====================

    function manejarEdicion(fila) {
        if (filaEnEdicion && filaEnEdicion !== fila) {
            cancelarEdicion(filaEnEdicion);
        }

        if (fila.classList.contains('modo-edicion')) {
            cancelarEdicion(fila);
        } else {
            activarModoEdicion(fila);
            filaEnEdicion = fila;
        }
    }

    function activarModoEdicion(fila) {
        fila.classList.add('modo-edicion');
        const celdas = fila.querySelectorAll('td');

        const valoresOriginales = {
            nombre: celdas[0].textContent,
            apellidoPaterno: celdas[1].textContent,
            apellidoMaterno: celdas[2].textContent,
            telefono: celdas[3].textContent
        };

        Object.entries(valoresOriginales).forEach(([key, value]) => {
            fila.dataset['valorOriginal' + key.charAt(0).toUpperCase() + key.slice(1)] = value;
        });

        celdas[0].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.nombre + '" placeholder="Nombre">';
        celdas[1].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.apellidoPaterno + '" placeholder="Apellido Paterno">';
        celdas[2].innerHTML = '<input type="text" class="input-editar" value="' + valoresOriginales.apellidoMaterno + '" placeholder="Apellido Materno">';
        celdas[3].innerHTML = '<input type="tel" class="input-editar" value="' + valoresOriginales.telefono + '" placeholder="Telefono" maxlength="10">';

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

        celdas[4].querySelector('.btn-guardar').addEventListener('click', () => guardarCambios(fila));
        celdas[4].querySelector('.btn-cancelar').addEventListener('click', () => cancelarEdicion(fila));

        celdas[0].querySelector('input').focus();
    }

    function guardarCambios(fila) {
        const celdas = fila.querySelectorAll('td');

        const datosNuevos = {
            nombre: celdas[0].querySelector('input').value.trim(),
            apellidoPaterno: celdas[1].querySelector('input').value.trim(),
            apellidoMaterno: celdas[2].querySelector('input').value.trim(),
            telefono: celdas[3].querySelector('input').value.trim()
        };

        const validacion = validarDatosCliente(datosNuevos);
        if (!validacion.valido) {
            mostrarMensaje(validacion.mensaje, 'error');
            return;
        }

        const btnGuardar = celdas[4].querySelector('.btn-guardar');
        btnGuardar.disabled = true;
        btnGuardar.style.opacity = '0.6';

        const datosActualizados = {
            action: 'actualizarCliente',
            idCliente: fila.dataset.idCliente,
            ...datosNuevos
        };

        console.log('Enviando datos:', datosActualizados);

        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosActualizados)
        })
        .then(response => {
            console.log('Respuesta:', response.status);
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                celdas[0].textContent = datosNuevos.nombre;
                celdas[1].textContent = datosNuevos.apellidoPaterno;
                celdas[2].textContent = datosNuevos.apellidoMaterno;
                celdas[3].textContent = datosNuevos.telefono;

                restaurarBotonesAccion(celdas[4], fila.dataset.idCliente);
                agregarEventosFila(fila, fila.dataset.idCliente);

                fila.classList.remove('modo-edicion');
                filaEnEdicion = null;

                mostrarMensaje('Cliente actualizado exitosamente', 'exito');
            } else {
                mostrarMensaje(data.message || 'Error al guardar', 'error');
                btnGuardar.disabled = false;
                btnGuardar.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexion: ' + error.message, 'error');
            btnGuardar.disabled = false;
            btnGuardar.style.opacity = '1';
        });
    }

    function validarDatosCliente(datos) {
        if (!datos.nombre) {
            return { valido: false, mensaje: 'El nombre no puede estar vacio' };
        }
        if (!datos.apellidoPaterno) {
            return { valido: false, mensaje: 'El apellido paterno no puede estar vacio' };
        }
        if (!datos.telefono) {
            return { valido: false, mensaje: 'El telefono no puede estar vacio' };
        }
        if (!/^\d{10}$/.test(datos.telefono)) {
            return { valido: false, mensaje: 'El telefono debe tener 10 digitos' };
        }
        return { valido: true };
    }

    function cancelarEdicion(fila) {
        const celdas = fila.querySelectorAll('td');
        const idCliente = fila.dataset.idCliente;

        celdas[0].textContent = fila.dataset.valorOriginalNombre;
        celdas[1].textContent = fila.dataset.valorOriginalApellidoPaterno;
        celdas[2].textContent = fila.dataset.valorOriginalApellidoMaterno;
        celdas[3].textContent = fila.dataset.valorOriginalTelefono;

        restaurarBotonesAccion(celdas[4], idCliente);
        agregarEventosFila(fila, idCliente);

        fila.classList.remove('modo-edicion');
        filaEnEdicion = null;
    }

    function restaurarBotonesAccion(celda, idCliente) {
        celda.innerHTML = `
            <div class="botones-accion">
                <button class="btn-editar" data-id="${idCliente}" title="Editar">
                    <img src="./src/assets/icon/22.png" alt="Editar">
                </button>
                <button class="btn-eliminar" data-id="${idCliente}" title="Eliminar">
                    <img src="./src/assets/icon/eliminar.png" alt="Eliminar">
                </button>
            </div>
        `;
    }

    // ==================== FUNCIONES DE ELIMINACION ====================

    function eliminarCliente(fila, idCliente) {
        const celdas = fila.querySelectorAll('td');
        const nombre = celdas[0].textContent;
        const apellido = celdas[1].textContent;
        const nombreCompleto = nombre + ' ' + apellido;

        if (!confirm('Estas seguro de eliminar al cliente?\n\n' + nombreCompleto + '\n\nEsta accion no se puede deshacer.')) {
            return;
        }

        const btnEliminar = fila.querySelector('.btn-eliminar');
        btnEliminar.disabled = true;
        btnEliminar.style.opacity = '0.6';

        console.log('Eliminando cliente:', idCliente);

        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'eliminarCliente',
                idCliente: idCliente
            })
        })
        .then(response => {
            console.log('Respuesta:', response.status);
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                fila.style.transition = 'all 0.3s ease';
                fila.style.opacity = '0';
                fila.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    fila.remove();
                    mostrarMensaje('Cliente ' + nombreCompleto + ' eliminado', 'exito');
                }, 300);
            } else {
                mostrarMensaje(data.message || 'Error al eliminar', 'error');
                btnEliminar.disabled = false;
                btnEliminar.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexion: ' + error.message, 'error');
            btnEliminar.disabled = false;
            btnEliminar.style.opacity = '1';
        });
    }

    // ==================== FUNCIONES DE UI ====================

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
});

// ==================== ESTILOS ====================

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
    }

    .input-editar:focus {
        outline: none;
        border-color: #5568d3;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
`;
document.head.appendChild(style);