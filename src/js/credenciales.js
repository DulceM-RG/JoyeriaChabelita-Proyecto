// ============================================
// IMPORTACIONES SOLO MOSTRAR CREDENCIALES
// ============================================
import apiEndPoints from "./apiEndPoints.js";
import Buttons from "./buttonsCredenciales.js";
import DataManager from "./dataManagerCredenciales.js";

// ============================================
// UTILIDADES
// ============================================
const dataManager = new DataManager("credenciales");
let datosCeldas = [];

function createCell(row, text) {
  const cell = document.createElement("td");
  cell.textContent = text;
  row.appendChild(cell);
}

// ============================================
// FUNCIÓN PRINCIPAL: DIBUJAR TABLA
// ============================================
const agregarFilaTabla = (dataDB, tbody) => {
  tbody.textContent = ""; // Limpiar tabla

  if (!dataDB || dataDB.length === 0) {
    const emptyRow = document.createElement("tr");
    const emptyCell = document.createElement("td");
    emptyCell.colSpan = 8;
    emptyCell.textContent = "No hay datos disponibles";
    emptyCell.style.textAlign = "center";
    emptyRow.appendChild(emptyCell);
    tbody.appendChild(emptyRow);
    return;
  }

  for (const data of dataDB) {
    const newRow = document.createElement("tr");
    newRow.dataset.idEmpleado = data.idEmpleado;

    // Convertir 'Activo'/'Baja' a badges
    const estadoTexto = data.activo;
    const badgeClass =
      data.activo === "Activo" ? "badge-activo" : "badge-inactivo";

    // Crear celdas de datos
    createCell(newRow, data.nombreCompleto || "N/A");
    createCell(newRow, data.usuario);
    createCell(newRow, "••••"); // Contraseña oculta
    createCell(newRow, data.fechaCreacion);
    createCell(newRow, data.ultimoCambio);

    // Celda de Estado con Badge
    const estadoCell = document.createElement("td");
    estadoCell.innerHTML = `<span class="badge ${badgeClass}">${estadoTexto}</span>`;
    newRow.appendChild(estadoCell);

    createCell(newRow, data.intentosFallidos);

    // Celda de ACCIONES (solo botón Editar)
    const actionsCell = document.createElement("td");
    const editButton = document.createElement("img");
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
};

// ============================================
// FUNCIÓN DE BÚSQUEDA
// ============================================
function buscarCredenciales(terminoBusqueda, tbody) {
  const credenciales = dataManager.readData();

  if (!terminoBusqueda || terminoBusqueda.trim() === "") {
    agregarFilaTabla(credenciales, tbody);
    return;
  }

  const termino = terminoBusqueda.toLowerCase().trim();
  const resultados = credenciales.filter((cred) => {
    const nombreCompleto = (cred.nombreCompleto || "").toLowerCase();
    const usuario = (cred.usuario || "").toLowerCase();
    return nombreCompleto.includes(termino) || usuario.includes(termino);
  });

  agregarFilaTabla(resultados, tbody);
}

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener("DOMContentLoaded", async function () {
  const tbody = document.getElementById("tbodyCredenciales");
  const inputBusqueda = document.getElementById("inputBusqueda");
  const btnBuscar = document.getElementById("btnBuscar");
  const btnLimpiar = document.getElementById("btnLimpiar");

  console.log("📄 Iniciando carga de credenciales...");

  // Cargar datos al iniciar
  try {
    const resultCredenciales = await apiEndPoints.selectAllCredenciales();

    console.log("Datos recibidos:", resultCredenciales);

    if (
      resultCredenciales &&
      Array.isArray(resultCredenciales) &&
      resultCredenciales.length > 0
    ) {
      dataManager.saveAllData(resultCredenciales);
      agregarFilaTabla(resultCredenciales, tbody);
      console.log(
        "Credenciales cargadas:",
        resultCredenciales.length,
        "registros"
      );
    } else {
      console.warn("No se recibieron datos del servidor");
      const credencialesLocal = dataManager.readData();
      if (credencialesLocal.length > 0) {
        agregarFilaTabla(credencialesLocal, tbody);
        console.log("Cargando desde localStorage");
      } else {
        agregarFilaTabla([], tbody);
        console.log("No hay datos disponibles");
      }
    }
  } catch (error) {
    console.error("Error al cargar credenciales:", error);
    agregarFilaTabla([], tbody);
  }

  // ============================================
  // EVENT LISTENERS - BÚSQUEDA
  // ============================================

  // Buscar al hacer clic en el botón
  btnBuscar.addEventListener("click", function () {
    buscarCredenciales(inputBusqueda.value, tbody);
  });

  // Buscar al presionar Enter
  inputBusqueda.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      buscarCredenciales(inputBusqueda.value, tbody);
    }
  });

  // Limpiar búsqueda
  btnLimpiar.addEventListener("click", function () {
    inputBusqueda.value = "";
    const credenciales = dataManager.readData();
    agregarFilaTabla(credenciales, tbody);
  });

  // ============================================
  // EVENT LISTENER - ACCIONES DE LA TABLA
  // ============================================
  tbody.addEventListener("click", async function (event) {
    // ========== ACCIÓN EDITAR ==========
    if (event.target.id === Buttons.botones.btnEdit.id) {
      const rowEdit = event.target.closest("tr");
      const cells = rowEdit.querySelectorAll("td");
      datosCeldas = [];

      cells.forEach((cell, index) => {
        if (index >= cells.length - 1) return;

        const valorActual = cell.textContent.trim();
        datosCeldas.push(valorActual);

        // SOLO EDITAR COLUMNA 2 (CONTRASEÑA)
        if (index === 2) {
          cell.textContent = "";

          const input = document.createElement("input");
          input.type = "text";
          input.value = "";
          input.placeholder = "4 dígitos";
          input.className = "input-contrasena";
          input.maxLength = 4;
          input.inputMode = "numeric";
          input.autocomplete = "off";

          // Validación en tiempo real
          input.addEventListener("input", function (e) {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, "");

            // Cambiar color del borde según validación
            if (this.value.length === 4) {
              this.style.borderColor = "#4CAF50"; // Verde cuando es válido
              this.style.backgroundColor = "#f1f8f4";
            } else if (this.value.length > 0) {
              this.style.borderColor = "#ff9800"; // Naranja cuando está incompleto
              this.style.backgroundColor = "#fff8f0";
            } else {
              this.style.borderColor = "#ddd"; // Gris por defecto
              this.style.backgroundColor = "#fff";
            }
          });

          cell.appendChild(input);

          // Enfocar automáticamente el input
          setTimeout(() => input.focus(), 100);
        }
        // LOS DEMÁS CAMPOS NO SE MODIFICAN
      });

      // Cambiar botón Editar por Guardar
      Buttons.changeButtonEvent(
        event,
        Buttons.botones.btnSave.id,
        Buttons.botones.btnSave.ruta,
        Buttons.botones.btnSave.title
      );

      // Agregar botón Cancelar
      const btnCancelar = document.createElement("img");
      Buttons.crearBotonesAcciones(
        cells[cells.length - 1],
        btnCancelar,
        Buttons.botones.btnCancel.id,
        Buttons.botones.btnCancel.ruta,
        Buttons.botones.btnCancel.title
      );

      console.log(" Modo edición activado - Solo contraseña");
      return;
    }

    // ========== ACCIÓN GUARDAR ==========
    if (event.target.id === Buttons.botones.btnSave.id) {
      const rowSave = event.target.closest("tr");
      const idEmpleado = rowSave.dataset.idEmpleado;

      const inputContrasena = rowSave.querySelector("td:nth-child(3) input");

      // Validación de contraseña
      const contrasenaValue = inputContrasena.value.trim();

      if (contrasenaValue === "") {
        alert("❌ Debe ingresar una contraseña.");
        inputContrasena.focus();
        return;
      }

      if (!/^\d{4}$/.test(contrasenaValue)) {
        alert("❌ La contraseña debe ser exactamente 4 dígitos numéricos.");
        inputContrasena.focus();
        inputContrasena.select();
        return;
      }

      const objCredencialActualizada = {
        idEmpleado: parseInt(idEmpleado),
        contrasena: contrasenaValue
      };

      console.log("💾 Guardando contraseña para empleado:", idEmpleado);

      try {
        const response = await apiEndPoints.updateCredencial(
          idEmpleado,
          objCredencialActualizada
        );

        if (response.errorDB || response.errorServer) {
          alert("❌ Error: " + (response.errorDB || response.errorServer));
          return;
        }

        // Actualizar datos locales
        dataManager.updateData(parseInt(idEmpleado), objCredencialActualizada);

        // Recargar tabla
        const credenciales = await apiEndPoints.selectAllCredenciales();
        dataManager.saveAllData(credenciales);
        agregarFilaTabla(credenciales, tbody);

        alert("Contraseña actualizada correctamente");
        console.log(" Actualización exitosa");
      } catch (error) {
        console.error("❌ Error:", error);
        alert("❌ Error de conexión con el servidor");
      }

      return;
    }

    // ========== ACCIÓN CANCELAR ==========
    if (event.target.id === Buttons.botones.btnCancel.id) {
      const rowCancel = event.target.closest("tr");
      const cells = rowCancel.querySelectorAll("td");

      // Restaurar valores originales
      cells.forEach((cell, index) => {
        if (index < cells.length - 1) {
          if (index === 5) {
            const estadoOriginal = datosCeldas[index];
            const badgeClass =
              estadoOriginal === "Activo" ? "badge-activo" : "badge-inactivo";
            cell.innerHTML = `<span class="badge ${badgeClass}">${estadoOriginal}</span>`;
          } else {
            cell.textContent = datosCeldas[index];
          }
        }
      });

      // Remover botón Cancelar
      const btnCancelar = cells[cells.length - 1].querySelector(
        "#" + Buttons.botones.btnCancel.id
      );
      if (btnCancelar) btnCancelar.remove();

      // Restaurar botón Editar
      const btnGuardar = cells[cells.length - 1].querySelector(
        "#" + Buttons.botones.btnSave.id
      );
      Buttons.changeButtonNotEvent(
        btnGuardar,
        Buttons.botones.btnEdit.id,
        Buttons.botones.btnEdit.ruta,
        Buttons.botones.btnEdit.title
      );

      console.log("❌ Edición cancelada");
      return;
    }
  });
});