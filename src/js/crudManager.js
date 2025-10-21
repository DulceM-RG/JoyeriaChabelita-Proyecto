export default class CrudManager {
    constructor(urlBase) {
        this.apiUrl = urlBase;
    }

    /* async createData(nombreTabla, dataForm, nombreArchivo) {
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
     }*/

    async createData(nombreTabla, dataForm, nombreArchivo) {
        try {
            const response = await fetch(this.apiUrl + nombreArchivo + '.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ datosFormulario: dataForm, tabla: nombreTabla })
            });

            // 🔹 MEJORADO: Intentar leer el JSON incluso si hay error HTTP
            const contentType = response.headers.get("content-type");

            // Verificar que la respuesta sea JSON
            if (contentType && contentType.includes("application/json")) {
                const jsonResponse = await response.json();

                // Si el servidor devolvió un error HTTP (400, 500, etc.)
                if (!response.ok) {
                    // Devolver el error JSON del servidor (con errorDB o errorServer)
                    return jsonResponse;
                }

                // Si todo está OK, devolver la respuesta exitosa
                return jsonResponse;
            } else {
                // Si no es JSON, probablemente es un error de PHP o del servidor
                const textResponse = await response.text();
                return {
                    errorServer: `Error del servidor (${response.status}): ${textResponse.substring(0, 200)}`
                };
            }

        } catch (error) {
            // Error de red o conexión
            return {
                errorServer: 'Error de conexión: ' + error.message
            };
        }
    }
    /*p
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
} 