// Sistema de Pestañas del Login / Registro
document.querySelectorAll('#auth-tabs li').forEach(item => {
  item.addEventListener('click', function() {
    document.querySelectorAll('#auth-tabs li').forEach(li => li.classList.remove('is-active'));
    this.classList.add('is-active');
    
    const tab = this.getAttribute('data-tab');
    if(tab === 'login') {
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

// Lógica de Registro simulado
function manejarRegistro(event) {
  event.preventDefault();
  alert('¡Cuenta creada con éxito! Ahora podés iniciar sesión.');
  document.querySelector('#auth-tabs li[data-tab="login"]').click();
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
  if(query.trim() !== "") {
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