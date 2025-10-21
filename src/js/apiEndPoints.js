// apiEndPoints.js

import CrudManager from './crudManager.js';
// AsegÃºrate de que la ruta sea correcta
// La URL debe apuntar a la carpeta donde estÃ¡n tus scripts PHP
const URL_BASE = 'http://localhost/JoyeriaChabelita-Proyecto/src/database/';

// 1. Instanciar CrudManager
const crud = new CrudManager(URL_BASE);

const apiEndPoints = {
    // 1. OBTENER TODOS LOS DATOS (usando CrudManager)
    selectAllCredenciales: async () => {
        // Llama a CrudManager.readAllData enviando el nombre de la tabla
        return await crud.readAllData('credenciales');
    },

    // 2. ACTUALIZAR (usando CrudManager)
    updateCredencial: async (idEmpleado, data) => {
        // Prepara los datos que espera CrudManager: todos los campos a actualizar
        const datosParaUpdate = {
            idEmpleado: idEmpleado,
            contrasena: data.contrasena,
            activo: data.activo,
            intentosFallidos: data.intentosFallidos
            // Agrega otros campos si son necesarios en la tabla credenciales
        };
        // Llama a CrudManager.updateData
        return await crud.updateData('credenciales', datosParaUpdate);
    },

    registrarUsuario: async (datos) => {
        return await crud.createData('credenciales', datos, 'insertCredencial');
    },


};


export default apiEndPoints;