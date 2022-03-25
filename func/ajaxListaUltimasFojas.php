<?php
// ajaxListaUltimasFojas.php
// Muestra las últimas fojas cargadas para el ABM o todas ordenadas por libro para el listado
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


if(!isset($_SESSION['libros'])){
  $sql = "SELECT * FROM dbo.[socios.libros2] order by tipo ASC, numeroLibro ASC;";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  $selectorLibro = "";
  $_SESSION['libros'] = array();
  while($row = sqlsrv_fetch_array($stmt)){
    $_SESSION['libros'][$row['idLibro']] = ucfirst($row['tipo'])." Nº$row[numeroLibro]";
  }
  $_SESSION['selectorLibro'] = $selectorLibro;
}


if(!isset($_GET['listaTodos'])){
  $sql = "SELECT TOP 10 * FROM dbo.[socios.fojas] ORDER BY idFoja DESC;";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  $res='';
  while($foja = sqlsrv_fetch_array($stmt)){
    $res .= "<li><a href='sociosLibrosAlta.php?idFoja=$foja[idFoja]'>".$_SESSION['libros'][$foja['idLibro']]." - Foja Nº$foja[foja]</a></li>";
  }
  //echo json_encode($msg);
  echo $res;
  die;
} else {
  // SELECT * FROM dbo.[socios.libros] ORDER BY libro asc, foja asc
  $sql = "SELECT * FROM dbo.[socios.fojas] ORDER BY idLibro DESC, foja DESC;";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  $res='';
  while($foja = sqlsrv_fetch_array($stmt)){
    $res .= "<li><a href='sociosLibrosAlta.php?idFoja=$foja[idFoja]' title='$foja[detalle]'>".$_SESSION['libros'][$foja['idLibro']]." - Foja Nº$foja[foja]</a></li>";
  }
  //echo json_encode($msg);
  echo $res;
  die;


}
?>
