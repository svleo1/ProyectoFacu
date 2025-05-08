<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: admin/dashboard.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Gestión Ovino-Caprino</title>
  <link rel="stylesheet" href="css/landing.css">
</head>
<body>
  <header>
    <div class="container">
      <h1>Sistema Ovino-Caprino</h1>
      <p>Gestión técnica, stock e informes ambientales para productores.</p>
      <a href="login.php" class="btn-acceder">Acceder al Sistema</a>
    </div>
  </header>

  <section class="features">
    <div class="container">
      <h2>¿Qué ofrece nuestro sistema?</h2>
      <div class="cards">
        <div class="card">
          <h3>Gestión de Stock</h3>
          <p>Registro de animales, movimientos y producción por lote y especie.</p>
        </div>
        <div class="card">
          <h3>Campos y Análisis</h3>
          <p>Control técnico de campos, pasturas y análisis ambientales integrados.</p>
        </div>
        <div class="card">
          <h3>Reportes Automatizados</h3>
          <p>Generación de informes mensuales y exportación de datos a PDF o Excel.</p>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; <?php echo date('Y'); ?> Sistema Ovino-Caprino. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
