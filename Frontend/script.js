// ================= SISTEMA DE PESTAÑAS (LOGIN / REGISTRO) =================
// Selecciona todas las pestañas de autenticación (#auth-tabs li) para alternar entre "Ingresar" y "Crear Cuenta"
const authTabs = document.querySelectorAll('#auth-tabs li');
if (authTabs.length > 0) {
  authTabs.forEach(item => {
    item.addEventListener('click', function () {
      // Marca visualmente la pestaña activa y quita el estado activo de las demás
      authTabs.forEach(li => li.classList.remove('is-active'));
      this.classList.add('is-active');

      // Lee el atributo data-tab ("login" o "register") para mostrar el formulario correspondiente
      const tab = this.getAttribute('data-tab');
      const formLogin = document.getElementById('form-login');
      const formRegister = document.getElementById('form-register');

      if (formLogin && formRegister) {
        if (tab === 'login') {
          formLogin.classList.remove('is-hidden');
          formRegister.classList.add('is-hidden');
        } else {
          formRegister.classList.remove('is-hidden');
          formLogin.classList.add('is-hidden');
        }
      }
    });
  });
}

// ================= LÓGICA DE INGRESO (LOGIN) =================
// Envía las credenciales al backend PHP, guarda la sesión en localStorage y configura la vista según el rol
function manejarLogin(event) {
  // Evita que el formulario recargue la página al enviarse
  event.preventDefault();

  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');
  if (!emailInput || !passwordInput) return;

  // Obtiene y limpia espacios en blanco de los campos ingresados
  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  const datosLogin = { email, password };

  // Realiza una petición POST asíncrona a login.php enviando JSON
  fetch('../Backend/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosLogin)
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);

        // Determina si el usuario es Administrador según el rol devuelto por la base de datos
        const esAdmin = (data.rol === 'Administrador');
        // Conserva el email real devuelto por el servidor (útil cuando el usuario inició sesión con su nombre)
        const emailRealSesion = data.email || (email.includes('@') ? email : '');

        // Estructura de sesión guardada en localStorage con marca de tiempo para expiración temporal
        const datosSesion = {
          nombre: data.nombre,
          email: emailRealSesion, // <--- GUARDAMOS EL EMAIL REAL DEL USUARIO LOGUEADO
          rol: data.rol,
          esAdmin: esAdmin,
          timestamp: new Date().getTime()
        };
        localStorage.setItem('sesion_usuario', JSON.stringify(datosSesion));

        // Oculta la pantalla de login y muestra la aplicación principal
        const loginSection = document.getElementById('login-section');
        const mainApp = document.getElementById('main-app');
        if (loginSection) loginSection.classList.add('is-hidden');
        if (mainApp) mainApp.classList.remove('is-hidden');

        // Actualiza la insignia de usuario en la barra de navegación y muestra/oculta el panel de admin
        const userBadge = document.getElementById('user-badge');
        const adminPanel = document.getElementById('admin-panel');

        if (userBadge) {
          if (esAdmin) {
            userBadge.textContent = 'Administrador (Admin)';
            userBadge.className = 'tag is-danger is-medium mr-3';
            if (adminPanel) adminPanel.classList.remove('is-hidden');
          } else {
            userBadge.textContent = 'Cliente: ' + data.nombre;
            userBadge.className = 'tag is-success is-medium mr-3';
            if (adminPanel) adminPanel.classList.add('is-hidden');
          }
        }

        // Autocompleta el campo de nombre del solicitante en los formularios de reserva si está presente
        const inputNombreCliente = document.getElementById('nombre-cliente');
        if (inputNombreCliente && data.nombre) {
          inputNombreCliente.value = data.nombre;
        }

      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error al conectar con el servidor.');
    });
}

// ================= LÓGICA DE REGISTRO =================
// Envía los datos de un nuevo cliente a registro_usuarios.php y cambia a la pestaña de login si tiene éxito
function manejarRegistro(event) {
  event.preventDefault();

  const formRegister = document.getElementById('form-register');
  if (!formRegister) return;
  const inputs = formRegister.querySelectorAll('input');

  // Extrae nombre, correo y contraseña del formulario de registro
  const nombre = inputs[0].value;
  const email = inputs[1].value;
  const password = inputs[2].value;

  const datosCliente = { nombre, email, password };

  fetch('../Backend/registro_usuarios.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosCliente)
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        // Cambia automáticamente a la pestaña "Ingresar" y limpia el formulario de registro
        const loginTab = document.querySelector('#auth-tabs li[data-tab="login"]');
        if (loginTab) loginTab.click();
        formRegister.reset();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error al conectar con el servidor.');
    });
}

// ================= CERRAR SESIÓN =================
// Elimina la sesión almacenada en el navegador, limpia los campos y vuelve a mostrar el login
function cerrarSesion() {
  localStorage.removeItem('sesion_usuario');
  const loginSection = document.getElementById('login-section');
  const mainApp = document.getElementById('main-app');
  const loginPassword = document.getElementById('login-password');
  const formLogin = document.getElementById('form-login');
  const inputNombreCliente = document.getElementById('nombre-cliente');

  if (loginSection) loginSection.classList.remove('is-hidden');
  if (mainApp) mainApp.classList.add('is-hidden');
  if (loginPassword) loginPassword.value = '';
  if (formLogin) formLogin.reset();
  if (inputNombreCliente) inputNombreCliente.value = '';
}

// ================= GALERÍA Y PRECIOS DE SALONES =================
// Cambia la imagen principal de la galería con una transición suave y resalta la miniatura seleccionada
function cambiarFoto(urlNueva, tarjetaClickeada) {
  const imgPrincipal = document.getElementById('foto-principal');
  if (!imgPrincipal) return;

  imgPrincipal.style.opacity = '0.2';

  setTimeout(() => {
    imgPrincipal.src = urlNueva;
    imgPrincipal.style.opacity = '1';
  }, 200);

  document.querySelectorAll('.thumb-card').forEach(card => card.classList.remove('active'));
  if (tarjetaClickeada) tarjetaClickeada.classList.add('active');
}

// Muestra u oculta el selector de menú de catering según si está marcado su checkbox
function toggleCateringMenu() {
  const checkboxCatering = document.getElementById('check-catering');
  const contenedorMenu = document.getElementById('contenedor-menu-select');
  const opcionMenu = document.getElementById('opcion-menu');

  if (!checkboxCatering || !contenedorMenu) return;

  if (checkboxCatering.checked) {
    contenedorMenu.classList.remove('is-hidden');
  } else {
    contenedorMenu.classList.add('is-hidden');
    if (opcionMenu) opcionMenu.value = "";
  }
}

// Calcula en pantalla el precio total sumando el precio base del salón y los servicios adicionales tildados
function calcularTotal() {
  const elementoBase = document.getElementById('precio-base');
  let precioBase = 180000;

  if (elementoBase) {
    // Limpia símbolos de moneda y separadores de miles para convertir el texto a número entero
    const textoLimpio = elementoBase.textContent.replace('$', '').replace(/\./g, '').trim();
    precioBase = parseInt(textoLimpio, 10) || 180000;
  }

  let total = precioBase;
  const checkboxes = document.querySelectorAll('.servicio-check:checked');

  checkboxes.forEach(checkbox => {
    total += parseInt(checkbox.getAttribute('data-precio')) || 0;
  });

  // Formatea el resultado final en formato de moneda local (es-AR)
  const elementoPrecio = document.getElementById('precio-final');
  if (elementoPrecio) {
    elementoPrecio.textContent = '$' + total.toLocaleString('es-AR');
  }
}


// ================= ENVÍO DE RESERVA CON MÉTODO DE PAGO =================
// Recopila fecha, turno, datos del solicitante, servicios seleccionados y método de pago para enviarlo a procesar_reserva.php
function enviarReservaConMetodo(event, idSalon, metodoPago) {
  event.preventDefault();

  const boton = event.currentTarget;
  const formBox = boton.closest('form');

  const fechaInput = document.getElementById('fecha-evento');
  const fechaEvento = fechaInput ? fechaInput.value : '';

  // Obtiene el selector de turno y normaliza el valor ("Día completo", "Tarde" o "Noche")
  const selectTurno = document.getElementById('turno-evento') || (formBox ? formBox.querySelector('select:not(#opcion-menu)') : null);
  let turnoSeleccionado = "Noche";
  if (selectTurno && selectTurno.value) {
    const valorTurno = selectTurno.value.trim();
    if (valorTurno.toLowerCase().startsWith('día completo') || valorTurno.toLowerCase().startsWith('dia completo')) {
      turnoSeleccionado = "Día completo";
    } else if (valorTurno.toLowerCase().startsWith('tarde')) {
      turnoSeleccionado = "Tarde";
    } else if (valorTurno.toLowerCase().startsWith('noche')) {
      turnoSeleccionado = "Noche";
    } else {
      turnoSeleccionado = valorTurno;
    }
  }

  const txtNombre = document.getElementById('nombre-cliente') || (formBox ? formBox.querySelector('input[type="text"]') : null);
  const txtDni = document.getElementById('dni-cliente');
  const txtTel = document.getElementById('telefono-cliente');
  const elementoPrecio = document.getElementById('precio-final');

  let montoTotal = 180000;
  if (elementoPrecio) {
    montoTotal = parseInt(elementoPrecio.textContent.replace('$', '').replace(/\./g, ''), 10) || 180000;
  }

  // Valida que los campos obligatorios de la reserva estén completos antes de enviar
  if (!fechaEvento || !txtDni || !txtDni.value || !txtTel || !txtTel.value) {
    alert("Por favor completá la fecha del evento, DNI y teléfono obligatorios.");
    return;
  }

  // RECUPERAR EL EMAIL Y NOMBRE DEL USUARIO LOGUEADO DESDE LOCALSTORAGE
  const sesionGuardada = localStorage.getItem('sesion_usuario');
  let emailCliente = "cliente@evaluacion.com"; // Resguardo por defecto si no hay sesión
  let nombreSesion = "";

  if (sesionGuardada) {
    const sesion = JSON.parse(sesionGuardada);
    if (sesion.email) {
      emailCliente = sesion.email;
    }
    if (sesion.nombre) {
      nombreSesion = sesion.nombre;
    }
  }

  // Reúne los IDs numéricos de todos los servicios adicionales marcados por el usuario
  const serviciosSeleccionados = [];
  document.querySelectorAll('.servicio-check:checked').forEach(checkbox => {
    serviciosSeleccionados.push(parseInt(checkbox.value, 10));
  });

  // Prioriza el nombre escrito en el formulario, luego el de la sesión y por último un valor por defecto
  const nombreFinal = (txtNombre && txtNombre.value.trim()) ? txtNombre.value.trim() : (nombreSesion || "Cliente Web");

  // Arma el payload JSON que consumirá el backend PHP
  const datosReserva = {
    id_salon: idSalon,
    fecha_evento: fechaEvento,
    turno: turnoSeleccionado,
    monto_total: montoTotal,
    metodo_pago: metodoPago, 
    nombre: nombreFinal,
    dni: txtDni.value,
    telefono: txtTel.value,
    email: emailCliente, // <--- SE ENVÍA EL CORREO REAL DEL CLIENTE AUTENTICADO
    servicios: serviciosSeleccionados
  };

  fetch('../Backend/procesar_reserva.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosReserva)
  })
    .then(response => response.json())
    .then(data => {
      alert(data.message);
      if (data.status === 'success') {
        location.reload();
      }
    })
    .catch(error => {
      console.error('Error en fetch:', error);
      alert('Hubo un error de conexión con el servidor.');
    });
}


// ================= BUSCADOR DE SALONES =================
// Catálogo local de salones con capacidad, descripción y enlace a su página de detalle
const salones = [
  { id: 1, nombre: "Salon Imperial", capacidad: 350, url: "SALON1.html", descripcion: "Espacio elegante para grandes eventos y casamientos." },
  { id: 2, nombre: "Casita de fiesta", capacidad: 70, url: "SALON2.html", descripcion: "Espacio con máquinas arcade, ping pong y pool para cumpleaños." },
  { id: 3, nombre: "Salon Quincho", capacidad: 150, url: "SALON3.html", descripcion: "Quincho campestre ideal para asados y eventos al aire libre." }
];

// Filtra los salones por capacidad mínima (si se ingresa un número) o por coincidencia de nombre (si es texto)
function realizarBusqueda() {
  const input = document.getElementById('input-buscar');
  const contenedor = document.getElementById('resultados-busqueda');

  if (!input || !contenedor) return;

  const query = input.value.trim();
  contenedor.innerHTML = '';

  if (!query) {
    contenedor.innerHTML = '<div class="column is-12"><p class="has-text-warning">Por favor, ingresá un nombre o número de personas.</p></div>';
    return;
  }

  let resultados = [];
  const esNumero = !isNaN(query) && query !== '';

  if (esNumero) {
    const cantidadInvitados = parseInt(query, 10);
    resultados = salones.filter(salon => salon.capacidad >= cantidadInvitados);
  } else {
    resultados = salones.filter(salon => salon.nombre.toLowerCase().includes(query.toLowerCase()));
  }

  if (resultados.length === 0) {
    contenedor.innerHTML = '<div class="column is-12"><div class="notification is-warning is-light">No se encontraron salones que coincidan con la búsqueda.</div></div>';
    return;
  }

  // Renderiza una tarjeta por cada salón que cumpla con el criterio de búsqueda
  resultados.forEach(salon => {
    contenedor.innerHTML += `
      <div class="column is-4">
        <div class="card">
          <div class="card-content">
            <p class="title is-5">${salon.nombre}</p>
            <p class="subtitle is-6 text-muted"><i class="fas fa-users mr-1"></i> Capacidad: Hasta ${salon.capacidad} personas</p>
            <p class="is-size-7 mb-4">${salon.descripcion}</p>
            <a href="${salon.url}" class="button is-warning is-small is-fullwidth has-text-weight-bold">Ver Salón</a>
          </div>
        </div>
      </div>`;
  });
}

// Configuración inicial y verificación de sesión temporal al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
  const loginSection = document.getElementById('login-section');
  const mainApp = document.getElementById('main-app');

  // Verificación de sesión temporal (3 minutos) almacenada en localStorage
  const sesionGuardada = localStorage.getItem('sesion_usuario');
  let sesionValida = false;

  if (sesionGuardada) {
    const sesion = JSON.parse(sesionGuardada);
    const ahora = new Date().getTime();
    const tresMinutos = 3 * 60 * 1000;

    if (ahora - sesion.timestamp < tresMinutos) {
      sesionValida = true;
      if (loginSection && mainApp) {
        loginSection.classList.add('is-hidden');
        mainApp.classList.remove('is-hidden');
      }

      // Restaura el badge del usuario y la visibilidad del panel de administrador según la sesión guardada
      const userBadge = document.getElementById('user-badge');
      const adminPanel = document.getElementById('admin-panel');

      if (userBadge) {
        if (sesion.esAdmin) {
          userBadge.textContent = 'Administrador (Admin)';
          userBadge.className = 'tag is-danger is-medium mr-3';
          if (adminPanel) adminPanel.classList.remove('is-hidden');
        } else {
          userBadge.textContent = 'Cliente: ' + sesion.nombre;
          userBadge.className = 'tag is-success is-medium mr-3';
          if (adminPanel) adminPanel.classList.add('is-hidden');
        }
      }

      // Autocompleta el nombre del cliente al cargar la vista de reserva
      const inputNombreCliente = document.getElementById('nombre-cliente');
      if (inputNombreCliente && sesion.nombre && !inputNombreCliente.value) {
        inputNombreCliente.value = sesion.nombre;
      }
    } else {
      // Si expiró el tiempo de sesión, borra los datos guardados
      localStorage.removeItem('sesion_usuario');
    }
  }

  // Si no hay sesión válida, muestra el login y oculta la aplicación principal
  if (!sesionValida && loginSection && mainApp) {
    loginSection.classList.remove('is-hidden');
    mainApp.classList.add('is-hidden');
  }

  // Permite ejecutar la búsqueda presionando la tecla Enter en el campo de búsqueda
  const inputBuscar = document.getElementById('input-buscar');
  if (inputBuscar) {
    inputBuscar.addEventListener('keyup', (event) => {
      if (event.key === 'Enter') realizarBusqueda();
    });
  }

  // Impide seleccionar fechas anteriores al día de hoy en el campo de fecha del evento
  const inputFecha = document.getElementById('fecha-evento');
  if (inputFecha) {
    inputFecha.min = new Date().toISOString().split('T')[0];
  }
});