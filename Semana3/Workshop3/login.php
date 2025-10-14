<?php
// Mostrar el formulario de login si se redireccionó
if (isset($_GET['username'])):
?>
<h2>Login</h2>
<form method="POST" action="autenticar.php">
    <label for="login_username">Usuario:</label>
    <input type="text" name="username" id="login_username" value="<?= htmlspecialchars($_GET['username']) ?>" required><br>
    <label for="login_password">Contraseña:</label>
    <input type="password" name="password" id="login_password" required><br>
    <button type="submit">Ingresar</button>
</form>
<?php endif; ?>
