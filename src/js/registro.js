import apiEndPoints from './apiEndPoints.js';

document.addEventListener('DOMContentLoaded', () => {
    const registroForm = document.getElementById('registroForm');
    const inputTelefono = document.getElementById('txtTelefono');
    const inputCodigoPostal = document.getElementById('txtCP');

    // 🔹 VALIDACIÓN EN TIEMPO REAL PARA TELÉFONO
    if (inputTelefono) {
        inputTelefono.addEventListener('input', function (e) {
            // Eliminar cualquier carácter que no sea número
            this.value = this.value.replace(/[^0-9]/g, '');

            // Limitar a 10 dígitos
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }

            // Validación visual
            if (this.value.length === 10) {
                this.style.borderColor = '#4CAF50'; // Verde = correcto
            } else if (this.value.length > 0) {
                this.style.borderColor = '#ff9800'; // Naranja = incompleto
            } else {
                this.style.borderColor = ''; // Default
            }
        });

        // Validación al perder el foco
        inputTelefono.addEventListener('blur', function () {
            if (this.value.length > 0 && this.value.length !== 10) {
                alert('El teléfono debe tener exactamente 10 dígitos');
                this.focus();
            }
        });
    }

    // 🔹 VALIDACIÓN EN TIEMPO REAL PARA CÓDIGO POSTAL
    if (inputCodigoPostal) {
        inputCodigoPostal.addEventListener('input', function (e) {
            // Solo números
            this.value = this.value.replace(/[^0-9]/g, '');

            // Limitar a 5 dígitos
            if (this.value.length > 5) {
                this.value = this.value.slice(0, 5);
            }

            // Validación visual
            if (this.value.length === 5) {
                this.style.borderColor = '#4CAF50';
            } else if (this.value.length > 0) {
                this.style.borderColor = '#ff9800';
            } else {
                this.style.borderColor = '';
            }
        });
    }

    if (registroForm) {
        registroForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            // 1. RECOLECCIÓN DE DATOS
            const formData = new FormData(registroForm);
            const datosFormulario = {};
            formData.forEach((value, key) => {
                datosFormulario[key] = value.trim(); // Eliminar espacios en blanco
            });

            // 🔹 VALIDACIONES ADICIONALES ANTES DE ENVIAR

            // Validar teléfono
            if (!/^[0-9]{10}$/.test(datosFormulario.telefono)) {
                alert('❌ El teléfono debe contener exactamente 10 dígitos numéricos.');
                inputTelefono.focus();
                return;
            }

            // Validar código postal
            if (!/^[0-9]{5}$/.test(datosFormulario.codigoPostal)) {
                alert('❌ El código postal debe contener exactamente 5 dígitos.');
                inputCodigoPostal.focus();
                return;
            }

            // Validar contraseña
            if (datosFormulario.contrasena.length < 4) {
                alert('❌ La contraseña debe tener al menos 4 caracteres.');
                document.getElementById('txtContraseña').focus();
                return;
            }

            // Validar que el número de calle sea un número
            if (isNaN(datosFormulario.numCalle) || datosFormulario.numCalle <= 0) {
                alert('❌ El número de calle debe ser un número válido.');
                document.getElementById('txtNumCalle').focus();
                return;
            }

            console.log('📤 Datos a enviar:', datosFormulario);

            try {
                // 2. ENVÍO DIRECTO
                console.log('📤 Enviando datos:', datosFormulario);

                const respuesta = await apiEndPoints.registrarUsuario(datosFormulario);

                console.log('📥 Respuesta completa del servidor:', respuesta);
                console.log('📥 Tipo de respuesta:', typeof respuesta);
                console.log('📥 Keys de respuesta:', Object.keys(respuesta));

                if (respuesta.creado) {
                    alert('✅ ¡Usuario registrado exitosamente!\n\n' +
                        'ID de Control: ' + respuesta.idControlGenerado + '\n' +
                        'ID Empleado: ' + respuesta.idInsertado);
                    registroForm.reset();

                    // Resetear estilos de validación
                    if (inputTelefono) inputTelefono.style.borderColor = '';
                    if (inputCodigoPostal) inputCodigoPostal.style.borderColor = '';

                } else if (respuesta.errorDB || respuesta.errorServer) {
                    alert('❌ Error en el registro:\n\n' + (respuesta.errorDB || respuesta.errorServer));
                    console.error('Error del servidor:', respuesta);
                } else {
                    alert('❌ Error desconocido al intentar registrar el usuario.');
                    console.error('Respuesta inesperada:', respuesta);
                }
            } catch (error) {
                alert('❌ Error de conexión o de red.\n\nPor favor, verifique su conexión e inténtalo más tarde.');
                console.error('Error de red/fetch:', error);
            }
        });
    }
});