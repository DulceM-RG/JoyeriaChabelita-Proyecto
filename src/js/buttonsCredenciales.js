/* Almacena toda la configuración de los botones en un solo lugar. Esto facilita 
cambiar rutas o nombres sin tocar el código principal.*/

const botones = {
    btnEdit:
    {
        'id': 'btnEditar',
        'ruta': './src/assets/icon/boligrafo.png',
        'title': 'Editar',
        'alt': 'Editar',
        'className': 'btn-img'
    },

    btnSave:
    {
        'id': 'btnGuardar',
        'ruta': './src/assets/icon/guardar.png',
        'title': 'GUardar',
        'alt': 'Guardar',
        'className': 'btn-img'
    },
    btnCancel:
    {
        'id': 'btnCancelar',
        'ruta': './src/assets/icon/cancelar.png',
        'title': 'Cancelar',
        'alt': 'cancelar',
        'className': 'btn-img'
    },

};


// recibe la celda de acciones, nombre de boton de imagen, id, ruta y titulo
function crearBotonesAcciones(celdaAcciones, nameImgButtons, id, ruta, title) {
    nameImgButtons.id = id; // Asignar un ID al botón de editar
    nameImgButtons.src = ruta; // Ruta de la imagen para editar
    nameImgButtons.alt = title;
    nameImgButtons.title = title;
    nameImgButtons.className = 'btn-img'; // Clase opcional para estilos
    celdaAcciones.appendChild(nameImgButtons);
}
//Cambia las propiedades de un botón cuando se dispara un evento (ejemplo: al hacer clic).
// recibe el boton que activa el evento, nuevo id, nueva ruta y el nuevo titulo
function changeButtonEvent(event, nuevoID, nuevaRuta, newTitle) {
    if (event.target) { // asegurar que el botón que lanzó el evento exista
        event.target.id = nuevoID; // Cambia el ID del botón de editar a guardar
        event.target.src = nuevaRuta; // Cambia la imagen del botón
        event.target.title = newTitle; // Cambia el título del botón
        event.alt = newTitle; // Cambia el texto alternativo del botón
    }
}

// recibe el botón, nuevo id, nueva ruta y el nuevo titulo
function changeButtonNotEvent(boton, nuevoID, nuevaRuta, newTitle) {
    if (boton) { // asegúrate de que el botón exista
        boton.id = nuevoID; // Cambia el ID del botón de eliminar a cancelar
        boton.src = nuevaRuta; // Cambia la imagen del botón
        boton.title = newTitle; // Cambia el título del botón
        boton.alt = newTitle; // Cambia el texto alternativo del botón
    }
}

export default { botones, changeButtonEvent, crearBotonesAcciones, changeButtonNotEvent };