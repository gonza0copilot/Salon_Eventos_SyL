<?php
session_start();
require_once 'conexion.php';

$mensaje = "";

// 1. CREAR NUEVO SALÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_salon') {
    $nombre = trim($_POST['nombre']);
    $capacidad = intval($_POST['capacidad_maxima']);
    $direccion = trim($_POST['direccion']);
    $precio_base = floatval($_POST['precio_base']);
    $tipo_salon = trim($_POST['tipo_salon']);

    try {
        $stmt = $pdo->prepare("INSERT INTO salon (nombre, capacidad_maxima, direccion, precio_base, tipo_salon, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
        $stmt->execute([$nombre, $capacidad, $direccion, $precio_base, $tipo_salon]);
        $mensaje = "¡Salón agregado con éxito!";
    } catch (PDOException $e) {
        $mensaje = "Error al agregar el salón: " . $e->getMessage();
    }
}

// 2. MODIFICAR SALÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'modificar_salon') {
    $id_salon = intval($_POST['id_salon']);
    $nombre = trim($_POST['nombre']);
    $capacidad = intval($_POST['capacidad_maxima']);
    $direccion = trim($_POST['direccion']);
    $precio_base = floatval($_POST['precio_base']);
    $tipo_salon = trim($_POST['tipo_salon']);

    try {
        $stmt = $pdo->prepare("UPDATE salon SET nombre = ?, capacidad_maxima = ?, direccion = ?, precio_base = ?, tipo_salon = ? WHERE id_salon = ?");
        $stmt->execute([$nombre, $capacidad, $direccion, $precio_base, $tipo_salon, $id_salon]);
        $mensaje = "¡Salón modificado con éxito!";
    } catch (PDOException $e) {
        $mensaje = "Error al modificar el salón: " . $e->getMessage();
    }
}

// 3. ELIMINAR SALÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_salon') {
    $id_salon = intval($_POST['id_salon']);
    try {
        $stmt = $pdo->prepare("DELETE FROM salon WHERE id_salon = ?");
        $stmt->execute([$id_salon]);
        $mensaje = "¡Salón eliminado con éxito!";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el salón (es posible que tenga reservas asociadas).";
    }
}

// Obtener datos del salón a editar (si se hizo clic en Editar)
$salonEditar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmtEdit = $pdo->prepare("SELECT * FROM salon WHERE id_salon = ?");
    $stmtEdit->execute([$id_editar]);
    $salonEditar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

// Obtener la lista actualizada de salones
try {
    $stmtSalones = $pdo->query("SELECT * FROM salon");
    $salones = $stmtSalones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $salones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración - Salon_Eventos_SyL</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../frontend/styles.css">
</head>
<body class="has-background-light">

  <!-- NAVBAR DE ADMINISTRACIÓN -->
  <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item has-text-weight-bold has-text-warning" href="../frontend/index.html">
          <i class="fas fa-star mr-2"></i> Panel Admin - Salon_Eventos_SyL
        </a>
      </div>
      <div class="navbar-end">
        <div class="navbar-item">
          <a href="../frontend/index.html" class="button is-light is-small">Volver al Sitio</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- CONTENIDO PRINCIPAL -->
  <section class="section">
    <div class="container">
      <h1 class="title is-3 has-text-centered mb-5">Gestión y ABM del Sistema</h1>

      <?php if (!empty($mensaje)): ?>
        <div class="notification is-success is-light"><?php echo $mensaje; ?></div>
      <?php endif; ?>

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
        <p class="subtitle is-6">Listado de espacios y control de altas, modificaciones y bajas.</p>

        <!-- Formulario Dinámico (Crear o Modificar) -->
        <form method="POST" class="box mb-5 has-background-white-ter">
          <input type="hidden" name="accion" value="<?php echo $salonEditar ? 'modificar_salon' : 'crear_salon'; ?>">
          <?php if ($salonEditar): ?>
            <input type="hidden" name="id_salon" value="<?php echo $salonEditar['id_salon']; ?>">
            <h3 class="title is-5 mb-3 has-text-info">Modificar Salón (ID: <?php echo $salonEditar['id_salon']; ?>)</h3>
          <?php else: ?>
            <h3 class="title is-5 mb-3">Agregar Nuevo Salón</h3>
          <?php endif; ?>

          <div class="columns is-multiline">
            <div class="column is-4">
              <div class="field">
                <label class="label">Nombre del Salón</label>
                <input class="input" type="text" name="nombre" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['nombre']) : ''; ?>" placeholder="Ej: Salón Cristal">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Capacidad Máxima</label>
                <input class="input" type="number" name="capacidad_maxima" required value="<?php echo $salonEditar ? $salonEditar['capacidad_maxima'] : ''; ?>" placeholder="Ej: 200">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Tipo de Salón</label>
                <input class="input" type="text" name="tipo_salon" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['tipo_salon']) : ''; ?>" placeholder="Ej: Principal / Campestre">
              </div>
            </div>
            <div class="column is-6">
              <div class="field">
                <label class="label">Dirección</label>
                <input class="input" type="text" name="direccion" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['direccion']) : ''; ?>" placeholder="Ej: Av. Alem 500">
              </div>
            </div>
            <div class="column is-6">
              <div class="field">
                <label class="label">Precio Base ($)</label>
                <input class="input" type="number" step="0.01" name="precio_base" required value="<?php echo $salonEditar ? $salonEditar['precio_base'] : ''; ?>" placeholder="Ej: 180000">
              </div>
            </div>
            <div class="column is-12">
              <button type="submit" class="button <?php echo $salonEditar ? 'is-info' : 'is-warning'; ?> has-text-weight-bold">
                <?php echo $salonEditar ? 'Guardar Cambios' : 'Guardar Salón'; ?>
              </button>
              <?php if ($salonEditar): ?>
                <a href="admin.php" class="button is-light ml-2">Cancelar</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <!-- Tabla de Salones Registrados -->
        <h3 class="title is-5 mt-5">Salones Existentes</h3>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Capacidad</th>
                <th>Dirección</th>
                <th>Precio Base</th>
                <th>Tipo</th>
                <th class="has-text-centered">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($salones) > 0): ?>
                <?php foreach ($salones as $salon): ?>
                  <tr>
                    <td><?php echo $salon['id_salon']; ?></td>
                    <td><strong><?php echo htmlspecialchars($salon['nombre']); ?></strong></td>
                    <td><?php echo $salon['capacidad_maxima']; ?> pers.</td>
                    <td><?php echo htmlspecialchars($salon['direccion']); ?></td>
                    <td>$<?php echo number_format($salon['precio_base'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($salon['tipo_salon']); ?></td>
                    <td class="has-text-centered">
                      <!-- Botón Editar -->
                      <a href="admin.php?editar=<?php echo $salon['id_salon']; ?>" class="button is-small is-info mr-1" title="Modificar">
                        <i class="fas fa-edit"></i>
                      </a>
                      <!-- Botón Eliminar -->
                      <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este salón?');">
                        <input type="hidden" name="accion" value="eliminar_salon">
                        <input type="hidden" name="id_salon" value="<?php echo $salon['id_salon']; ?>">
                        <button type="submit" class="button is-small is-danger" title="Eliminar">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="has-text-centered">No hay salones registrados.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECCIÓN 2: ABM SERVICIOS (Próximamente) -->
      <div id="abm-servicios" class="abm-content box is-hidden">
        <h2 class="title is-4 has-text-warning-dark">Administrar Servicios Adicionales</h2>
        <p class="subtitle is-6">Controlá los precios de catering, DJ, decoración y más.</p>
        <div class="notification is-info is-light">Próximamente conectaremos este ABM.</div>
      </div>

      <!-- SECCIÓN 3: ABM PERSONAL (Próximamente) -->
      <div id="abm-personal" class="abm-content box is-hidden">
        <h2 class="title is-4 has-text-warning-dark">Administrar Personal</h2>
        <p class="subtitle is-6">Gestioná los empleados y colaboradores asignados a los eventos.</p>
        <div class="notification is-info is-light">Próximamente conectaremos este ABM.</div>
      </div>

    </div>
  </section>

  <!-- SCRIPT PARA CAMBIAR ENTRE PESTAÑAS -->
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