<?php
session_start();
require_once 'conexion.php';

// Opcional: Validar que el usuario sea administrador por seguridad
// (Puedes adaptar esto según cómo manejes tu sesión en el backend)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración - Salon_Eventos_SyL</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="has-background-light">

  <!-- NAVBAR DE ADMINISTRACIÓN -->
  <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item has-text-weight-bold has-text-warning" href="index.html">
          <i class="fas fa-star mr-2"></i> Panel Admin - Salon_Eventos_SyL
        </a>
      </div>
      <div class="navbar-end">
        <div class="navbar-item">
          <a href="index.html" class="button is-light is-small">Volver al Sitio</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- CONTENIDO PRINCIPAL -->
  <section class="section">
    <div class="container">
      <h1 class="title is-3 has-text-centered mb-5">Gestión y ABM del Sistema</h1>

      <!-- PESTAÑAS DE NAVEGACIÓN DE ABM -->
      <div class="tabs is-boxed is-centered" id="admin-tabs">
        <ul>
          <li class="is-active" data-target="abm-salones"><a><i class="fas fa-door-open mr-2"></i> Salones</a></li>
          <li data-target="abm-servicios"><a><i class="fas fa-concierge-bell mr-2"></i> Servicios</a></li>
          <li data-target="abm-personal"><a><i class="fas fa-users-cog mr-2"></i> Personal</a></li>
        </ul>
      </div>

      <!-- SECCIÓN 1: ABM SALONES -->
      <div id="abm-salones" class="abm-content box">
        <h2 class="title is-4 has-text-warning-dark">Administrar Salones</h2>
        <p class="subtitle is-6">Aquí podrás dar de alta, modificar precios o capacidad, y gestionar los salones.</p>
        <!-- Tabla o formulario de Salones irán aquí -->
        <div class="notification is-info is-light">Próximamente: Tabla interactiva de Salones con conexión a la BD.</div>
      </div>

      <!-- SECCIÓN 2: ABM SERVICIOS (Oculto por defecto) -->
      <div id="abm-servicios" class="abm-content box is-hidden">
        <h2 class="title is-4 has-text-warning-dark">Administrar Servicios Adicionales</h2>
        <p class="subtitle is-6">Controlá los precios de catering, DJ, decoración y más.</p>
        <div class="notification is-info is-light">Próximamente: Tabla interactiva de Servicios.</div>
      </div>

      <!-- SECCIÓN 3: ABM PERSONAL (Oculto por defecto) -->
      <div id="abm-personal" class="abm-content box is-hidden">
        <h2 class="title is-4 has-text-warning-dark">Administrar Personal</h2>
        <p class="subtitle is-6">Gestioná los empleados y colaboradores asignados a los eventos.</p>
        <div class="notification is-info is-light">Próximamente: Tabla interactiva de Personal.</div>
      </div>

    </div>
  </section>

  <!-- SCRIPT PARA CAMBIAR ENTRE PESTAÑAS DEL PANEL -->
  <script>
    document.querySelectorAll('#admin-tabs li').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('#admin-tabs li').forEach(item => item.classList.remove('is-active'));
        this.classList.add('is-active');

        const target = this.getAttribute('data-target');
        document.querySelectorAll('.abm-content').forEach(content => {
          content.classList.add('is-hidden');
        });
        document.getElementById(target).classList.remove('is-hidden');
      });
    });
  </script>
</body>
</html>