<?php
session_start();

// Validar sesión
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$db = 'bd';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Funciones auxiliares
function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function saveUser($conn, $user) {
    $stmt = $conn->prepare("UPDATE usuarios SET username=?, password=?, activo=? WHERE id=?");
    $stmt->bind_param("ssii", $user['username'], $user['password'], $user['activo'], $user['id']);
    $stmt->execute();
    $stmt->close();
}

function saveUserWithoutPassword($conn, $user) {
    $stmt = $conn->prepare("UPDATE usuarios SET username=?, activo=? WHERE id=?");
    $stmt->bind_param("sii", $user['username'], $user['activo'], $user['id']);
    $stmt->execute();
    $stmt->close();
}

function deleteUser($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Acciones
$action = $_GET['action'] ?? '';
$message = '';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $activo = 1;
    $stmt = $conn->prepare("INSERT INTO usuarios (username, password, activo) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $password, $activo);
    $stmt->execute();
    $stmt->close();
    $message = "Usuario agregado.";
}

if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $username = $_POST['username'];
    $activo = isset($_POST['active']) ? 1 : 0;

    $user = getUserById($conn, $id);
    if (!$user) {
        $message = "Usuario no encontrado.";
    } else {
        $user['username'] = $username;
        $user['activo'] = $activo;

        if (!empty($_POST['password'])) {
            $user['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            saveUser($conn, $user);
        } else {
            saveUserWithoutPassword($conn, $user);
        }

        $message = "Usuario editado.";
    }
}

if ($action == 'delete') {
    $id = intval($_GET['id']);
    deleteUser($conn, $id);
    $message = "Usuario eliminado.";
}

if ($action == 'disable') {
    $id = intval($_GET['id']);
    $user = getUserById($conn, $id);
    if ($user) {
        $user['activo'] = 0;
        saveUserWithoutPassword($conn, $user);
        $message = "Usuario deshabilitado.";
    }
}

// Pantalla principal
echo "<h1>Panel de Administración</h1>";
echo "<p>Bienvenido, <strong>{$_SESSION['username']}</strong></p>";
if ($message) echo "<p style='color:green;'>$message</p>";

if ($action == 'add') {
    echo "<h2>Agregar Usuario</h2>
    <form method='post'>
        Usuario: <input name='username' required><br>
        Contraseña: <input name='password' type='password' required><br>
        <button type='submit'>Agregar</button>
    </form>
    <a href='admin.php'>Volver</a>";
    exit;
}

if ($action == 'edit') {
    $id = intval($_GET['id']);
    $user = getUserById($conn, $id);
    if (!$user) {
        echo "<p>Usuario no encontrado.</p><a href='admin.php'>Volver</a>";
        exit;
    }
    echo "<h2>Editar Usuario</h2>
    <form method='post'>
        <input type='hidden' name='id' value='{$user['id']}'>
        Usuario: <input name='username' value='{$user['username']}' required><br>
        Contraseña (dejar vacío para no cambiar): <input name='password' type='password'><br>
        Activo: <input type='checkbox' name='active' ".($user['activo'] ? 'checked' : '')."><br>
        <button type='submit'>Guardar</button>
    </form>
    <a href='admin.php'>Volver</a>";
    exit;
}

// Listado de usuarios
$result = $conn->query("SELECT * FROM usuarios");
echo "<h2>Listado de Usuarios</h2>
<a href='admin.php?action=add'>Agregar Usuario</a>
<table border='1' cellpadding='5'>
<tr>
    <th>ID</th>
    <th>Usuario</th>
    <th>Activo</th>
    <th>Acciones</th>
</tr>";
while ($user = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$user['id']}</td>
        <td>{$user['username']}</td>
        <td>".($user['activo'] ? 'Sí' : 'No')."</td>
        <td>
            <a href='admin.php?action=edit&id={$user['id']}'>Editar</a> |
            <a href='admin.php?action=delete&id={$user['id']}' onclick='return confirm(\"¿Eliminar usuario?\")'>Eliminar</a> |
            ".($user['activo'] ? "<a href='admin.php?action=disable&id={$user['id']}'>Deshabilitar</a>" : "")."
        </td>
    </tr>";
}
echo "</table>";
?>
