<?php
// listaCierreTesoreriaEfectivo.php
// Muestra lo que debería haber en la caja de tesorería al cierre de cada mes 
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//   print_r($_POST); die;

$aa = $_POST['anio'];
$ab = $aa + 1;
setlocale(LC_ALL,"es_ES");
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$tablaEfectivo = "";$a=$q=0;
$jsonCierre = array();
$tmp = array();
$incluyeCierreAsiento = true;
if($_POST['periodicidad']=='true'){
  $fecha = "DATEADD(MONTH, DATEDIFF(MONTH,0, a.Fecha), 0)";
  $formatoFecha = 'F Y';
} else {
  $fecha = "CONVERT(date, a.Fecha)";
  $formatoFecha = 'd/m/Y';
}


// obtengo los datos desde el último cierre de tesorería
$sqlIVACategoria = "select $fecha as Fecha2, a.IdModeloContable, c.Codigo, c.IdCuentaContable, c.Descripcion, DebitoCredito, SUM(CASE DebitoCredito WHEN 0 THEN Importe ELSE 0 END) as Debe, SUM(CASE DebitoCredito WHEN 1 THEN Importe ELSE 0 END) as Haber, Concepto, AVG(a.IdAsiento) as IdAsiento2 from dbo.asientos a, dbo.asientosdetalle b, dbo.cuentascontables c where a.IdAsiento=b.IdAsiento AND fecha>='$aa-01-01' and Fecha<'$ab-01-01 00:00:00' AND b.idcuentaContable=c.IdCuentaContable AND a.IdModeloContable IS NULL GROUP BY  $fecha, a.IdAsiento, a.IdModeloContable, c.Codigo, c.IdCuentaContable, Descripcion, DebitoCredito, Concepto UNION 
select $fecha as Fecha2, a.IdModeloContable, c.Codigo, c.IdCuentaContable, c.Descripcion, DebitoCredito, SUM(CASE DebitoCredito WHEN 0 THEN Importe ELSE 0 END) as Debe, SUM(CASE DebitoCredito WHEN 1 THEN Importe ELSE 0 END) as Haber, d.Descripcion as Concepto, 0 as IdAsiento2 from dbo.asientos a, dbo.asientosdetalle b, dbo.cuentascontables c, dbo.modeloscontables d where d.IdModeloContable=a.IdModeloContable AND a.IdAsiento=b.IdAsiento AND fecha>='$aa-01-01' and Fecha<'$ab-01-01 00:00:00' AND b.idcuentaContable=c.IdCuentaContable AND a.IdModeloContable IS NOT NULL GROUP BY  $fecha, a.IdModeloContable, c.Codigo, c.IdCuentaContable, c.Descripcion, DebitoCredito, d.Descripcion  ORDER BY Fecha2, Concepto, a.IdModeloContable, DebitoCredito;";

//ChromePhp::log('IVA EESS '.$sqlIVACategoria);

////////////////////////////////////////////////////////////////////
/*
  IdModeloContable, Fecha2, Codigo, IdCuentaContable, Descripcion, DebitoCredito, Debe, Haber, Concepto, IdAsiento2
*/
$apertura = "APERTURA EJERC.(ACTIVO+PASIVO+PN)";
$cierre1 = "Cierre Ejercic.(ACTIVO+PASIVO+PN)";
$cierre2 = "Cierre Ejercic.(GANAN.VS.PERD)";
$numeroasiento = 1;
$debe = $haber = $debeTotal = $haberTotal = 0;
$stmt = odbc_exec2( $mssql, $sqlIVACategoria, __LINE__, __FILE__);
while($fila = sqlsrv_fetch_array(($stmt))){
  if(!isset($header)||$header<>$fila['Concepto']){
    $numeroasiento++;
    if(isset($header)&&$incluyeCierreAsiento) {
      // no es el primer asiento, cargo el cierre del asiento anterior
      $tmp[] = array('clase' => 'cierreAsiento', 'fecha' => '', 'txt' => "Total", 'debe' => peso($debe),  'haber' => peso($haber));
      $debe = $haber = 0;
    }elseif(isset($header)&&!$incluyeCierreAsiento) {
      // no es el primer asiento, cargo el cierre del asiento anterior
      $debe = $haber = 0;
    }
    if(isset($header)){
      switch ($header) {
        case $apertura:
          ChromePhp::log('Apertura ');
          // Estoy cerrando el asiento de apertura, mando todo adelante
          $jsonCierre = array_merge($tmp, $jsonCierre); 
          break;
        case $cierre1:
          ChromePhp::log('Cierre1 ');
          $tmpCierre1 = $tmp;
          break;
        case $cierre2:
          ChromePhp::log('Cierre2 ');
          $tmpCierre2 = $tmp;
          break;
        default:
          $jsonCierre =  array_merge($jsonCierre, $tmp);
      }
      unset($tmp);$tmp = array();
    }
    $tmp[] = array('clase' => 'abreAsiento', 'fecha' => $mes[$fila['Fecha2']->format('n')].' '.$fila['Fecha2']->format('Y'), 'txt' => "$fila[Concepto]", 'debe' => '',  'haber' => '', 'numero' => key($jsonCierre));
    $string = $fila['Fecha2']->format($formatoFecha);
    $date = DateTime::createFromFormat("d/m/Y", $string);
    //$tmp[] = array('clase' => 'abreAsiento', 'fecha' => strftime("%A", $date->getTimestamp()), 'txt' => "$fila[Concepto]", 'debe' => '',  'haber' => '');
    
//     strftime("%A",$date->getTimestamp());
    $header = $fila['Concepto'];
  }
  if($fila['Debe']>0){
    $debe += $fila['Debe'];
    $debeTotal += $fila['Debe'];
  } 
  if($fila['Haber']>0){
    $haber += $fila['Haber'];
    $haberTotal += $fila['Haber'];
  } 
  $tmp[] = array('clase' => 'asiento', 'fecha' => '', 'txt' => "$fila[Descripcion]", 'debe' => peso($fila['Debe']),  'haber' => peso($fila['Haber']));
}
$error = (round($debe,2)-round($haber,2)<>0)?"error":'';
$jsonCierre[] = array('clase' => "cierreAsiento", 'fecha' => '', 'txt' => "Total", 'debe' => peso($debe),  'haber' => peso($haber), 'error' => ((round($debe,2)-round($haber,2)<>0)?"error":''));
$jsonCierre = array_merge($jsonCierre, $tmpCierre1);
$jsonCierre = array_merge($jsonCierre, $tmpCierre2);
//$jsonCierre[] = array('clase' => 'cierreAsientoTotal', 'fecha' => '', 'txt' => "Gran Total", 'debe' => peso($debeTotal),  'haber' => peso($haberTotal));

echo json_encode($jsonCierre);

?>
