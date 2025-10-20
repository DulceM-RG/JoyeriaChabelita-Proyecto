// ============================================
// IMPORTACIONES
// ============================================
import apiEndPonints from './apiEndPoints.js';
import Buttons from './buttonsCredenciales.js';
import DataManager from './dataManagerCredenciales.js';

// ============================================
// UTILIDADES
// ============================================
const dataManager = new DataManager('credenciales');
let datosCeldas = [];

function createCell(row, text) {
    const cell = document.createElement('td');
    cell.textContent = text;
    row.appendChild(cell);
}

// ============================================
// FUNCIÓN PRINCIPAL: DIBUJAR TABLA
// ============================================
const agregarFilaTabla = (dataDB, tbody) => {
    tbody.textContent = ""; // Limpiar tabla

    if (!dataDB || dataDB.length === 0) {
        const emptyRow = document.createElement('tr');
        const emptyCell = document.createElement('td');
        emptyCell.colSpan = 8;
        emptyCell.textContent = 'No hay datos disponibles';
        emptyCell.style.textAlign = 'center';
        emptyRow.appendChild(emptyCell);
        tbody.appendChild(emptyRow);
        return;
    }

    for (const data of dataDB) {
        const newRow = document.createElement('tr');
        newRow.dataset.idEmpleado = data.idEmpleado;

        // Convertir 'Activo'/'Baja' a 'Sí'/'No' con badges
        const estadoTexto = data.activo;
        const badgeClass = (data.activo === 'Activo') ? 'badge-activo' : 'badge-inactivo';

        // Crear celdas de datos
        createCell(newRow, data.nombreCompleto || 'N/A');
        createCell(newRow, data.usuario);
        createCell(newRow, '••••••••'); // Contraseña oculta
        createCell(newRow, data.fechaCreacion);
        createCell(newRow, data.ultimoCambio);

        // Celda de Estado con Badge
        const estadoCell = document.createElement('td');
        estadoCell.innerHTML = `<span class="badge ${badgeClass}">${estadoTexto}</span>`;
        newRow.appendChild(estadoCell);

        createCell(newRow, data.intentosFallidos);

        // Celda de ACCIONES (solo botón Editar)
        const actionsCell = document.createElement('td');
        const editButton = document.createElement('img');
        Buttons.crearBotonesAcciones(
            actionsCell,
            editButton,
            Buttons.botones.btnEdit.id,
            Buttons.botones.btnEdit.ruta,
            Buttons.botones.btnEdit.title
        );

        newRow.appendChild(actionsCell);
        tbody.appendChild(newRow);
    }
}

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', async function () {
    const tbody = document.getElementById('tbodyCredenciales');

    console.log('🔄 Iniciando carga de credenciales...');

    // Cargar datos al iniciar
    try {
        const resultCredenciales = await apiEndPonints.selectAllCredenciales();

        console.log('📊 Datos recibidos:', resultCredenciales);

        if (resultCredenciales && Array.isArray(resultCredenciales) && resultCredenciales.length > 0) {
            dataManager.saveAllData(resultCredenciales);
            agregarFilaTabla(resultCredenciales, tbody);
            console.log('✅ Credenciales cargadas:', resultCredenciales.length, 'registros');
        } else {
            console.warn('⚠️ No se recibieron datos del servidor');
            // Intentar cargar desde localStorage
            const credencialesLocal = dataManager.readData();
            if (credencialesLocal.length > 0) {
                agregarFilaTabla(credencialesLocal, tbody);
                console.log('📦 Cargando desde localStorage');
            } else {
                agregarFilaTabla([], tbody);
                console.log('❌ No hay datos disponibles');
            }
        }
    } catch (error) {
        console.error('❌ Error al cargar credenciales:', error);
        agregarFilaTabla([], tbody);
    }

    // ============================================
    // EVENT LISTENER - ACCIONES DE LA TABLA
    // ============================================
    tbody.addEventListener('click', async function (event) {

        // ========== ACCIÓN EDITAR ==========
        if (event.target.id === Buttons.botones.btnEdit.id) {
            const rowEdit = event.target.closest('tr');
            const cells = rowEdit.querySelectorAll('td');
            datosCeldas = [];

            cells.forEach((cell, index) => {
                if (index >= cells.length - 1) return;

                const valorActual = cell.textContent.trim();
                datosCeldas.push(valorActual);
                cell.textContent = '';

                let input;

                // Columna 2: Contraseña
                if (index === 2) {
                    input = document.createElement('input');
                    input.type = 'password';
                    input.value = '';
                    input.placeholder = 'Nueva Contraseña';
                    input.className = 'input-editar';

                } else if (index === 5) {
                    const badgeTexto = cell.querySelector('.badge')?.textContent.trim() || valorActual;
                    datosCeldas[index] = badgeTexto;

                    input = document.createElement('select');
                    input.className = 'select-editar';
                    const isActivo = badgeTexto === 'Activo';
                    input.innerHTML = `
                        <option value="Activo" ${isActivo ? 'selected' : ''}>Activo</option>
                        <option value="Baja" ${!isActivo ? 'selected' : ''}>Baja</option>
                    `;

                } else if (index === 6) {
                    input = document.createElement('select');
                    input.className = 'select-editar';
                    input.innerHTML = `
                        <option value="0" ${valorActual === '0' ? 'selected' : ''}>0</option>
                        <option value="1" ${valorActual === '1' ? 'selected' : ''}>1</option>
                        <option value="2" ${valorActual === '2' ? 'selected' : ''}>2</option>
                        <option value="3" ${valorActual === '3' ? 'selected' : ''}>3</option>
                    `;

                } else if (index === 0 || index === 1 || index === 3 || index === 4) {
                    cell.textContent = valorActual;
                    return;
                }

                if (input) {
                    cell.appendChild(input);
                }
            });

            Buttons.changeButtonEvent(
                event,
                Buttons.botones.btnSave.id,
                Buttons.botones.btnSave.ruta,
                Buttons.botones.btnSave.title
            );

            const btnCancelar = document.createElement('img');
            Buttons.crearBotonesAcciones(
                cells[cells.length - 1],
                btnCancelar,
                Buttons.botones.btnCancel.id,
                Buttons.botones.btnCancel.ruta,
                Buttons.botones.btnCancel.title
            );

            console.log('🔧 Modo edición activado');
            return;
        }

        // ========== ACCIÓN GUARDAR ==========
        if (event.target.id === Buttons.botones.btnSave.id) {
            const rowSave = event.target.closest('tr');
            const idEmpleado = rowSave.dataset.idEmpleado;

            const inputContrasena = rowSave.querySelector('td:nth-child(3) input');
            const selectActivo = rowSave.querySelector('td:nth-child(6) select');
            const selectIntentos = rowSave.querySelector('td:nth-child(7) select');

            if (!inputContrasena || inputContrasena.value.trim() === '') {
                alert('⚠️ Debe ingresar una nueva contraseña.');
                return;
            }

            const objCredencialActualizada = {
                idEmpleado: parseInt(idEmpleado),
                contrasena: inputContrasena.value,
                activo: selectActivo.value,
                intentosFallidos: parseInt(selectIntentos.value, 10)
            };

            console.log('💾 Guardando:', objCredencialActualizada);

            try {
                const response = await apiEndPonints.updateCredencial(idEmpleado, objCredencialActualizada);

                if (response.errorDB || response.errorServer) {
                    alert("❌ Error: " + (response.errorDB || response.errorServer));
                    return;
                }

                dataManager.updateData(parseInt(idEmpleado), objCredencialActualizada);

                const credenciales = await apiEndPonints.selectAllCredenciales();
                agregarFilaTabla(credenciales, tbody);

                alert("✅ Credencial actualizada correctamente");
                console.log('✅ Actualización exitosa');

            } catch (error) {
                console.error('❌ Error:', error);
                alert("❌ Error de conexión con el servidor");
            }

            return;
        }

        // ========== ACCIÓN CANCELAR ==========
        if (event.target.id === Buttons.botones.btnCancel.id) {
            const rowCancel = event.target.closest('tr');
            const cells = rowCancel.querySelectorAll('td');

            cells.forEach((cell, index) => {
                if (index < cells.length - 1) {
                    if (index === 5) {
                        const estadoOriginal = datosCeldas[index];
                        const badgeClass = (estadoOriginal === 'Activo') ? 'badge-activo' : 'badge-inactivo';
                        cell.innerHTML = `<span class="badge ${badgeClass}">${estadoOriginal}</span>`;
                    } else {
                        cell.textContent = datosCeldas[index];
                    }
                }
            });

            const btnCancelar = cells[cells.length - 1].querySelector('#' + Buttons.botones.btnCancel.id);
            if (btnCancelar) btnCancelar.remove();

            const btnGuardar = cells[cells.length - 1].querySelector('#' + Buttons.botones.btnSave.id);
            Buttons.changeButtonNotEvent(
                btnGuardar,
                Buttons.botones.btnEdit.id,
                Buttons.botones.btnEdit.ruta,
                Buttons.botones.btnEdit.title
            );

            console.log('❌ Edición cancelada');
            return;
        }
    });
});