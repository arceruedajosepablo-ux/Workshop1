<?php
// Conexión a la base de datos
$host = 'localhost';
$db = 'bd';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $provincia_id = $_POST['provincia'];

    // Verificar si el username ya existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $error = "El nombre de usuario ya existe. Por favor, elija otro.";
        $stmt_check->close();
    } else {
        $stmt_check->close();
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, username, password, provincia_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nombre, $apellidos, $username, $password, $provincia_id);
        $stmt->execute();
        $stmt->close();

        // Redireccionar a login con el username
        header("Location: login.php?username=" . urlencode($username));
        exit;
    }
}

// Obtener provincias de la base de datos
$provincias = [];
$result = $conn->query("SELECT id, nombre FROM provincias");
while ($row = $result->fetch_assoc()) {
    $provincias[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <?php if (!empty($error)): ?>
        <div style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="index.php">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required><br>

        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" id="apellidos" required><br>

        <label for="username">Nombre de usuario:</label>
        <input type="text" name="username" id="username" required><br>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required><br>

        <label for="provincia">Provincia:</label>
        <select name="provincia" id="provincia" required>
            <option value="">Seleccione una provincia</option>
            <?php foreach ($provincias as $provincia): ?>
                <option value="<?= $provincia['id'] ?>"><?= htmlspecialchars($provincia['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
