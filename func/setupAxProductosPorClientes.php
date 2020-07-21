<?php
// setupProductosPorClientes.php
// Muestra IVA Ventas completo
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';



if(!isset($_SESSION['productosTransporte'])){
  $sqlProductos = "SELECT codigo, nombre FROM dbo.PRODUCTO WHERE  (inhabfecha='1900-01-01' OR rehabfecha>inhabfecha) order by nombre";
  $stmt = odbc_exec2($mssql2, $sqlProductos, __LINE__, __FILE__);
  while($producto = sqlsrv_fetch_array($stmt)){
    print_r($producto);
    $_SESSION['productosTransporte'][$producto['codigo']] = trim($producto['nombre']);
  }
}
ChromePhp::log($_SESSION['productosTransporte']);


$soloProducto = (isset($_POST['soloProducto'])&&$_POST['soloProducto']>0)?" AND dbo.PRODUCTO.codigo=$_POST[soloProducto]":"";
$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";

$orden = 'clienteNombre';
if( $_POST['ordenaCliente'] == false){
  $orden = 'producto';
  var_dump($_POST['ordenaCliente']);
  ChromePhp::log($_POST['ordenaCliente'].'  ------    OrdenaCliente');
} 
$orden = 'producto';
ChromePhp::log($_POST['ordenaCliente']);


if(isset($_POST['mes'])){
  $periodo = "";
  if(strlen($_POST['mes'])==4){
    $anio = $_POST['mes'];
    $mes = "";
  } else {
    $anio = substr($_POST['mes'], 0, 4);
    $mes = " AND datepart(month, dbo.concasie.fecha_asie)='".substr($_POST['mes'], 5, 2)."'";
  }
  if(!isset($_POST['soloExternos'])||$_POST['soloExternos']==0){
    if(isset($_POST['muestraPagos'])&&$_POST['muestraPagos']=='true'){
      $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad, producto, d.pago FROM dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes, dbo.impupagf d WHERE d.numero=dbo.detaosvt.numero AND d.sucursal=dbo.detaosvt.sucursal AND d.comproban IN ('FACTURA', 'NOTA DE CREDITO') AND DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob $soloProducto UNION ALL SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad, producto, 0 as pago FROM dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE concat(dbo.detaosvt.sucursal, dbo.detaosvt.numero) NOT IN (SELECT concat(sucursal,numero) FROM impupagf WHERE comproban IN ('FACTURA', 'NOTA DE CREDITO')) AND DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob $soloProducto UNION ALL ";
      ChromePhp::log('1');
    } else {
      $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad, producto, 0 as pago FROM dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob $soloProducto UNION ALL ";
       ChromePhp::log('2');
    }
    $sqlClientes .= "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.codigo, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto, 0 as pago FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob AND dbo.histvtam.suctranglo=dbo.concasie.suctranglo and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detavtas.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob $soloProducto AND libro='VENTAS' ORDER BY $orden;";
    ChromePhp::log('3');
  } elseif($_POST['soloExternos']==1){
    // Solo Fleteros
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad FROM  dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob ORDER BY $orden;";
     ChromePhp::log('4');
    
  } else { 
//     // Solo Clientes
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.codigo, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' $mes AND dbo.detavtas.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY $orden;";
     ChromePhp::log('5');
  }
} else {
  die;
}
ChromePhp::log($sqlClientes);
$stmt = odbc_exec2($mssql2, $sqlClientes, __LINE__, __FILE__);
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas = $cantidadClientes = $totalCantidad = 0;
$comision=array();
$totalAComisionar = array();
$comprobantePorLibro = array();
while($fila = sqlsrv_fetch_array($stmt)){
  // evita duplicación por comprobantes que aparecen en ambos libros
  if(isset($comprobantePorLibro[$fila['idtranglob']]['INGRESOS'])&&$fila['libro']=='VENTAS'){
    //evita duplicado
  } else {
    $comprobantePorLibro[$fila['idtranglob']][$fila['libro']] = $fila['idtranglob'];
    if(!isset($encabezaProducto) || ($orden == 'producto' && $encabezaProducto<>$fila['codigo']) || ($orden == 'clienteNombre' && $encabezaProducto<>$fila['clienteCodigo']) ){
      if(isset($encabezaProducto)&&$encabezaProducto<>$fila['codigo']&&$orden=='producto'){
        $tabla .= "<tr class='info comisionEncabezado'><td colspan=4>Subtotal <b>{$_SESSION['productosTransporte'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaIVA[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaPercepciones[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaTotal[$encabezaProducto], 2, ',', '.')."</td><td></td></tr>";
      } else if(isset($encabezaProducto)&&$encabezaProducto<>$fila['clienteCodigo']&&$orden=='clienteNombre'){
        $tabla .= "<tr class='info comisionEncabezado'><td colspan=4>Subtotal <b>".utf8_encode(strtoupper($fila['clienteNombre']))."</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaIVA[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaPercepciones[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaTotal[$encabezaProducto], 2, ',', '.')."</td><td></td></tr>";
      }
      $encabezaProducto = ($orden=='producto')?$fila['codigo']:$fila['clienteCodigo'];
      $sumaNeto[$fila['codigo']] = $sumaIVA[$fila['codigo']] = $sumaPrecio[$fila['codigo']] = $sumaPercepciones[$fila['codigo']] = $sumaTotal[$fila['codigo']] =0;
      $tabla .= ($orden=='producto')?"<tr class='info comisionEncabezado viaje'><td><b>{$_SESSION['productosTransporte'][$fila['codigo']]}</b></td><td colspan='9' style='text-align:right'></td></tr>":'';
      unset($encabezaSocio);
    }
    if(!isset($encabezaSocio)||$encabezaSocio<>$fila['clienteCodigo']){
      $encabezaSocio = $fila['clienteCodigo'];
      $td = "<b>".utf8_encode(ucwords(strtolower($fila['clienteNombre'])))."</b> ($fila[clienteCodigo])";
    } else $td="    \"       \"";
    $signo = (($fila['comprobant']=='NOTA DE CREDITO')?-1:1);
    $fecha_asie=substr($fila['fecha_asie'],0,10);
    $fecha_asie=$fila['fecha_asie']->format('d/m/Y');
    $tabla .= "<tr class='viaje'><td>$td</td><td>$fecha_asie</td><td>$fila[comprobant] $fila[sucursal]-$fila[numero]</td><td class='text-right'>".number_format($signo*$fila['cantidad'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['precio_vta'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['neto_grava'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['iva'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['ingbruto']/2.3333, 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*($fila['total_item']+$fila['ingbruto']/2.3333), 2, ',', '.')."</td>
    <td>$fila[pago]</td></tr>";
    $sumaNeto[$fila['codigo']] = $sumaNeto[$fila['codigo']]+$signo*$fila['neto_grava'];
    $sumaIVA[$fila['codigo']] = $sumaIVA[$fila['codigo']]+$signo*$fila['iva'];
    $sumaPrecio[$fila['codigo']] = $sumaPrecio[$fila['codigo']]+$signo*$fila['precio_vta'];
    $sumaPercepciones[$fila['codigo']] = $sumaPercepciones[$fila['codigo']]+$signo*$fila['ingbruto']/2.333;
    $sumaTotal[$fila['codigo']]= $sumaTotal[$fila['codigo']]+$signo*($fila['total_item']+$fila['ingbruto']/2.3333);
    
    $totalNeto = $totalNeto+$signo*$fila['neto_grava'];
    $totalIVA = $totalIVA+$signo*$fila['iva'];
    $totalPrecio = $totalPrecio+$signo*$fila['precio_vta'];
    $totalPercepciones = $totalPercepciones+$signo*$fila['ingbruto']/2.333;
    $total = $total+$signo*($fila['total_item']+$fila['ingbruto']/2.3333);
    
    if(isset($tablaEncabezado)){
      $tabla.=$tablaEncabezado;
      unset($tablaEncabezado);
    }
  }
}
if(isset($encabezaProducto)){
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=4>Subtotal <b>{$_SESSION['productosTransporte'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaIVA[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaPercepciones[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaTotal[$encabezaProducto], 2, ',', '.')."</td><td></td></tr>";
  $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='4' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td><td class='text-right'><b>$".number_format($totalNeto, 2, ',', '.')."</b></td><td class='text-right'><b>$".number_format($totalIVA, 2, ',', '.')."</b></td><td><b>$".number_format($totalPercepciones, 2, ',', '.')."</b></td><td><b>$".number_format($total, 2, ',', '.')."</b></td><td></td></tr>";
} else {
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=5><b>No hay datos</b></td><td></td><td></td><td></td></tr>";
}
echo $tabla;
?>
