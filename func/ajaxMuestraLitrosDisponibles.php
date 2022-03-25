<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';

//ChromePhp::log("oko ". ($_SESSION['fechaTurnoAnterior']). ' ssss '.date('Y-m-d H:i:s').'    diferencia '.(strtotime($_SESSION['fechaTurnoAnterior'])-strtotime(date('Y-m-d H:i:s'))));
// tengo que hacer que reste las notas de crédito:
if( !isset($_SESSION['fechaTurnoAnterior']) || (strtotime(date('Y-m-d H:i:s'))-strtotime($_SESSION['fechaTurnoAnterior']))>43200 ){
//if( !isset($_SESSION['fechaTurnoAnterior'])){
  $sqlFechaTurnoAnterior = "SELECT TOP 1 fecha from dbo.cierresturno WHERE idCaja=1 order by idcierreturno desc";
  ChromePhp::log($sqlFechaTurnoAnterior);
  $stmt = odbc_exec2( $mssql, $sqlFechaTurnoAnterior, __FILE__, __LINE__);
  $rowFacturasExluidas = sqlsrv_fetch_array($stmt);
  $_SESSION['fechaTurnoAnterior'] = $rowFacturasExluidas[0]->format('Y-m-d H:i:s');
}


$sql = "select IdArticulo, sum(Cantidad) as facturado, 'fact' as q from dbo.MovimientosDetalleFac, dbo.movimientosfac where FechaEmision>='$_SESSION[fechaTurnoAnterior]' AND IdArticulo IN (2068, 2069) AND dbo.MovimientosDetalleFac.IdMovimientofac=dbo.movimientosfac.IdMovimientoFac AND ExcluidoDeTurno=0 AND IdCierreTurno IS NULL GROUP BY IdArticulo UNION select IdArticulo, SUM(Cantidad) as despachado, 'desp' as q from Despachos where Fecha>='$_SESSION[fechaTurnoAnterior]' AND idArticulo IN (2068, 2069) GROUP BY IdArticulo ORDER BY IdArticulo, q DESC;";
ChromePhp::log($sql);

$stmt = odbc_exec2( $mssql, $sql, __FILE__, __LINE__);

echo "<table class='table table-striped table-bordered IdCierreTurno'><thead><tr><th><b>Combustible</b></th><th>Facturados</th><th>Despachados</th><th>Disponibles</th></tr></thead><tbody>";
while($rowFacturasExluidas = sqlsrv_fetch_array($stmt)){
  if(!isset($producto)||$producto<>$rowFacturasExluidas['IdArticulo']){
    $producto = $rowFacturasExluidas['IdArticulo'];
    //print_r($articulo);
    echo "<tr><td>{$articulo[$rowFacturasExluidas['IdArticulo']]['descripcion']}</td><td>".sprintf("%.2f",$rowFacturasExluidas['facturado'])." lts</td>";
    $despachado = $rowFacturasExluidas['facturado'];
  } else {
    $disponibles = ($rowFacturasExluidas['facturado']-$despachado);
    if($disponibles<0)$noFacturar=1;
    $class = ($disponibles<0)?'neg':'';
    echo "<td>".sprintf("%.2f",$rowFacturasExluidas['facturado'])." lts</td><td class='$class'>".sprintf("%.2f",$disponibles)." lts</td></tr>";
  }
}
if(isset($noFacturar))echo "<tr><td colspan=4 class='alert alert-danger'>HAY MAS FACTURADO QUE DESPACHADO!<br/><br/> NO FACTURAR MAS DEL PRODUCTO QUE ESTÉ EN ROJO</td></tr>";
echo "</table>";


//echo $tabla;
?>