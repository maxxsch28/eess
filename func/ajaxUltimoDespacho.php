<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$sql = "SELECT TOP 1 Fecha, IdArticulo FROM dbo.Despachos ORDER BY IdDespacho DESC;";
//$sql = "SELECT TOP 1 Fecha, IdArticulo FROM dbo.Despachos ORDER BY IdDespacho ASC;";

$stmt = odbc_exec2($mssql, $sql);
$tmp = array();
while($fila = sqlsrv_fetch_array($stmt)){
  $tmp['ultimoDespacho']=$fila[0]->format('d/m/y H:i:s');
  $tmp['IdArticulo']=$fila[1];
}
echo json_encode($tmp);
?>
