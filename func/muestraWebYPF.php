<?php
// muestraWebYPF.php
// muestra modal para descarga de camión y a la vez procesa esa descarga.
include('../include/inicia.php');
$sql = "SELECT * FROM webypf ORDER BY id DESC LIMIT 1";
$result = $mysqli->query($sql);
$fila = $result->fetch_assoc();
echo $fila['web'];
?>
