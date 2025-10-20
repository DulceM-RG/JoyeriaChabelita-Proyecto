/*gestionar y estandarizar las peticiones HTTP */
export default class CrudManager {
    constructor(urlBase) {
        this.apiUrl = urlBase;
    }

    async createData(nombreTabla, dataForm, nombreArchivo) {
        try {
            const response = await fetch(this.apiUrl + nombreArchivo + '.php',
                {
                    method: 'POST', // método de la petición
                    headers: { 'Content-Type': 'application/json' }, // tipo de contenido que se envía al servidor
                    body: JSON.stringify({ datosFormulario: dataForm, tabla: nombreTabla }) // convierte el objeto a un string formato JSON

                }
            );
            if (!response.ok) {
                throw new Error('Error del servidor HTTP: ' + response.status); // lanza un error si el archivo o ruta es incorrecto
            }
            return await response.json(); // devuelve la respuesta JSON del servidor con los datos
        } catch (error) {
            return { errorServer: 'Error al insertar los datos: ' + error.message }; // manejo de error con el servidor
        }
    }
    /*para que puedas editar los datos del empleado. Debes implementar la lógica fetch similar a createData, pero apuntando a un script PHP de UPDATE. */


    /* Llama directamente a readAllData.php*/
    async readAllData(nombreTabla) {
        try {

            const response = await fetch(this.apiUrl + 'readAllData.php',
                {
                    method: 'POST', // método de la petición
                    headers: { 'Content-Type': 'application/json' }, // tipo de contenido que se envía al servidor
                    body: JSON.stringify({ tabla: nombreTabla }) // convierte un objeto en cadena JSON

                }
            );
            if (!response.ok) {
                throw new Error('Error del servidor HTTP: ' + response.status); // lanza un error si el archivo o ruta es incorrecto
            }
            return await response.json(); // devuelve la respuesta JSON del servidor con los datos
        } catch (error) {
            return { errorServer: 'Error al obtener los datos: ' + error.message }; // manejo de error con el servidor
        }
    }

    // -------------------- MÉTODO UPDATE IMPLEMENTADO --------------------
    async updateData(nombreTabla, datosFormulario) {
        try {
            // Llama al script PHP responsable de la actualización
            const response = await fetch(this.apiUrl + 'updateCredenciales.php',
                {
                    method: 'POST', // Método de la petición
                    headers: { 'Content-Type': 'application/json' },
                    // Envía la tabla y los datos completos del registro a actualizar (incluyendo el ID)
                    body: JSON.stringify({ datosFormulario: datosFormulario, tabla: nombreTabla })
                }
            );
            if (!response.ok) {
                throw new Error('Error del servidor HTTP: ' + response.status);
            }
            // Devuelve la respuesta JSON del servidor (ej. { "mensaje": "Actualización exitosa" })
            return await response.json();
        } catch (error) {
            return { errorServer: 'Error al actualizar los datos: ' + error.message };
        }
    }
    // -------------------- MÉTODO DELETE IMPLEMENTADO --------------------
    async deleteData(nombreTabla, datosIdentificador) {
        try {
            // Llama al script PHP responsable de la eliminación
            const response = await fetch(this.apiUrl + 'deleteData.php',
                {
                    method: 'POST', // Método de la petición
                    headers: { 'Content-Type': 'application/json' },
                    // Envía la tabla y el identificador único del registro a eliminar (ej. { id: 5 })
                    body: JSON.stringify({ datosIdentificador: datosIdentificador, tabla: nombreTabla })
                }
            );
            if (!response.ok) {
                throw new Error('Error del servidor HTTP: ' + response.status);
            }
            // Devuelve la respuesta JSON del servidor (ej. { "mensaje": "Eliminación exitosa" })
            return await response.json();
        } catch (error) {
            return { errorServer: 'Error al eliminar los datos: ' + error.message };
        }
    }
    // ----

} 