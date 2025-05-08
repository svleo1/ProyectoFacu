<?php

// Opciones de sesión seguras (o en php.ini)
ini_set('session.cookie_httponly', 1);
//ini_set('session.cookie_secure', 1);

session_start();

// Generar CSRF token si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Solicitud inválida');
    }

    // Sanitizar input
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '';
    $password = trim($_POST['password'] ?? '');

    if ($email) {
        $stmt = $pdo->prepare("SELECT id, email, password, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol']        = $usuario['rol'];
            $_SESSION['email']      = $usuario['email'];

            // Redirección según rol
            if ($usuario['rol'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: productor/home.php");
            }
            exit;
        }
    }
    $error = "Correo o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Ovinos</title>
  <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="login-page">
  <div class="login-container">
    <h2 class="login-title">Acceso al sistema</h2>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>

    <form class="login-form" method="POST" novalidate>
      <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" id="email" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" minlength="8" required>
      </div>

      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="form-actions">
        <button type="submit" class="btn">Ingresar</button>
        <button type="reset" class="btn btn-secondary">Limpiar</button>
      </div>
    </form>
  </div>
</body>

</body>
</html>


