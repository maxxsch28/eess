<?php
// setupViajesPendientesLiquidacion.php
// Lista los viajes que no se han liquidados a una fecha determinada
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';
$fecha = explode('/', $_POST['mes']);
$rangoFin = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
$fecha2 = explode('/', $_POST['rangoInicio']);
$rangoInicio = $fecha2[2].'-'.$fecha2[1].'-'.$fecha2[0];



$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
  if(!isset($_POST['soloExternos'])||$_POST['soloExternos']==0){
    $sqlClientes = "SELECT a.sucursal_e, a.parte, a.fletero, f.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, c.codigo, c.nombre Collate SQL_Latin1_General_CP1253_CI_AI as cliente, nom_origen, nom_destin, salida, operador, impobasefl, comprobant, tipo, p.sucursal, p.numero, p.cantidad, p.importefle, p.importe FROM dbo.partes a, dbo.fleteros f, dbo.partevta as p, dbo.clientes c WHERE p.cliente=c.codigo AND a.parte=p.parte AND a.sucursal_e=p.sucursal_e AND a.parte NOT IN (SELECT parte FROM dbo.liquifle a, dbo.detliqui b WHERE a.idtranglob=b.idtranglob) AND a.salida>='$rangoInicio' AND a.salida<='$rangoFin' AND a.fletero=f.fletero AND a.fletero>0 UNION 
    SELECT a.sucursal_e, a.parte, a.fletero, f.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, c.codigo, c.nombre Collate SQL_Latin1_General_CP1253_CI_AI as cliente, nom_origen, nom_destin, salida, operador, impobasefl, comprobant, tipo, p.sucursal, p.numero, p.cantidad, p.importefle, p.importe FROM dbo.partes a, dbo.fleteros f, dbo.partevta p, dbo.clientes c WHERE p.cliente=c.codigo AND a.parte=p.parte AND a.sucursal_e=p.sucursal_e AND  a.salida<'$rangoFin' AND a.salida>='$rangoInicio' AND CONCAT(a.sucursal_e,a.parte) IN (SELECT CONCAT(b.sucursal_e,parte) FROM dbo.liquifle a, dbo.detliqui b WHERE a.idtranglob=b.idtranglob AND a.fecha>'$rangoFin') AND a.fletero=f.fletero AND a.fletero>0 ORDER BY a.fletero ASC, a.salida ASC;";
    
  } 
} else {
  die;
}
fb($sqlClientes);
$stmt = odbc_exec2($mssql2, $sqlClientes, __LINE__, __FILE__);
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas = $cantidadClientes = $totalCantidad = 0;
$comision=array();
$totalAComisionar = array();
while($fila = sqlsrv_fetch_array($stmt)){
  // evita duplicación por comprobantes que aparecen en ambos libros
  if(!isset($encabezaProducto)||$encabezaProducto<>$fila['fletero']){
    if(isset($encabezaProducto)&&$encabezaProducto<>$fila['fletero']){
      $tabla .= "<tr class='info comisionEncabezado'><td colspan=5>Subtotal <b>".utf8_encode($fletero)."</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td></tr>";
    }
    $encabezaProducto = $fila['fletero'];
    $fletero = $fila['nombre'];
    $sumaPrecio[$fila['fletero']] = 0;
    //$tabla .= "<tr class='info comisionEncabezado viaje'><td><b>".utf8_encode($fila['nombre'])."</b></td><td colspan='4' style='text-align:right'></td></tr>";
    unset($encabezaSocio);
  }
  if(!isset($encabezaSocio)||$encabezaSocio<>$fila['codigo']){
    $encabezaSocio = $fila['codigo'];
    $td = "$fila[codigo] - <b>".utf8_encode($fila['cliente'])."</b>";
  } else {
    $td="    \"       \"";
  }
  $signo = (($fila['comprobant']=='NOTA DE CREDITO')?-1:1);
  $fecha_asie=$fila['salida']->format('d/m/Y');
  $tabla .= "<tr class='viaje'>
  <td>$td</td>
  <td>{$fecha_asie}</td>
  <td>$fila[sucursal_e]-$fila[parte]</td><td>$fila[nom_origen] -> $fila[nom_destin]</td>
  <td>";
  if($fila['comprobant']<>''){
    $tabla .= "$fila[comprobant] $fila[tipo] $fila[sucursal]-$fila[numero] (neto facturado $".number_format($fila['importe'], 2, ',', '.').")";
  }
  
  $tabla.="</td><td class='text-right x'>$".number_format($signo*$fila['impobasefl'], 2, ',', '.')."</td>
  </tr>";
  $sumaPrecio[$fila['fletero']] = $sumaPrecio[$fila['fletero']]+$signo*$fila['impobasefl'];
  $totalPrecio = $totalPrecio+$signo*$fila['impobasefl'];
  
  if(isset($tablaEncabezado)){
    $tabla.=$tablaEncabezado;
    unset($tablaEncabezado);
  }
}
if(isset($encabezaProducto)){
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=5>Subtotal <b>".utf8_encode($fletero)."</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td></tr>";
  $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='5' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td></tr>";
} else {
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=6><b>No hay datos</b></td></tr>";
}
echo $tabla;
?>
