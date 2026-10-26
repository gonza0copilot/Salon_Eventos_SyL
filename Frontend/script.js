// Sistema de Pestañas del Login / Registro
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

// Lógica de Ingreso (Login)
function manejarLogin(event) {
  event.preventDefault();
  const pass = document.getElementById('login-password').value;
  const usuarioInput = document.getElementById('login-email').value;

  // Ocultar pantalla de login y mostrar la app principal
  document.getElementById('login-section').classList.add('is-hidden');
  document.getElementById('main-app').classList.remove('is-hidden');

  // Validar si es Administrador (si la contraseña es 'admin123')
  if (pass === 'admin123') {
    document.getElementById('user-badge').textContent = 'Administrador (Admin)';
    document.getElementById('user-badge').className = 'tag is-danger is-medium mr-3';
    document.getElementById('admin-panel').classList.remove('is-hidden');
  } else {
    document.getElementById('user-badge').textContent = 'Cliente: ' + usuarioInput;
    document.getElementById('user-badge').className = 'tag is-success is-medium mr-3';
    document.getElementById('admin-panel').classList.add('is-hidden');
  }
}



// Lógica de Registro conectada a la Base de Datos PHP
function manejarRegistro(event) {
  event.preventDefault();

  // Capturar los inputs del formulario de registro en el index.html

  const formRegister = document.getElementById('form-register');
  const inputs = formRegister.querySelectorAll('input');

  const nombre = inputs[0].value;
  const email = inputs[1].value;
  const password = inputs[2].value;

  const datosCliente = { nombre, email, password };

  // Enviar datos al backend PHP mediante Fetch
  fetch('../Backend/registro_usuarios.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(datosCliente)
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        // Cambiar automáticamente a la pestaña de login
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



// Cerrar Sesión
function cerrarSesion() {
  document.getElementById('main-app').classList.add('is-hidden');
  document.getElementById('login-section').classList.remove('is-hidden');
  document.getElementById('login-password').value = '';
}

// Búsqueda simulada
function realizarBusqueda() {
  const query = document.getElementById('input-buscar').value;
  if (query.trim() !== "") {
    alert("Buscando resultados para: " + query);
  } else {
    alert("Por favor ingresa un nombre o capacidad para buscar.");
  }
}




// Cambiar foto de la galería horizontal
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

// Mostrar u ocultar el menú desplegable de catering según el checkbox
function toggleCateringMenu() {
  const checkboxCatering = document.getElementById('check-catering');
  const contenedorMenu = document.getElementById('contenedor-menu-select');

  if (checkboxCatering.checked) {
    contenedorMenu.classList.remove('is-hidden');
  } else {
    contenedorMenu.classList.add('is-hidden');
    document.getElementById('opcion-menu').value = ""; // Resetea la selección si lo desmarca
  }
}



const salones = [
  {
    id: 1,
    nombre: "Salon Imperial",
    capacidad: 350,
    url: "SALON1.html",
    descripcion: "Espacio elegante para grandes eventos y casamientos."
  },
  {
    id: 2,
    nombre: "Casita de fiesta",
    capacidad: 70,
    url: "SALON2.html",
    descripcion: "Espacio con máquinas arcade, ping pong y pool para cumpleaños."
  },
  {
    id: 3,
    nombre: "Salon Quincho",
    capacidad: 150,
    url: "SALON3.html",
    descripcion: "Quincho campestre ideal para asados y eventos al aire libre."
  }
];





// filtro de busqueda
function realizarBusqueda() {
  const input = document.getElementById('input-buscar').value.trim();
  const contenedor = document.getElementById('resultados-busqueda');

  if (!contenedor) return;

  // Limpiar resultados anteriores
  contenedor.innerHTML = '';

  if (!input) {
    contenedor.innerHTML = '<div class="column is-12"><p class="has-text-warning">Por favor, ingresá un nombre o número de personas.</p></div>';
    return;
  }

  let resultados = [];
  const esNumero = !isNaN(input) && input !== '';

  if (esNumero) {
    const cantidadInvitados = parseInt(input, 10);
    // Filtrar salones con capacidad mayor o igual a la cantidad requerida
    resultados = salones.filter(salon => salon.capacidad >= cantidadInvitados);
  } else {
    // Filtrar por coincidencia en el nombre (sin importar mayúsculas/minúsculas)
    resultados = salones.filter(salon =>
      salon.nombre.toLowerCase().includes(input.toLowerCase())
    );
  }

  // Renderizar resultados
  if (resultados.length === 0) {
    contenedor.innerHTML = `
      <div class="column is-12">
        <div class="notification is-warning is-light">
          No se encontraron salones que coincidan con la búsqueda.
        </div>
      </div>`;
    return;
  }

  resultados.forEach(salon => {
    const cardHTML = `
      <div class="column is-4">
        <div class="card">
          <div class="card-content">
            <p class="title is-5">${salon.nombre}</p>
            <p class="subtitle is-6 text-muted">
              <i class="fas fa-users mr-1"></i> Capacidad: Hasta ${salon.capacidad} personas
            </p>
            <p class="is-size-7 mb-4">${salon.descripcion}</p>
            <a href="${salon.url}" class="button is-warning is-small is-fullwidth has-text-weight-bold">
              Ver Salón
            </a>
          </div>
        </div>
      </div>
    `;
    contenedor.innerHTML += cardHTML;
  });
}

// Opcional: Permitir presionar "Enter" en el input para buscar
document.addEventListener('DOMContentLoaded', () => {
  const inputBuscar = document.getElementById('input-buscar');
  if (inputBuscar) {
    inputBuscar.addEventListener('keyup', (event) => {
      if (event.key === 'Enter') {
        realizarBusqueda();
      }
    });
  }
});

// Restringir el calendario para que no permita seleccionar fechas pasadas
document.addEventListener('DOMContentLoaded', () => {
  const inputFecha = document.getElementById('fecha-evento');
  if (inputFecha) {
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.min = hoy;
  }
});


function calcularTotal() {
  // Leer dinámicamente el precio base desde el HTML del salón actual
  const elementoBase = document.getElementById('precio-base');
  let precioBase = 180000; // Valor por defecto de respaldo

  if (elementoBase) {
    // Limpiar el texto (ej: "$250.000" se convierte en el número 250000)
    const textoLimpio = elementoBase.textContent.replace('$', '').replace(/\./g, '').trim();
    precioBase = parseInt(textoLimpio, 10) || 180000;
  }

  let total = precioBase;

  // Buscar todos los checkboxes de servicios adicionales que estén marcados
  const checkboxes = document.querySelectorAll('.servicio-check:checked');

  checkboxes.forEach(checkbox => {
    total += parseInt(checkbox.getAttribute('data-precio')) || 0;
  });

  // Mostrar el precio final formateado con puntos (ej: $335.000)
  const elementoPrecio = document.getElementById('precio-final');
  if (elementoPrecio) {
    elementoPrecio.textContent = '$' + total.toLocaleString('es-AR');
  }
}