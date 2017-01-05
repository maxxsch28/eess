<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';

if(!isset($_SESSION['productosTransporte'])){
  $sqlProductos = "SELECT codigo, nombre FROM dbo.PRODUCTO order by codigo";
  $stmt = sqlsrv_query( $mssql2, $sqlProductos);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlProductos<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  while($producto = sqlsrv_fetch_array($stmt)){
    $_SESSION['productosTransporte'][$producto['codigo']] = $producto['nombre'];
  }
}


$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])){
  $periodo = "";
  $anio = substr($_POST['mes'], 0, 4);
  $mes = substr($_POST['mes'], 5, 2);
  if(!isset($_POST['soloExternos'])||$_POST['soloExternos']==0){
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad, producto FROM dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob UNION ALL SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.codigo, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detavtas.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY producto;";
  } elseif($_POST['soloExternos']==1){
    // Solo Fleteros
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.codigo, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad FROM  dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detaosvt.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob ORDER BY producto;";
  } else { 
    // Solo Clientes
    $sqlClientes = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.codigo, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.codigo as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.codigo AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detavtas.producto=dbo.PRODUCTO.codigo AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY producto;";
  }
  fb($sqlClientes);
} else {
  $limit=' LIMIT 5';
  $sqlClientes = "select clientes.idCliente as idC, clientes.socio, facturas.idFacturaRecibida, sum(usoRed) AS uR, sum( interesMora ) AS iM, sum( ingresosBrutos ) AS iB,facturaB, (SELECT count(celular) FROM `movistar.celulares` WHERE idCliente=idC) AS q,numeroFactura, nombre, codigo, periodo, facturas.idCliente, sum(cargoFijo) as cF, cargoFijo, (SUM( cargoFijo ) - cargoFijo) as Dif, sum(cargoVariable) as cV, sum(cargoBB) as cB, sum(IVA21) as IV1, sum(IVA27) as IV7, sum(otros) as o, sum(impuestosInternos) as iI, empleado from `movistar.facturasrecibidas` as facturas, `movistar.facturasitems` as items, `movistar.clientes` as clientes WHERE facturas.idFacturaRecibida=items.idFacturaRecibida and clientes.idCliente=facturas.idCliente group by facturas.idFacturaRecibida order by facturas.idFacturaRecibida desc $limit";
}


fb($sqlClientes);


$stmt = sqlsrv_query( $mssql2, $sqlClientes);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlClientes<br/>";
     die( print_r( sqlsrv_errors(), true));
}
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
    $tabla .= "<tr class='viaje'><td>$td</td><td>{$fila['fecha_asie']->format('d/m/Y')}</td><td>$fila[comprobant] $fila[sucursal]-$fila[numero]</td><td class='text-right'>".number_format($signo*$fila['cantidad'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['precio_vta'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['neto_grava'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['iva'], 2, ',', '.')."</td><td class='text-right'>$".number_format($signo*$fila['percepib'], 2, ',', '.')."</td></tr>";
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
