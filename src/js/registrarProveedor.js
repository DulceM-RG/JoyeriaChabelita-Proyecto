// CONFIGURACIÓN
const API_URL = './src/database/registrarProveedor.php';
//src\database\registrarProveedor.php
// ============================================
// 1. CONFIGURAR EVENTOS AL CARGAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    configurarEventos();
});

// ============================================
// 2. CONFIGURAR EVENTOS
// ============================================
function configurarEventos() {
    // Validación en tiempo real del RFC
    document.getElementById('txtRfc').addEventListener('input', function (e) {
        // Convertir a mayúsculas automáticamente
        this.value = this.value.toUpperCase();

        // Permitir solo letras, números, Ñ y &
        this.value = this.value.replace(/[^A-ZÑ&0-9]/g, '');

        // Limitar a 13 caracteres
        if (this.value.length > 13) {
            this.value = this.value.substring(0, 13);
        }

        // Mostrar longitud actual
        validarLongitudRfc(this.value);
    });

    // Validación en tiempo real de Razón Social
    document.getElementById('txtRazonSocial').addEventListener('input', function (e) {
        const maxLength = 100;
        const currentLength = this.value.length;

        if (currentLength > maxLength) {
            this.value = this.value.substring(0, maxLength);
            mostrarError(`La razón social no puede exceder ${maxLength} caracteres`);
        }

        // Permitir solo letras, números, espacios y caracteres especiales básicos
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&\-]/g, '');
    });

    // Validación en tiempo real del Teléfono
    document.getElementById('txtTelefono').addEventListener('input', function (e) {
        // Permitir solo números
        this.value = this.value.replace(/[^0-9]/g, '');

        // Limitar a 10 dígitos
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
        }
    });

    // Enviar formulario
    document.getElementById('registroForm').addEventListener('submit', registrarProveedor);
}

// ============================================
// 3. VALIDAR LONGITUD DEL RFC
// ============================================
function validarLongitudRfc(rfc) {
    const longitud = rfc.length;
    const inputRfc = document.getElementById('txtRfc');

    if (longitud === 0) {
        inputRfc.style.borderColor = '';
        return;
    }

    if (longitud === 12 || longitud === 13) {
        inputRfc.style.borderColor = '#4caf50'; // Verde
    } else {
        inputRfc.style.borderColor = '#ff9800'; // Naranja
    }
}

// ============================================
// 4. VALIDACIONES ANTES DE ENVIAR
// ============================================
function validarFormulario() {
    const errores = [];

    // 1. RFC
    const rfc = document.getElementById('txtRfc').value.trim();
    if (!rfc) {
        errores.push('El RFC es obligatorio');
    } else {
        const longitudRfc = rfc.length;
        if (longitudRfc < 12 || longitudRfc > 13) {
            errores.push('El RFC debe tener 12 caracteres (persona moral) o 13 caracteres (persona física)');
        }

        // Validar formato básico
        const patronRfc = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
        if (!patronRfc.test(rfc)) {
            errores.push('Formato de RFC inválido. Ejemplo válido: ABC123456XYZ o ABCD123456XYZ');
        }
    }

    // 2. Razón Social
    const razonSocial = document.getElementById('txtRazonSocial').value.trim();
    if (!razonSocial) {
        errores.push('La razón social es obligatoria');
    } else if (razonSocial.length < 3) {
        errores.push('La razón social debe tener al menos 3 caracteres');
    } else if (razonSocial.length > 100) {
        errores.push('La razón social no puede exceder 100 caracteres');
    }

    // 3. Teléfono (opcional, pero si se proporciona debe ser válido)
    const telefono = document.getElementById('txtTelefono').value.trim();
    if (telefono) {
        if (!/^[0-9]{10}$/.test(telefono)) {
            errores.push('El teléfono debe tener exactamente 10 dígitos');
        }
    }

    return errores;
}

// ============================================
// 5. REGISTRAR PROVEEDOR
// ============================================
async function registrarProveedor(e) {
    e.preventDefault();

    // Validar formulario
    const errores = validarFormulario();
    if (errores.length > 0) {
        mostrarError(errores.join('\n'));
        return;
    }

    // Preparar datos
    const datos = {
        rfc: document.getElementById('txtRfc').value.trim().toUpperCase(),
        razonSocial: document.getElementById('txtRazonSocial').value.trim(),
        telefono: document.getElementById('txtTelefono').value.trim()
    };

    try {
        // Deshabilitar botón
        const btnSubmit = document.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
        }

        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            mostrarExito(resultado.message);
            limpiarFormulario();
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Guardar Proveedor';
            }
        } else {
            mostrarError(resultado.message);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Guardar Proveedor';
            }
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarError('No se pudo conectar con el servidor');
        const btnSubmit = document.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Proveedor';
        }
    }
}

// ============================================
// 6. LIMPIAR FORMULARIO
// ============================================
function limpiarFormulario() {
    document.getElementById('registroForm').reset();
    document.getElementById('txtRfc').style.borderColor = '';
}

// ============================================
// 7. MOSTRAR MENSAJES
// ============================================
function mostrarError(mensaje) {
    alert('❌ ERROR:\n\n' + mensaje);
}

function mostrarExito(mensaje) {
    alert('✅ ÉXITO:\n\n' + mensaje + '\n\nPuedes registrar otro proveedor.');
}

// ============================================
// 8. FORMATEAR RFC MIENTRAS SE ESCRIBE (OPCIONAL)
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const txtRfc = document.getElementById('txtRfc');

    // Mostrar ayuda visual del formato
    txtRfc.addEventListener('focus', function () {
        console.log('Formato RFC:\n- Persona Moral: 3 letras + 6 números + 3 alfanuméricos (12 caracteres)\n- Persona Física: 4 letras + 6 números + 3 alfanuméricos (13 caracteres)');
    });
});