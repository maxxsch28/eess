<?php
// listaCierreTesoreriaEfectivo.php
// Muestra lo que debería haber en la caja de tesorería al cierre de cada mes 
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 //print_r($_POST); die;
// $array=array();
 // 14/08/2018
if(isset($_POST['fechaCierre'])&&$_POST['fechaCierre']<>''){
  $mm=substr($_POST['fechaCierre'],3,2);
  $aa=substr($_POST['fechaCierre'],6,4);
  $dd=substr($_POST['fechaCierre'],0,2);
}
$ultimoDiaMes = "$aa-$mm-".cal_days_in_month(CAL_GREGORIAN, $mm, $aa);

$tablaEfectivo = "";$a=$q=0;
$jsonCierre = array();

// obtengo los datos desde el último cierre de tesorería
$sqlIVACategoria = "select a.Consignado, d.Descripcion, 21 as Alicuota,  SUM(a.NetoCombustibles) as Neto, SUM(CASE IdTipomovimiento WHEN 'NCB' THEN -1*IVA WHEN 'NCA' THEN -1*IVA ELSE IVA END) AS IVA from dbo.movimientosfac a, dbo.CategoriasIVA d WHERE d.IdCategoriaIVA=a.IdCategoriaIVA AND a.FechaEmision>='$aa-$mm-01' and FechaEmision<'$ultimoDiaMes 23:59:59' AND IdTipoMovimiento='NLP' GROUP BY a.Consignado, d.Descripcion UNION select a.Consignado, d.Descripcion, e.Alicuota, SUM(CASE IdTipomovimiento WHEN 'NCB' THEN -1*(a.NetoCigarrillos+a.NetoCombustibles+a.NetoConceptosFinancieros+a.NetoLubricantes+a.NetoMercaderias+a.NetoNoGravado) WHEN 'NCA' THEN -1*(a.NetoCigarrillos+a.NetoCombustibles+a.NetoConceptosFinancieros+a.NetoLubricantes+a.NetoMercaderias+a.NetoNoGravado) ELSE (a.NetoCigarrillos+a.NetoCombustibles+a.NetoConceptosFinancieros+a.NetoLubricantes+a.NetoMercaderias+a.NetoNoGravado) END) AS Neto, SUM(CASE IdTipomovimiento WHEN 'NCB' THEN -1*b.Cantidad*b.IVA WHEN 'NCA' THEN -1*b.Cantidad*b.IVA ELSE b.Cantidad*b.IVA END) AS IVA from dbo.movimientosdetallefac b, dbo.movimientosfac a, dbo.CategoriasIVA d, dbo.AlicuotasIVA e WHERE b.IdAlicuotaIVA=e.IdAlicuotaIva AND d.IdCategoriaIVA=a.IdCategoriaIVA AND  a.IdMovimientoFac=b.IdMovimientoFac AND a.FechaEmision>='$aa-$mm-01' and FechaEmision<'$ultimoDiaMes 23:59:59' AND IdTipoMovimiento NOT IN ('REM', 'RDV') GROUP BY a.Consignado, d.Descripcion, e.Alicuota UNION select a.Consignado, d.Descripcion, 0 as Alicuota, SUM(CASE IdTipomovimiento WHEN 'NCB' THEN -1*(b.Cantidad*b.Precio) WHEN 'NCA' THEN -1*(b.Cantidad*b.Precio) ELSE (b.Cantidad*b.Precio) END) AS Neto, SUM(CASE IdTipomovimiento WHEN 'NCB' THEN -1*b.Cantidad*b.IVA WHEN 'NCA' THEN -1*b.Cantidad*b.IVA ELSE b.Cantidad*b.IVA END) AS IVA from dbo.movimientosdetallefac b, dbo.movimientosfac a, dbo.CategoriasIVA d  WHERE IdAlicuotaIVA IS NULL AND d.IdCategoriaIVA=a.IdCategoriaIVA AND  a.IdMovimientoFac=b.IdMovimientoFac AND a.FechaEmision>='$aa-$mm-01' and FechaEmision<'$ultimoDiaMes 23:59:59' AND IdTipoMovimiento NOT IN ('REM', 'RDV') GROUP BY a.Consignado, d.Descripcion;";

ChromePhp::log('IVA EESS '.$sqlIVACategoria);
////////////////////////////////////////////////////////////////////

$iva = $neto = 0;
//Consignado, Descripcion,  Neto, Alicuota, IVA
$stmt = odbc_exec2( $mssql, $sqlIVACategoria, __LINE__, __FILE__);
while($fila = sqlsrv_fetch_array(($stmt))){
  if(!isset($header)||$header<>$fila['Consignado']){
    if($fila['Consignado']==0){
      $header=0;
      $jsonCierre[] = array('t' => 'EESS', 'q' => 'NoConsignado', 'clase' => "h2", 'txt' => '<b>Kiosko</b>', 'neto' => "Neto", 'alicuota'=> "Alicuota", 'iva' => "IVA");
      $iva = $neto = 0;
      $t = 'NoConsignado';
    } else {
      $jsonCierre[] = array('t' => 'EESS', 'q' => 'NoConsignado', 'clase' => 'bold', 'txt' => "Total", 'neto' => peso($neto),  'alicuota' => '--', 'iva' => peso($iva));
      $header=1;
      $jsonCierre[] = array('t' => 'EESS', 'q' => 'Consignado', 'clase' => "h2", 'txt' => "<b>Combustibles</b>", 'neto' => "Neto", 'alicuota'=> "Alicuota", 'iva' => "IVA");
      $t = 'Consignado';
    }
  }
  $jsonCierre[] = array('t' => 'EESS', 'q' => "$t", 'clase' => "", 'txt' => "$fila[Descripcion]", 'neto' => peso($fila['Neto']), 'alicuota' => "$fila[Alicuota]", 'iva' => peso($fila['IVA']));
  $iva += $fila['IVA'];
  $neto += $fila['Neto'];
}


$jsonCierre[] = array('t' => 'EESS', 'q' => 'Consignado', 'clase' => 'bold', 'txt' => "Total", 'neto' => peso($neto), 'alicuota'=>'--', 'iva' => peso($iva));
echo json_encode($jsonCierre);

?>
