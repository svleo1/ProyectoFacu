<?php
require_once '../auth_admin.php';
require_once '../header.php';
require_once '../../includes/db.php';

// Función para verificar contraseña admin
function verificarAdminPass($pdo, $pass) {
    $sql = "SELECT password FROM usuarios WHERE rol='admin' LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    return password_verify($pass, $hash);
}

// Procesar formulario (crear, editar, eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';
    
    if (!verificarAdminPass($pdo, $admin_pass)) {
        $error = "Contraseña de administrador incorrecta.";
    } else {
        $pdo->beginTransaction();
        try {
            if ($accion === 'crear') {
                // Validar datos
                $email = $_POST['email'] ?? '';
                $nombre = $_POST['nombre'] ?? '';
                $dni = $_POST['dni'] ?? '';
                $localidad = $_POST['localidad'] ?? '';
                $latitud = str_replace(',', '.', $_POST['latitud'] ?? '');
                $longitud = str_replace(',', '.', $_POST['longitud'] ?? '');

                // Validaciones
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El email no tiene un formato válido.");
                }
                if (!is_numeric($latitud) || $latitud < -90 || $latitud > 90) {
                    throw new Exception("La latitud debe estar entre -90 y 90.");
                }
                if (!is_numeric($longitud) || $longitud < -180 || $longitud > 180) {
                    throw new Exception("La longitud debe estar entre -180 y 180.");
                }
                if (!preg_match('/^\d{7,8}$/', $dni)) {
                    throw new Exception("El DNI debe tener 7 u 8 dígitos numéricos.");
                }

                // Insert usuarios
                $pass_user = password_hash('temp1234', PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (email, password, rol) VALUES (?, ?, 'productor')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $pass_user]);
                $usuario_id = $pdo->lastInsertId();

                // Insert productores
                $sql = "INSERT INTO productores (usuario_id, nombre, dni) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id, $nombre, $dni]);
                $productor_id = $pdo->lastInsertId();

                // Insert campos
                $sql = "INSERT INTO campos (productor_id, localidad, latitud, longitud) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$productor_id, $localidad, $latitud, $longitud]);

                $mensaje = "Productor creado correctamente. Contraseña temporal: temp1234";
                
            } elseif ($accion === 'editar') {
                // Validar datos
                $id = $_POST['id'] ?? 0;
                $nombre = $_POST['nombre'] ?? '';
                $dni = $_POST['dni'] ?? '';
                $email = $_POST['email'] ?? '';
                $localidad = $_POST['localidad'] ?? '';
                $latitud = str_replace(',', '.', $_POST['latitud'] ?? '');
                $longitud = str_replace(',', '.', $_POST['longitud'] ?? '');

                // Validaciones
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El email no tiene un formato válido.");
                }
                if (!is_numeric($latitud) || $latitud < -90 || $latitud > 90) {
                    throw new Exception("La latitud debe estar entre -90 y 90.");
                }
                if (!is_numeric($longitud) || $longitud < -180 || $longitud > 180) {
                    throw new Exception("La longitud debe estar entre -180 y 180.");
                }
                if (!preg_match('/^\d{7,8}$/', $dni)) {
                    throw new Exception("El DNI debe tener 7 u 8 dígitos numéricos.");
                }

                // Actualizar usuario y productor
                $sql = "UPDATE usuarios u JOIN productores p ON u.id = p.usuario_id 
                        SET u.email = ?, p.nombre = ?, p.dni = ? WHERE p.id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $nombre, $dni, $id]);

                // Actualizar campos
                $sql = "SELECT id FROM campos WHERE productor_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $campo_id = $stmt->fetchColumn();

                if ($campo_id) {
                    $sql = "UPDATE campos SET localidad = ?, latitud = ?, longitud = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$localidad, $latitud, $longitud, $campo_id]);
                } else {
                    $sql = "INSERT INTO campos (productor_id, localidad, latitud, longitud) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id, $localidad, $latitud, $longitud]);
                }
                
                $mensaje = "Productor editado correctamente.";
                
            } elseif ($accion === 'eliminar') {
                $id = $_POST['id'] ?? 0;
                
                // Eliminar con prepared statement
                $sql = "DELETE u FROM usuarios u JOIN productores p ON u.id = p.usuario_id WHERE p.id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                
                $mensaje = "Productor eliminado correctamente.";
            }
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Obtener lista actualizada
$sql = "SELECT p.id, p.nombre, u.email, p.dni, c.localidad, c.latitud, c.longitud
        FROM productores p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN campos c ON p.id = c.productor_id
        ORDER BY p.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lista = $stmt->fetchAll();

?>

<div class="contenedor-productores">
  <h2>Productores registrados</h2>
  <?php if (isset($mensaje)): ?>
    <div style="margin-top: 1rem; color: green; text-align: center;"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <?php if (isset($error)): ?>
    <div style="margin-top: 1rem; color: red; text-align: center;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!--botones agregar productor y buscar -->
  <div class="acciones-tabla">
    <button class="btn-nuevo" onclick="mostrarFormulario()">+ Nuevo productor</button>
    <div class="busqueda-dni">
      <label for="busquedaInput">🔍 Búsqueda por DNI</label>
      <input type="text" id="busquedaInput" class="input-busqueda" placeholder="Ej: 30111222">
    </div>
  </div>

  <!-- Mostrar contenido de la tabla -->
  <table class="tabla-productores" border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>DNI</th>
        <th>Email</th>
        <th>Localidad</th>
        <th>Latitud</th>
        <th>Longitud</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lista as $fila): ?>
        <tr>
          <td><?= htmlspecialchars($fila['id']) ?></td>
          <td><?= htmlspecialchars($fila['nombre']) ?></td>
          <td><?= htmlspecialchars($fila['dni']) ?></td>
          <td><?= htmlspecialchars($fila['email']) ?></td>
          <td><?= htmlspecialchars($fila['localidad']) ?></td>
          <td><?= htmlspecialchars($fila['latitud']) ?></td>
          <td><?= htmlspecialchars($fila['longitud']) ?></td>
          <td>
            <button class="btn-accion editar" onclick="mostrarFormulario(<?= $fila['id'] ?>)">Editar</button>
            <button class="btn-accion eliminar" onclick="mostrarEliminar(<?= $fila['id'] ?>, '<?= htmlspecialchars($fila['nombre']) ?>')">Eliminar</button>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  <div class="contenedor-expandir">
    <button id="toggleTabla" class="btn-expandir" onclick="toggleTabla()" title="Mostrar/Ocultar tabla">
      <img id="toggleIcon" src="assets/chevron-down.svg" alt="Expandir" />
    </button>
  </div>
</div>

<!-- Fondo oscurecido y modal -->
<div id="modalBackdrop" class="modal-backdrop" style="display:none;"></div>

<div id="formularioModal" class="modal" style="display:none;">
  <form method="POST" onsubmit="return validarFormulario()">
    <input type="hidden" name="accion" id="accion" value="crear">
    <input type="hidden" name="id" id="productor_id" value="">
    <h3>Agregar/Editar Productor</h3>

    <label>Nombre completo</label>
    <input type="text" name="nombre" id="nombre" required>

    <label>DNI</label>
    <input type="text" name="dni" id="dni" required>

    <label>Email</label>
    <input type="email" name="email" id="email" required>

    <label>Localidad</label>
    <input type="text" name="localidad" id="localidad">
    
    <label>Latitud</label>
    <input type="number" step="0.000001" lang="en" name="latitud" id="latitud">
    
    <label>Longitud</label>
    <input type="number" step="0.000001" lang="en" name="longitud" id="longitud">

    <label>Contraseña administrador</label>
    <input type="password" name="admin_pass" id="admin_pass" required>

    <button type="submit">Guardar</button>
    <button type="button" onclick="cerrarFormulario()">Cancelar</button>
  </form>
</div>

<!-- Modal eliminar productor -->
<div id="eliminarModal" class="modal" style="display:none;">
  <form method="POST">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="eliminar_id">
    <h3>¿Eliminar productor?</h3>
    <p id="eliminarTexto"></p>
    <label>Contraseña administrador</label>
    <input type="password" name="admin_pass" required>
    <div id="eliminarMensaje" style="margin-top: 1rem; color: var(--color-error);"></div>
    <button type="submit">Confirmar eliminación</button>
    <button type="button" onclick="cerrarEliminar()">Cancelar</button>
  </form>
</div>

<script src="index_crud.js"></script>

<?php require_once '../footer.php'; ?>
