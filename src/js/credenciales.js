
import apiEndPonints from './apiEndPoints';
// ===== CREDENCIALES - INTERACTIVIDAD =====

document.addEventListener('DOMContentLoaded', function () {

    // Obtener todos los botones de editar
    const botonesEditar = document.querySelectorAll('.btn-editar');

    // Variable para rastrear la fila que se está editando actualmente
    let filaEnEdicion = null;

    // Agregar evento click a cada botón de editar
    botonesEditar.forEach(boton => {
        boton.addEventListener('click', function () {
            const fila = this.closest('tr');

            // Si ya hay una fila en edición, restaurarla primero
            if (filaEnEdicion && filaEnEdicion !== fila) {
                cancelarEdicion(filaEnEdicion);
            }

            // Alternar entre modo edición y modo normal
            if (fila.classList.contains('modo-edicion')) {
                cancelarEdicion(fila);
            } else {
                activarModoEdicion(fila);
                filaEnEdicion = fila;
            }
        });
    });

    // Función para activar el modo edición
    function activarModoEdicion(fila) {
        // Marcar la fila como en edición
        fila.classList.add('modo-edicion');

        // Obtener todas las celdas de la fila
        const celdas = fila.querySelectorAll('td');

        // Índices de las columnas editables: 2: Contraseña, 5: Activo, 6: Intentos fallidos

        // Guardar valores originales
        fila.dataset.valorOriginalPassword = celdas[2].textContent;
        // fila.dataset.valorOriginalFecha = celdas[4].textContent; // ELIMINADO
        fila.dataset.valorOriginalActivo = celdas[5].querySelector('.badge').textContent;
        fila.dataset.valorOriginalIntentos = celdas[6].textContent;

        // CONTRASEÑA (índice 2) - Input password
        const passwordActual = celdas[2].textContent;
        celdas[2].innerHTML = `
            <input type="password" 
                   class="input-editar" 
                   value="${passwordActual}" 
                   placeholder="Nueva contraseña">
        `;

        // FECHA ÚLTIMO CAMBIO (índice 4) - NO SE TOCA

        // ACTIVO (índice 5) - Select Activo/Baja
        const activoActual = celdas[5].querySelector('.badge').textContent;
        celdas[5].innerHTML = `
            <select class="select-editar">
                <option value="Activo" ${activoActual === 'Activo' ? 'selected' : ''}>Activo</option>
                <option value="Baja" ${activoActual === 'Baja' ? 'selected' : ''}>Baja</option>
            </select>
        `;

        // INTENTOS FALLIDOS (índice 6) - Select 0,1,2,3
        const intentosActual = celdas[6].textContent;
        celdas[6].innerHTML = `
            <select class="select-editar">
                <option value="0" ${intentosActual === '0' ? 'selected' : ''}>0</option>
                <option value="1" ${intentosActual === '1' ? 'selected' : ''}>1</option>
                <option value="2" ${intentosActual === '2' ? 'selected' : ''}>2</option>
                <option value="3" ${intentosActual === '3' ? 'selected' : ''}>3</option>
            </select>
        `;

        // ACCIONES (índice 7) - Cambiar botones a Guardar y Cancelar
        celdas[7].innerHTML = `
            <div class="botones-accion">
                <button class="btn-guardar" title="Guardar cambios">
                    <img src="./images/save-icon.png" alt="Guardar">
                </button>
                <button class="btn-cancelar" title="Cancelar">
                    <img src="./images/cancel-icon.png" alt="Cancelar">
                </button>
            </div>
        `;

        // Agregar eventos a los nuevos botones
        const btnGuardar = celdas[7].querySelector('.btn-guardar');
        const btnCancelar = celdas[7].querySelector('.btn-cancelar');

        btnGuardar.addEventListener('click', function () {
            guardarCambios(fila);
        });

        btnCancelar.addEventListener('click', function () {
            cancelarEdicion(fila);
        });
    }

    // Función para guardar los cambios
    function guardarCambios(fila) {
        const celdas = fila.querySelectorAll('td');

        // Obtener nuevos valores
        const nuevaPassword = celdas[2].querySelector('input').value;
        // const nuevaFecha = celdas[4].querySelector('input').value; // ELIMINADO
        const nuevoActivo = celdas[5].querySelector('select').value;
        const nuevosIntentos = celdas[6].querySelector('select').value;

        // Validar que la contraseña no esté vacía
        if (nuevaPassword.trim() === '') {
            alert('La contraseña no puede estar vacía');
            return;
        }

        // VALIDACIÓN DE FECHA ELIMINADA

        // Actualizar las celdas con los nuevos valores
        celdas[2].textContent = '••••••••'; // Ocultar contraseña
        // celdas[4].textContent = '...'; // NO SE TOCA

        // Actualizar badge de Activo
        const badgeClass = nuevoActivo === 'Activo' ? 'badge-activo' : 'badge-inactivo';
        celdas[5].innerHTML = `<span class="badge ${badgeClass}">${nuevoActivo}</span>`; // Muestra "Activo" o "Baja"

        celdas[6].textContent = nuevosIntentos;

        // Restaurar botón de editar
        celdas[7].innerHTML = `
            <button class="btn-editar">
                <img src="./src/assets/icon/iconEditar.png" alt="Editar">
            </button>
        `;

        // Volver a agregar el evento click al nuevo botón
        const nuevoBotonEditar = celdas[7].querySelector('.btn-editar');
        nuevoBotonEditar.addEventListener('click', function () {
            const fila = this.closest('tr');
            if (filaEnEdicion && filaEnEdicion !== fila) {
                cancelarEdicion(filaEnEdicion);
            }
            if (fila.classList.contains('modo-edicion')) {
                cancelarEdicion(fila);
            } else {
                activarModoEdicion(fila);
                filaEnEdicion = fila;
            }
        });

        // Quitar clase de edición
        fila.classList.remove('modo-edicion');
        filaEnEdicion = null;

        // Mostrar mensaje de éxito
        mostrarMensaje('Cambios guardados exitosamente', 'exito');

        // Aquí puedes agregar código para enviar los datos al servidor (PHP)
        console.log('Datos a guardar:', {
            empleado: celdas[0].textContent,
            usuario: celdas[1].textContent,
            password: nuevaPassword,
            fechaCreacion: celdas[3].textContent,
            // fechaUltimoCambio: ELIMINADO
            activo: nuevoActivo,
            intentosFallidos: nuevosIntentos
        });
    }

    // Función para cancelar la edición
    function cancelarEdicion(fila) {
        const celdas = fila.querySelectorAll('td');

        // Restaurar valores originales
        celdas[2].textContent = fila.dataset.valorOriginalPassword;
        // celdas[4].textContent = fila.dataset.valorOriginalFecha; // ELIMINADO

        const activoOriginal = fila.dataset.valorOriginalActivo;

        //// Actualizar badge de Activo
        // CORREGIDO: Usar activoOriginal en lugar de nuevoActivo
        const badgeClass = activoOriginal === 'Activo' ? 'badge-activo' : 'badge-inactivo';
        celdas[5].innerHTML = `<span class="badge ${badgeClass}">${activoOriginal}</span>`;


        celdas[6].textContent = fila.dataset.valorOriginalIntentos;

        // Restaurar botón de editar
        celdas[7].innerHTML = `
            <button class="btn-editar">
                <img src="./src/assets/icon/iconEditar.png" alt="Editar">
            </button>
        `;

        // Volver a agregar el evento click al nuevo botón
        const nuevoBotonEditar = celdas[7].querySelector('.btn-editar');
        nuevoBotonEditar.addEventListener('click', function () {
            const fila = this.closest('tr');
            if (filaEnEdicion && filaEnEdicion !== fila) {
                cancelarEdicion(filaEnEdicion);
            }
            if (fila.classList.contains('modo-edicion')) {
                cancelarEdicion(fila);
            } else {
                activarModoEdicion(fila);
                filaEnEdicion = fila;
            }
        });

        // Quitar clase de edición
        fila.classList.remove('modo-edicion');
        filaEnEdicion = null;
    }

    // Función para mostrar mensajes (Mantener sin cambios)
    function mostrarMensaje(texto, tipo) {
        // ... (Tu código)
    }
});