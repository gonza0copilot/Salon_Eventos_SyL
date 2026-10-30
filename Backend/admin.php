<?php
session_start();
require_once 'conexion.php';

$mensaje = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'creado') $mensaje = "¡Salón agregado con éxito!";
    if ($_GET['status'] == 'modificado') $mensaje = "¡Salón modificado con éxito!";
    if ($_GET['status'] == 'eliminado') $mensaje = "¡Salón eliminado con éxito!";
    if ($_GET['status'] == 'servicio_creado') $mensaje = "¡Servicio agregado con éxito!";
    if ($_GET['status'] == 'servicio_modificado') $mensaje = "¡Servicio modificado con éxito!";
    if ($_GET['status'] == 'servicio_eliminado') $mensaje = "¡Servicio eliminado con éxito!";
    if ($_GET['status'] == 'personal_creado') $mensaje = "¡Personal agregado con éxito!";
    if ($_GET['status'] == 'personal_modificado') $mensaje = "¡Personal modificado con éxito!";
    if ($_GET['status'] == 'personal_eliminado') $mensaje = "¡Personal eliminado con éxito!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // ================= SALONES (ABM) =================
    if ($accion === 'crear_salon') {
        $nombre = trim($_POST['nombre']);
        $capacidad = intval($_POST['capacidad_maxima']);
        $direccion = trim($_POST['direccion']);
        $precio_base = floatval($_POST['precio_base']);
        $tipo_salon = trim($_POST['tipo_salon']);

        try {
            $stmt = $pdo->prepare("INSERT INTO salon (nombre, capacidad_maxima, direccion, precio_base, tipo_salon, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
            $stmt->execute([$nombre, $capacidad, $direccion, $precio_base, $tipo_salon]);
            header("Location: admin.php?status=creado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al agregar el salón: " . $e->getMessage();
        }
    }

    if ($accion === 'modificar_salon') {
        $id_salon = intval($_POST['id_salon']);
        $nombre = trim($_POST['nombre']);
        $capacidad = intval($_POST['capacidad_maxima']);
        $direccion = trim($_POST['direccion']);
        $precio_base = floatval($_POST['precio_base']);
        $tipo_salon = trim($_POST['tipo_salon']);

        try {
            $stmt = $pdo->prepare("UPDATE salon SET nombre = ?, capacidad_maxima = ?, direccion = ?, precio_base = ?, tipo_salon = ? WHERE id_salon = ?");
            $stmt->execute([$nombre, $capacidad, $direccion, $precio_base, $tipo_salon, $id_salon]);
            header("Location: admin.php?status=modificado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al modificar el salón: " . $e->getMessage();
        }
    }

    if ($accion === 'eliminar_salon') {
        $id_salon = intval($_POST['id_salon']);
        try {
            $stmt = $pdo->prepare("DELETE FROM salon WHERE id_salon = ?");
            $stmt->execute([$id_salon]);
            header("Location: admin.php?status=eliminado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el salón (es posible que tenga reservas asociadas).";
        }
    }

    // ================= SERVICIOS (ABM) =================
    if ($accion === 'crear_servicio') {
        $nombre = trim($_POST['nombre_servicio']);
        $descripcion = trim($_POST['descripcion']);
        $precio_base = floatval($_POST['precio_base']);
        $categoria = trim($_POST['categoria']);

        try {
            $stmt = $pdo->prepare("INSERT INTO servicio (nombre_servicio, descripcion, precio_base, categoria) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio_base, $categoria]);
            header("Location: admin.php?status=servicio_creado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al agregar el servicio: " . $e->getMessage();
        }
    }

    if ($accion === 'modificar_servicio') {
        $id_servicio = intval($_POST['id_servicio']);
        $nombre = trim($_POST['nombre_servicio']);
        $descripcion = trim($_POST['descripcion']);
        $precio_base = floatval($_POST['precio_base']);
        $categoria = trim($_POST['categoria']);

        try {
            $stmt = $pdo->prepare("UPDATE servicio SET nombre_servicio = ?, descripcion = ?, precio_base = ?, categoria = ? WHERE id_servicio = ?");
            $stmt->execute([$nombre, $descripcion, $precio_base, $categoria, $id_servicio]);
            header("Location: admin.php?status=servicio_modificado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al modificar el servicio: " . $e->getMessage();
        }
    }

    if ($accion === 'eliminar_servicio') {
        $id_servicio = intval($_POST['id_servicio']);
        try {
            $stmt = $pdo->prepare("DELETE FROM servicio WHERE id_servicio = ?");
            $stmt->execute([$id_servicio]);
            header("Location: admin.php?status=servicio_eliminado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el servicio.";
        }
    }

    // ================= PERSONAL (ABM) =================
    if ($accion === 'crear_personal') {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $dni_cuit = trim($_POST['dni_cuit']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $tipo_personal = trim($_POST['tipo_personal']);

        try {
            $stmt = $pdo->prepare("INSERT INTO personal (nombre, apellido, dni_cuit, telefono, email, tipo_personal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $dni_cuit, $telefono, $email, $tipo_personal]);
            header("Location: admin.php?status=personal_creado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al agregar el personal: " . $e->getMessage();
        }
    }

    if ($accion === 'modificar_personal') {
        $id_personal = intval($_POST['id_personal']);
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $dni_cuit = trim($_POST['dni_cuit']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $tipo_personal = trim($_POST['tipo_personal']);

        try {
            $stmt = $pdo->prepare("UPDATE personal SET nombre = ?, apellido = ?, dni_cuit = ?, telefono = ?, email = ?, tipo_personal = ? WHERE id_personal = ?");
            $stmt->execute([$nombre, $apellido, $dni_cuit, $telefono, $email, $tipo_personal, $id_personal]);
            header("Location: admin.php?status=personal_modificado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al modificar el personal: " . $e->getMessage();
        }
    }

    if ($accion === 'eliminar_personal') {
        $id_personal = intval($_POST['id_personal']);
        try {
            $stmt = $pdo->prepare("DELETE FROM personal WHERE id_personal = ?");
            $stmt->execute([$id_personal]);
            header("Location: admin.php?status=personal_eliminado");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el personal.";
        }
    }
}

// Ediciones
$salonEditar = null;
if (isset($_GET['editar'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM salon WHERE id_salon = ?");
    $stmtEdit->execute([intval($_GET['editar'])]);
    $salonEditar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}

$servicioEditar = null;
if (isset($_GET['editar_servicio'])) {
    $stmtEditS = $pdo->prepare("SELECT * FROM servicio WHERE id_servicio = ?");
    $stmtEditS->execute([intval($_GET['editar_servicio'])]);
    $servicioEditar = $stmtEditS->fetch(PDO::FETCH_ASSOC);
}

$personalEditar = null;
if (isset($_GET['editar_personal'])) {
    $stmtEditP = $pdo->prepare("SELECT * FROM personal WHERE id_personal = ?");
    $stmtEditP->execute([intval($_GET['editar_personal'])]);
    $personalEditar = $stmtEditP->fetch(PDO::FETCH_ASSOC);
}

// Listas
$salones = $pdo->query("SELECT * FROM salon")->fetchAll(PDO::FETCH_ASSOC);
$servicios = $pdo->query("SELECT * FROM servicio")->fetchAll(PDO::FETCH_ASSOC);
$personalList = $pdo->query("SELECT * FROM personal")->fetchAll(PDO::FETCH_ASSOC);
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

      <!-- PESTAÑAS DE NAVEGACIÓN -->
      <div class="tabs is-boxed is-centered" id="admin-tabs">
        <ul>
          <li class="<?php echo ($servicioEditar || $personalEditar) ? '' : 'is-active'; ?>" data-target="abm-salones"><a><i class="fas fa-door-open mr-2"></i> Salones</a></li>
          <li class="<?php echo $servicioEditar ? 'is-active' : ''; ?>" data-target="abm-servicios"><a><i class="fas fa-concierge-bell mr-2"></i> Servicios</a></li>
          <li class="<?php echo $personalEditar ? 'is-active' : ''; ?>" data-target="abm-personal"><a><i class="fas fa-users-cog mr-2"></i> Personal</a></li>
        </ul>
      </div>

      <!-- SECCIÓN 1: ABM SALONES -->
      <div id="abm-salones" class="abm-content box <?php echo ($servicioEditar || $personalEditar) ? 'is-hidden' : ''; ?>">
        <h2 class="title is-4 has-text-warning-dark">Administrar Salones</h2> <br>
        <p class="subtitle is-6">Control de altas, modificaciones y bajas de espacios.</p>

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
                <input class="input" type="text" name="nombre" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['nombre']) : ''; ?>">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Capacidad Máxima</label>
                <input class="input" type="number" name="capacidad_maxima" required value="<?php echo $salonEditar ? $salonEditar['capacidad_maxima'] : ''; ?>">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Tipo de Salón</label>
                <input class="input" type="text" name="tipo_salon" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['tipo_salon']) : ''; ?>">
              </div>
            </div>
            <div class="column is-6">
              <div class="field">
                <label class="label">Dirección</label>
                <input class="input" type="text" name="direccion" required value="<?php echo $salonEditar ? htmlspecialchars($salonEditar['direccion']) : ''; ?>">
              </div>
            </div>
            <div class="column is-6">
              <div class="field">
                <label class="label">Precio Base ($)</label>
                <input class="input" type="number" step="0.01" name="precio_base" required value="<?php echo $salonEditar ? $salonEditar['precio_base'] : ''; ?>">
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
              <?php foreach ($salones as $salon): ?>
                <tr>
                  <td><?php echo $salon['id_salon']; ?></td>
                  <td><strong><?php echo htmlspecialchars($salon['nombre']); ?></strong></td>
                  <td><?php echo $salon['capacidad_maxima']; ?> pers.</td>
                  <td><?php echo htmlspecialchars($salon['direccion']); ?></td>
                  <td>$<?php echo number_format($salon['precio_base'], 2, ',', '.'); ?></td>
                  <td><?php echo htmlspecialchars($salon['tipo_salon']); ?></td>
                  <td class="has-text-centered">
                    <a href="admin.php?editar=<?php echo $salon['id_salon']; ?>" class="button is-small is-info mr-1" title="Modificar"><i class="fas fa-edit"></i></a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este salón?');">
                      <input type="hidden" name="accion" value="eliminar_salon">
                      <input type="hidden" name="id_salon" value="<?php echo $salon['id_salon']; ?>">
                      <button type="submit" class="button is-small is-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECCIÓN 2: ABM SERVICIOS -->
      <div id="abm-servicios" class="abm-content box <?php echo $servicioEditar ? '' : 'is-hidden'; ?>">
        <h2 class="title is-4 has-text-warning-dark">Administrar Servicios Adicionales</h2> <br>
        <p class="subtitle is-6">Controlá los precios y detalles de catering, DJ, decoración y más.</p>

        <form method="POST" class="box mb-5 has-background-white-ter">
          <input type="hidden" name="accion" value="<?php echo $servicioEditar ? 'modificar_servicio' : 'crear_servicio'; ?>">
          <?php if ($servicioEditar): ?>
            <input type="hidden" name="id_servicio" value="<?php echo $servicioEditar['id_servicio']; ?>">
            <h3 class="title is-5 mb-3 has-text-info">Modificar Servicio (ID: <?php echo $servicioEditar['id_servicio']; ?>)</h3>
          <?php else: ?>
            <h3 class="title is-5 mb-3">Agregar Nuevo Servicio</h3>
          <?php endif; ?>

          <div class="columns is-multiline">
            <div class="column is-4">
              <div class="field">
                <label class="label">Nombre del Servicio</label>
                <input class="input" type="text" name="nombre_servicio" required value="<?php echo $servicioEditar ? htmlspecialchars($servicioEditar['nombre_servicio']) : ''; ?>" placeholder="Ej: Barra Libre">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Categoría</label>
                <input class="input" type="text" name="categoria" required value="<?php echo $servicioEditar ? htmlspecialchars($servicioEditar['categoria']) : ''; ?>" placeholder="Ej: Gastronomía">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Precio Base ($)</label>
                <input class="input" type="number" step="0.01" name="precio_base" required value="<?php echo $servicioEditar ? $servicioEditar['precio_base'] : ''; ?>" placeholder="Ej: 50000">
              </div>
            </div>
            <div class="column is-12">
              <div class="field">
                <label class="label">Descripción</label>
                <textarea class="textarea" name="descripcion" placeholder="Detalle del servicio..."><?php echo $servicioEditar ? htmlspecialchars($servicioEditar['descripcion']) : ''; ?></textarea>
              </div>
            </div>
            <div class="column is-12">
              <button type="submit" class="button <?php echo $servicioEditar ? 'is-info' : 'is-warning'; ?> has-text-weight-bold">
                <?php echo $servicioEditar ? 'Guardar Cambios Servicio' : 'Guardar Servicio'; ?>
              </button>
              <?php if ($servicioEditar): ?>
                <a href="admin.php" class="button is-light ml-2">Cancelar</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <h3 class="title is-5 mt-5">Servicios Existentes</h3>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Descripción</th>
                <th>Precio Base</th>
                <th class="has-text-centered">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($servicios) > 0): ?>
                <?php foreach ($servicios as $serv): ?>
                  <tr>
                    <td><?php echo $serv['id_servicio']; ?></td>
                    <td><strong><?php echo htmlspecialchars($serv['nombre_servicio']); ?></strong></td>
                    <td><?php echo htmlspecialchars($serv['categoria']); ?></td>
                    <td><?php echo htmlspecialchars($serv['descripcion']); ?></td>
                    <td>$<?php echo number_format($serv['precio_base'], 2, ',', '.'); ?></td>
                    <td class="has-text-centered">
                      <a href="admin.php?editar_servicio=<?php echo $serv['id_servicio']; ?>" class="button is-small is-info mr-1" title="Modificar"><i class="fas fa-edit"></i></a>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este servicio?');">
                        <input type="hidden" name="accion" value="eliminar_servicio">
                        <input type="hidden" name="id_servicio" value="<?php echo $serv['id_servicio']; ?>">
                        <button type="submit" class="button is-small is-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="has-text-centered">No hay servicios registrados.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECCIÓN 3: ABM PERSONAL -->
      <div id="abm-personal" class="abm-content box <?php echo $personalEditar ? '' : 'is-hidden'; ?>">
        <h2 class="title is-4 has-text-warning-dark">Administrar Personal</h2> <br>
        <p class="subtitle is-6">Gestioná los empleados, mozos, técnicos y coordinadores del salón.</p>

        <form method="POST" class="box mb-5 has-background-white-ter">
          <input type="hidden" name="accion" value="<?php echo $personalEditar ? 'modificar_personal' : 'crear_personal'; ?>">
          <?php if ($personalEditar): ?>
            <input type="hidden" name="id_personal" value="<?php echo $personalEditar['id_personal']; ?>">
            <h3 class="title is-5 mb-3 has-text-info">Modificar Personal (ID: <?php echo $personalEditar['id_personal']; ?>)</h3>
          <?php else: ?>
            <h3 class="title is-5 mb-3">Agregar Nuevo Personal</h3>
          <?php endif; ?>

          <div class="columns is-multiline">
            <div class="column is-4">
              <div class="field">
                <label class="label">Nombre</label>
                <input class="input" type="text" name="nombre" required value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['nombre']) : ''; ?>" placeholder="Ej: Carlos">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Apellido</label>
                <input class="input" type="text" name="apellido" required value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['apellido']) : ''; ?>" placeholder="Ej: Gómez">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">DNI / CUIT</label>
                <input class="input" type="text" name="dni_cuit" required value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['dni_cuit']) : ''; ?>" placeholder="Ej: 20-33445566-9">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Teléfono</label>
                <input class="input" type="text" name="telefono" value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['telefono']) : ''; ?>" placeholder="Ej: 2914556677">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Email</label>
                <input class="input" type="email" name="email" value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['email']) : ''; ?>" placeholder="Ej: carlos@email.com">
              </div>
            </div>
            <div class="column is-4">
              <div class="field">
                <label class="label">Tipo / Rol de Personal</label>
                <input class="input" type="text" name="tipo_personal" value="<?php echo $personalEditar ? htmlspecialchars($personalEditar['tipo_personal']) : ''; ?>" placeholder="Ej: Parrillero / Animador">
              </div>
            </div>
            <div class="column is-12">
              <button type="submit" class="button <?php echo $personalEditar ? 'is-info' : 'is-warning'; ?> has-text-weight-bold">
                <?php echo $personalEditar ? 'Guardar Cambios Personal' : 'Guardar Personal'; ?>
              </button>
              <?php if ($personalEditar): ?>
                <a href="admin.php" class="button is-light ml-2">Cancelar</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <h3 class="title is-5 mt-5">Personal Registrado</h3>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre y Apellido</th>
                <th>DNI / CUIT</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Rol / Tipo</th>
                <th class="has-text-centered">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($personalList) > 0): ?>
                <?php foreach ($personalList as $p): ?>
                  <tr>
                    <td><?php echo $p['id_personal']; ?></td>
                    <td><strong><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['dni_cuit']); ?></td>
                    <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                    <td><span class="tag is-info is-light"><?php echo htmlspecialchars($p['tipo_personal']); ?></span></td>
                    <td class="has-text-centered">
                      <a href="admin.php?editar_personal=<?php echo $p['id_personal']; ?>" class="button is-small is-info mr-1" title="Modificar"><i class="fas fa-edit"></i></a>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este empleado?');">
                        <input type="hidden" name="accion" value="eliminar_personal">
                        <input type="hidden" name="id_personal" value="<?php echo $p['id_personal']; ?>">
                        <button type="submit" class="button is-small is-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="has-text-centered">No hay personal registrado.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <!-- SCRIPT CORREGIDO PARA CAMBIAR ENTRE PESTAÑAS -->
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