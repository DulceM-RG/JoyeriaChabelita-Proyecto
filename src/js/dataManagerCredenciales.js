// dataManagerCredenciales.js - VERSIÓN COMPLETA
export default class DataManager {
    constructor(keyLocal) {
        this.keyLocal = keyLocal;
        this.dblocal = JSON.parse(localStorage.getItem(this.keyLocal)) || [];
    }

    // CREATE - Agregar una nueva credencial
    createData(objCredencial) {
        this.dblocal.push(objCredencial);
        localStorage.setItem(this.keyLocal, JSON.stringify(this.dblocal));
    }

    // READ - Leer todas las credenciales
    readData() {
        return this.dblocal;
    }

    // ✅ SAVE ALL - Guardar todos los datos (reemplazar todo)
    saveAllData(arrayCredenciales) {
        this.dblocal = arrayCredenciales;
        localStorage.setItem(this.keyLocal, JSON.stringify(this.dblocal));
    }

    // UPDATE - Actualizar una credencial
    updateData(idEmpleado, objCredencial) {
        this.dblocal = this.dblocal.map((credencial) => {
            if (credencial.idEmpleado === idEmpleado) {
                return { ...credencial, ...objCredencial };
            }
            return credencial;
        });
        localStorage.setItem(this.keyLocal, JSON.stringify(this.dblocal));
    }

    // DELETE - Eliminar una credencial
    delete(idEmpleado) {
        this.dblocal = this.dblocal.filter((credencial) => credencial.idEmpleado !== idEmpleado);
        localStorage.setItem(this.keyLocal, JSON.stringify(this.dblocal));
    }

    // CLEAR - Limpiar el almacenamiento
    clear() {
        localStorage.removeItem(this.keyLocal);
        this.dblocal = [];
    }
}