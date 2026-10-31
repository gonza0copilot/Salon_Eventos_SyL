// ================= SISTEMA DE PESTAÑAS (LOGIN / REGISTRO) =================
const authTabs = document.querySelectorAll('#auth-tabs li');
if (authTabs.length > 0) {
  authTabs.forEach(item => {
    item.addEventListener('click', function () {
      authTabs.forEach(li => li.classList.remove('is-active'));
      this.classList.add('is-active');

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
function manejarLogin(event) {
  event.preventDefault();

  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');
  if (!emailInput || !passwordInput) return;

  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  const datosLogin = { email, password };

  fetch('../Backend/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosLogin)
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);

        const esAdmin = (password === 'admin123');
        const datosSesion = {
          nombre: data.nombre,
          esAdmin: esAdmin,
          timestamp: new Date().getTime()
        };
        localStorage.setItem('sesion_usuario', JSON.stringify(datosSesion));

        const loginSection = document.getElementById('login-section');
        const mainApp = document.getElementById('main-app');
        if (loginSection) loginSection.classList.add('is-hidden');
        if (mainApp) mainApp.classList.remove('is-hidden');

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
function manejarRegistro(event) {
  event.preventDefault();

  const formRegister = document.getElementById('form-register');
  if (!formRegister) return;
  const inputs = formRegister.querySelectorAll('input');

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
function cerrarSesion() {
  localStorage.removeItem('sesion_usuario');
  const loginSection = document.getElementById('login-section');
  const mainApp = document.getElementById('main-app');
  const loginPassword = document.getElementById('login-password');
  const formLogin = document.getElementById('form-login');

  if (loginSection) loginSection.classList.remove('is-hidden');
  if (mainApp) mainApp.classList.add('is-hidden');
  if (loginPassword) loginPassword.value = '';
  if (formLogin) formLogin.reset();
}

// ================= GALERÍA Y PRECIOS DE SALONES =================
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

function calcularTotal() {
  const elementoBase = document.getElementById('precio-base');
  let precioBase = 180000;

  if (elementoBase) {
    const textoLimpio = elementoBase.textContent.replace('$', '').replace(/\./g, '').trim();
    precioBase = parseInt(textoLimpio, 10) || 180000;
  }

  let total = precioBase;
  const checkboxes = document.querySelectorAll('.servicio-check:checked');

  checkboxes.forEach(checkbox => {
    total += parseInt(checkbox.getAttribute('data-precio')) || 0;
  });

  const elementoPrecio = document.getElementById('precio-final');
  if (elementoPrecio) {
    elementoPrecio.textContent = '$' + total.toLocaleString('es-AR');
  }
}


// ================= ENVÍO DE RESERVA CON MÉTODO DE PAGO =================
function enviarReservaConMetodo(event, idSalon, metodoPago) {
  event.preventDefault();

  // Usamos event.currentTarget para asegurar que capturemos el botón sin importar si hicieron clic en el texto o en el icono
  const boton = event.currentTarget;
  const formBox = boton.closest('form');

  const fechaInput = document.getElementById('fecha-evento');
  const fechaEvento = fechaInput ? fechaInput.value : '';
  
  // Captura segura de los campos por ID o selectores del formulario
  const txtNombre = formBox ? formBox.querySelector('input[type="text"]') : null;
  const txtDni = document.getElementById('dni-cliente');
  const txtTel = document.getElementById('telefono-cliente');
  const elementoPrecio = document.getElementById('precio-final');

  let montoTotal = 180000;
  if (elementoPrecio) {
    montoTotal = parseInt(elementoPrecio.textContent.replace('$', '').replace(/\./g, ''), 10) || 180000;
  }

  // Validación rápida en el cliente antes de enviar
  if (!fechaEvento || !txtDni || !txtDni.value || !txtTel || !txtTel.value) {
    alert("Por favor completá la fecha del evento, DNI y teléfono obligatorios.");
    return;
  }

  const datosReserva = {
    id_salon: idSalon,
    fecha_evento: fechaEvento,
    turno: "Noche", // O el valor que corresponda según tu selector de turno
    monto_total: montoTotal,
    metodo_pago: metodoPago, // 'efectivo' o 'transferencia'
    nombre: txtNombre ? txtNombre.value : "Cliente Web",
    dni: txtDni.value,
    telefono: txtTel.value,
    email: "cliente@evaluacion.com" // Email por defecto para identificar la solicitud
  };

  // Petición al backend
  fetch('../Backend/procesar_reserva.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosReserva)
  })
    .then(response => response.json())
    .then(data => {
      // Muestra el cartelito de éxito o error devuelto por PHP
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
const salones = [
  { id: 1, nombre: "Salon Imperial", capacidad: 350, url: "SALON1.html", descripcion: "Espacio elegante para grandes eventos y casamientos." },
  { id: 2, nombre: "Casita de fiesta", capacidad: 70, url: "SALON2.html", descripcion: "Espacio con máquinas arcade, ping pong y pool para cumpleaños." },
  { id: 3, nombre: "Salon Quincho", capacidad: 150, url: "SALON3.html", descripcion: "Quincho campestre ideal para asados y eventos al aire libre." }
];

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

  // Verificación de sesión temporal (3 minutos)
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

    const userBadge = document.getElementById('user-badge');
    const adminPanel = document.getElementById('admin-panel');

    if (userBadge) {
      if (sesion.esAdmin) {
        userBadge.textContent = 'Administrador (Admin)';
        userBadge.className = 'tag is-danger is-medium mr-3';
        if (adminPanel) adminPanel.classList.remove('is-hidden');
      } else {
        // AQUÍ ESTABA EL DETALLE: Asignamos el nombre guardado en la sesión
        userBadge.textContent = 'Cliente: ' + sesion.nombre;
        userBadge.className = 'tag is-success is-medium mr-3';
        if (adminPanel) adminPanel.classList.add('is-hidden');
      }
    }
  } else {
    localStorage.removeItem('sesion_usuario');
  }
}

  // Si NO hay sesión válida, mostrar el login y ocultar la app principal
  if (!sesionValida && loginSection && mainApp) {
    loginSection.classList.remove('is-hidden');
    mainApp.classList.add('is-hidden');
  }

  const inputBuscar = document.getElementById('input-buscar');
  if (inputBuscar) {
    inputBuscar.addEventListener('keyup', (event) => {
      if (event.key === 'Enter') realizarBusqueda();
    });
  }

  const inputFecha = document.getElementById('fecha-evento');
  if (inputFecha) {
    inputFecha.min = new Date().toISOString().split('T')[0];
  }
});