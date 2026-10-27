// ================= SISTEMA DE PESTAÑAS (LOGIN / REGISTRO) =================
document.querySelectorAll('#auth-tabs li').forEach(item => {
  item.addEventListener('click', function () {
    document.querySelectorAll('#auth-tabs li').forEach(li => li.classList.remove('is-active'));
    this.classList.add('is-active');

    const tab = this.getAttribute('data-tab');
    if (tab === 'login') {
      document.getElementById('form-login').classList.remove('is-hidden');
      document.getElementById('form-register').classList.add('is-hidden');
    } else {
      document.getElementById('form-register').classList.remove('is-hidden');
      document.getElementById('form-login').classList.add('is-hidden');
    }
  });
});

// ================= LÓGICA DE INGRESO (LOGIN) =================
function manejarLogin(event) {
  event.preventDefault();

  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value.trim();

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

        // Ocultar sección de login y mostrar app principal
        document.getElementById('login-section').classList.add('is-hidden');
        document.getElementById('main-app').classList.remove('is-hidden');

        // Validar si es Administrador (según contraseña maestra o rol)
        if (password === 'admin123') {
          document.getElementById('user-badge').textContent = 'Administrador (Admin)';
          document.getElementById('user-badge').className = 'tag is-danger is-medium mr-3';
          document.getElementById('admin-panel').classList.remove('is-hidden');
        } else {
          document.getElementById('user-badge').textContent = 'Cliente: ' + data.nombre;
          document.getElementById('user-badge').className = 'tag is-success is-medium mr-3';
          document.getElementById('admin-panel').classList.add('is-hidden');
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
        document.querySelector('#auth-tabs li[data-tab="login"]').click();
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
  document.getElementById('main-app').classList.add('is-hidden');
  document.getElementById('login-section').classList.remove('is-hidden');
  document.getElementById('login-password').value = '';
  document.getElementById('form-login').reset();
}

// ================= GALERÍA Y PRECIOS DE SALONES =================
function cambiarFoto(urlNueva, tarjetaClickeada) {
  const imgPrincipal = document.getElementById('foto-principal');
  imgPrincipal.style.opacity = '0.2';

  setTimeout(() => {
    imgPrincipal.src = urlNueva;
    imgPrincipal.style.opacity = '1';
  }, 200);

  document.querySelectorAll('.thumb-card').forEach(card => card.classList.remove('active'));
  tarjetaClickeada.classList.add('active');
}

function toggleCateringMenu() {
  const checkboxCatering = document.getElementById('check-catering');
  const contenedorMenu = document.getElementById('contenedor-menu-select');

  if (checkboxCatering.checked) {
    contenedorMenu.classList.remove('is-hidden');
  } else {
    contenedorMenu.classList.add('is-hidden');
    document.getElementById('opcion-menu').value = "";
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

// ================= ENVÍO DE RESERVA REAL =================
function enviarReservaReal(event, idSalon) {
  event.preventDefault();

  const fechaEvento = document.getElementById('fecha-evento').value;
  
  // Capturar los campos del formulario de forma precisa usando los inputs del evento
  const inputsForm = event.target.querySelectorAll('input');
  
  // Asignamos según el orden habitual del formulario de reserva del salón:
  // [0] es la fecha (input type="date")
  // [1] es el checkbox del DJ (o primer checkbox)
  // Busquemos los campos de texto recorriendo o por posición específica del formulario de contacto:
  // Ajustamos para capturar el nombre, email, dni y teléfono de los inputs de texto visibles:
  let nombre = "";
  let email = "";
  let dni = "";
  let telefono = "";

  // Buscamos dentro del formulario los inputs de tipo text, email y tel
  const inputNombre = event.target.querySelector('input[type="text"]');
  const inputEmail = event.target.querySelector('input[type="email"]');
  const inputDni = event.target.querySelectorAll('input[type="text"]')[1]; // o según corresponda
  const inputTel = event.target.querySelector('input[type="tel"]');

  // O una forma más sencilla y robusta: leer por orden si los inputs están estructurados
  // Tomamos los valores directamente de los campos del bloque 3 (Contacto del Solicitante):
  const camposTexto = event.target.querySelectorAll('.column.is-12 input, .column.is-6 input');
  
  // Imprimimos en consola para depurar si hace falta, o capturamos por índice seguro:
  // Supongamos que los inputs de texto del final son: [Nombre, DNI, Teléfono] y el email si lo agregamos.
  // Vamos a asegurar una captura limpia pasando los datos del formulario actual:
  const inputsVisibles = event.target.querySelectorAll('input');
  
  // Usemos un selector directo por clase o posición en el bloque de contacto:
  const formBox = event.target;
  
  // Captura segura buscando los inputs de texto dentro del form
  const txtNombre = formBox.querySelector('input[placeholder*="Juan Pérez"]');
  const txtEmail = formBox.querySelector('input[type="email"]');
  const txtDni = formBox.querySelector('input[placeholder*="Sin puntos"]');
  const txtTel = formBox.querySelector('input[type="tel"]');

  const datosReserva = {
    id_salon: idSalon,
    fecha_evento: fechaEvento,
    turno: "Noche",
    monto_total: parseInt(document.getElementById('precio-final').textContent.replace('$', '').replace(/\./g, ''), 10) || 180000,
    nombre: txtNombre ? txtNombre.value : "Cliente Web",
    dni: txtDni ? txtDni.value : "Sin DNI",
    telefono: txtTel ? txtTel.value : "Sin Teléfono",
    email: txtEmail ? txtEmail.value : "cliente@test.com"
  };

  fetch('../Backend/procesar_reserva.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datosReserva)
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Hubo un error al procesar la reserva con el servidor.');
    });
}

// ================= BUSCADOR DE SALONES =================
const salones = [
  { id: 1, nombre: "Salon Imperial", capacidad: 350, url: "SALON1.html", descripcion: "Espacio elegante para grandes eventos y casamientos." },
  { id: 2, nombre: "Casita de fiesta", capacidad: 70, url: "SALON2.html", descripcion: "Espacio con máquinas arcade, ping pong y pool para cumpleaños." },
  { id: 3, nombre: "Salon Quincho", capacidad: 150, url: "SALON3.html", descripcion: "Quincho campestre ideal para asados y eventos al aire libre." }
];

function realizarBusqueda() {
  const input = document.getElementById('input-buscar').value.trim();
  const contenedor = document.getElementById('resultados-busqueda');

  if (!contenedor) return;
  contenedor.innerHTML = '';

  if (!input) {
    contenedor.innerHTML = '<div class="column is-12"><p class="has-text-warning">Por favor, ingresá un nombre o número de personas.</p></div>';
    return;
  }

  let resultados = [];
  const esNumero = !isNaN(input) && input !== '';

  if (esNumero) {
    const cantidadInvitados = parseInt(input, 10);
    resultados = salones.filter(salon => salon.capacidad >= cantidadInvitados);
  } else {
    resultados = salones.filter(salon => salon.nombre.toLowerCase().includes(input.toLowerCase()));
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

// Configuración inicial de eventos al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
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