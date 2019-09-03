<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';
$teresa = (isset($_GET['teresa']))?" AND Fecha>'".date("Y-m-d")."'":" AND Fecha>'".date("Y")."-01-01'";
//$teresa = " AND Fecha>'2017-01-01' AND Fecha<='2017-12-31'";
$andFecha = "";

$sql = "SELECT DISTINCT CAST(Fecha AS date) as Fecha2, IdArticulo, PrecioPublico FROM dbo.CambiosPrecio WHERE idarticulo IN (SELECT idarticulo FROM dbo.articulos WHERE consignado=1) $andFecha ORDER BY Fecha2 DESC, IdArticulo DESC, PrecioPublico DESC ;";
 //Chromephp::log($sql);
$stmt = odbc_exec2( $mssql, $sql, __FILE__, __LINE__);

$row_count = sqlsrv_num_rows( $stmt );
// var_dump(sqlsrv_has_rows($stmt));
// var_dump($row_count);
//IdMovimientoFac	IdTipoMovimiento	PuntoVenta	Numero	Fecha	RazonSocial	Total	Cantidad	IdArticulo
//473236	FAA	8	77650	2016-04-14 09:15:08.983	STIP NESTOR	1968.6577	108.0500	2068
// TODO armar que calcule las variaciones porcentuales entre cambios y un acumulado anual para cada precio.
// para eso debería armarlo ordenado al revés

$tabla = "<form name='listaFacturasExcluidas' id='listaFacturasExcluidas'>
<table class='table table-striped table-bordered IdCierreTurno'><thead>
<tr><th>Fecha</th>";
foreach($articulo as $IdArticulo => $detalleArticulo) {
  $tabla .= "<th>$detalleArticulo[descripcion]</th>";
}
$tabla .= "</tr></thead><tbody>";
$sumaLote = 0;
$_SESSION['percepcionesFcDiferencia']=0;
$precio=array();
while($fila = sqlsrv_fetch_array($stmt)){
  if(!isset($precio[$fila['Fecha2']->format('d/m/y')][$fila['IdArticulo']])){
    $precio[$fila['Fecha2']->format('d/m/y')][$fila['IdArticulo']] = $fila['PrecioPublico'];
  }
}
foreach($precio as $fecha => $cambioPrecio){
  if(!isset($f)||$f<>$fila['fecha2']){
    if($f<>$fecha) $tabla.="</tr>";
    $tabla.= "<tr><td>$fecha</td>";
    $f = $fila['fecha2'];
  }
  foreach($articulo as $IdArticulo => $detalleArticulo) {
    $tabla .= "<td>$ ".sprintf("%.2f",$cambioPrecio[$IdArticulo])."</td>";
  }
}
echo $tabla."</tbody></table>";
?>
