<?php
date_default_timezone_set("America/Mexico_City");
$fechaHora = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fecha y Hora Actual</title>
</head>
<body>
    <h1>Fecha y hora actuales:</h1>
    <p><?php echo $fechaHora; ?></p>

    <a href="index.html">Volver</a>
</body>
</html>
