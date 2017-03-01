<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';



if(!isset($_SESSION['productosTransporte'])){
  $sqlProductos = "SELECT codigo, nombre FROM dbo.PRODUCTO order by codigo";
  $stmt = odbc_exec2($mssql2, $sqlProductos, __LINE__, __FILE__);
  while($producto = odbc_fetch_array($stmt)){
    $_SESSION['productosTransporte'][$producto['codigo']] = trim($producto['nombre']);
  }
}
//fb($_SESSION['productosTransporte']);


$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
  $periodo = "";
  $anio = substr($_POST['mes'], 0, 4);
  $mes = substr($_POST['mes'], 5, 2);
  if(!isset($_POST['soloExternos'])||$_POST['soloExternos']==0){
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle Collate SQL_Latin1_General_CP1253_CI_AI, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, p.codigo, p.detalle Collate SQL_Latin1_General_CP1253_CI_AI, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre  Collate SQL_Latin1_General_CP1253_CI_AI as clienteNombre, p.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad, producto FROM dbo.concasie, dbo.detaosvt, [SqlCoop_DBSHARED].dbo.PRODUCTO p, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detaosvt.producto=p.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob UNION SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle Collate SQL_Latin1_General_CP1253_CI_AI, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, p.codigo, p.detalle Collate SQL_Latin1_General_CP1253_CI_AI, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre  Collate SQL_Latin1_General_CP1253_CI_AI as clienteNombre, p.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, [SqlCoop_DBSHARED].dbo.PRODUCTO p, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detavtas.producto=p.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY producto  ;";
  } elseif($_POST['soloExternos']==1){
    // Solo Fleteros
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad FROM  dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob ORDER BY producto;";
  } else { 
    // Solo Clientes
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.codigo, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detavtas.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY producto;";
  }
} else {
  die;
}

$stmt = odbc_exec2( $mssql2, $sqlClientes, __LINE__, __FILE__);
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas = $cantidadClientes = $totalCantidad = 0;
$comision=array();
$totalAComisionar = array();
$comprobantePorLibro = array();
while($fila = odbc_fetch_array($stmt)){
  // evita duplicación por comprobantes que aparecen en ambos libros
  if(isset($comprobantePorLibro[$fila['idtranglob']]['INGRESOS'])&&$fila['libro']=='VENTAS'){
    //evita duplicado
  } else {
    $comprobantePorLibro[$fila['idtranglob']][$fila['libro']] = $fila['idtranglob'];
    if(!isset($encabezaProducto)||$encabezaProducto<>$fila['codigo']){
      if(isset($encabezaProducto)&&$encabezaProducto<>$fila['codigo']){
        $tabla .= "<tr class='info comisionEncabezado'><td colspan=4>Subtotal <b>{$_SESSION['productosTransporte'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaIVA[$encabezaProducto], 2, ',', '.')."</td><td></td></tr>";
      }
      $encabezaProducto = $fila['codigo'];
      $sumaNeto[$fila['codigo']] = $sumaIVA[$fila['codigo']] = $sumaPrecio[$fila['codigo']] = 0;
      $tabla .= "<tr class='info comisionEncabezado viaje'><td><b>{$_SESSION['productosTransporte'][$fila['codigo']]}</b></td><td colspan='8' style='text-align:right'></td></tr>";
      unset($encabezaSocio);
    }
    if(!isset($encabezaSocio)||$encabezaSocio<>$fila['clienteCodigo']){
      $encabezaSocio = $fila['clienteCodigo'];
      $td = "$fila[clienteCodigo] - <b>".utf8_encode($fila['clienteNombre'])."</b>";
    } else $td="    \"       \"";
    $signo = (($fila['comprobant']=='NOTA DE CREDITO')?-1:1);
    $fecha_asie=substr($fila['fecha_asie'],0,10);
    $tabla .= "<tr class='viaje'><td>$td</td><td>{$fecha_asie}</td><td>$fila[comprobant] $fila[sucursal]-$fila[numero]</td><td class='text-right'>".number_format($signo*$fila['cantidad'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['precio_vta'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['neto_grava'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['iva'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['percepib'], 2, ',', '.')."</td></tr>";
    $sumaNeto[$fila['codigo']] = $sumaNeto[$fila['codigo']]+$signo*$fila['neto_grava'];
    $sumaIVA[$fila['codigo']] = $sumaIVA[$fila['codigo']]+$signo*$fila['iva'];
    $sumaPrecio[$fila['codigo']] = $sumaPrecio[$fila['codigo']]+$signo*$fila['precio_vta'];
    $totalNeto = $totalNeto+$signo*$fila['neto_grava'];
    $totalIVA = $totalIVA+$signo*$fila['iva'];
    $totalPrecio = $totalPrecio+$signo*$fila['precio_vta'];
    
    if(isset($tablaEncabezado)){
      $tabla.=$tablaEncabezado;
      unset($tablaEncabezado);
    }
  }
}
if(isset($encabezaProducto)){
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=4>Subtotal <b>{$_SESSION['productosTransporte'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaPrecio[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td><td class='text-right'>$".number_format($sumaIVA[$encabezaProducto], 2, ',', '.')."</td><td></td></tr>";
  $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='4' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td><td class='text-right'><b>$".number_format($totalNeto, 2, ',', '.')."</b></td><td class='text-right'><b>$".number_format($totalIVA, 2, ',', '.')."</b></td><td></td></tr>";
} else {
  $tabla .= "<tr class='info comisionEncabezado'><td colspan=5><b>No hay datos</b></td><td></td><td></td><td></td></tr>";
}
echo $tabla;
?>
