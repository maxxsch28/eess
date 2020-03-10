<?php
// ajaxSociosLibrosABM.php
// Recibe PDF de escaneo libro actas
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$sql = "SELECT TOP 10 * FROM dbo.[socios.libros] ORDER BY idFoja DESC;";
$stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
$res='';
while($foja = sqlsrv_fetch_array($stmt)){
  $res .= "<li><a href='?idFoja=$foja[idFoja]'>Libro Nº$foja[libro] - Foja Nº$foja[foja]</a></li>";
}
//echo json_encode($msg);
echo $res;
die;
?>
