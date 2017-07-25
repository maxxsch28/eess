<?php
// setupProductosPorClientes.php
// Muestra IVA Ventas completo
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';
$cuentasGastosCargadora = '340010, 610111, 311308, 312204, 340046';
$cuentasSueldosCargadora = '213202, 213201';
$productosCargadora = "26, 27, 103, 104, 105";
$productosFacturadosCargadora = "26, 27, 103, 104, 105, 128";


if(!isset($_SESSION['cuentasSueldosCargadora'])||1){
  $sqlProductos = "select nombre, codigo from dbo.PLANCUEN WHERE codigo in ($cuentasSueldosCargadora)";
  $stmt = odbc_exec2($mssql2, $sqlProductos, __LINE__, __FILE__);
  while($producto = sqlsrv_fetch_array($stmt)){
    $_SESSION['cuentasSueldosCargadora'][$producto['codigo']] = trim($producto['nombre']);
  }
}
if(!isset($_SESSION['cuentasGastosCargadora'])||1){
  $sqlProductos = "select nombre, codigo from dbo.PLANCUEN WHERE codigo in ($cuentasGastosCargadora)";
  $stmt = odbc_exec2($mssql2, $sqlProductos, __LINE__, __FILE__);
  while($producto = sqlsrv_fetch_array($stmt)){
    $_SESSION['cuentasGastosCargadora'][$producto['codigo']] = trim($producto['nombre']);
  }
}
if(!isset($_SESSION['productosCargadora'])||1){
  $sqlProductos = "select nombre, codigo from dbo.PRODUCTO WHERE codigo in ($productosFacturadosCargadora)";
  $stmt = odbc_exec2($mssql2, $sqlProductos, __LINE__, __FILE__);
  while($producto = sqlsrv_fetch_array($stmt)){
    $_SESSION['productosCargadora'][$producto['codigo']] = trim($producto['nombre']);
  }
}
if(isset($_POST['mes'])&&is_numeric($_POST['mes'])&&strlen($_POST['mes'])==6){
    $mes=substr($_POST['mes'],4,2);
    $anio=substr($_POST['mes'],0,4);
  } 

$soloExternos = (isset($_POST['soloExternos']))?" AND tipoviaje=$_POST[soloExternos]":"";
if(isset($_POST['mes'])&&is_numeric($_POST['mes'])&&strlen($_POST['mes'])==6){
  $anio = substr($_POST['mes'], 0, 4);
  $mes = substr($_POST['mes'], 5, 2);
  $rangoFin = $anio."-$mes-31";
} elseif(strlen($_POST['mes'])==4){
  $anio = substr($_POST['mes'], 0, 4);
  $mes = '01';
  $rangoFin = $anio.'-12-31';
}

if(!isset($_POST['soloExternos'])||$_POST['soloExternos']==0){
  $sqlIngresos = "  select a.comprobant, a.sucursal, a.numero, producto, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, monto, a.iva, a.cuentacont, b.cliente, emision, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, a.idtranglob, c.codigo from dbo.detavtas as a, dbo.histoven as b, dbo.clientes as c WHERE b.emision>='$anio-$mes-01' AND b.emision<='$rangoFin' AND a.idtranglob=b.idtranglob AND a.numero=b.numero AND a.producto IN ($productosFacturadosCargadora)  and b.cliente=c.codigo ORDER BY producto, nombre;";
  
  $sqlGastos = "select a.libro, fecha, cuentacont as concepto, debe, haber,  detalle, a.idtranglob from dbo.asiecont as a,dbo.concasie as b where cuentacont in ($cuentasGastosCargadora) and a.asiento=b.asiento and a.idtranglob=b.idtranglob and fecha>='$anio-$mes-01' AND fecha<='$rangoFin' order by cuentacont asc, fecha asc";
  
  $sqlSueldos = "select a.libro, fecha, cuentacont as concepto, .35*debe as debe, 0.35*haber as haber,  detalle, a.idtranglob from dbo.asiecont as a,dbo.concasie as b where cuentacont in ($cuentasSueldosCargadora) and a.asiento=b.asiento and a.idtranglob=b.idtranglob and fecha>='$anio-$mes-01' AND fecha<='$rangoFin' AND haber=0 order by cuentacont asc, fecha asc";
  
} elseif($_POST['soloExternos']==1){
  // Solo Fleteros
  $sqlGastos = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detaosvt.comprobant, dbo.detaosvt.sucursal, dbo.detaosvt.numero, producto.concepto, producto.detalle, dbo.detaosvt.precio_vta, monto, dbo.detaosvt.importe, dbo.detaosvt.iva, dbo.detaosvt.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.concepto as clienteCodigo, fecha_asie, dbo.detaosvt.cantidad FROM  dbo.concasie, dbo.detaosvt, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.concepto AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detaosvt.producto=dbo.PRODUCTO.concepto AND dbo.concasie.idtranglob=dbo.detaosvt.idtranglob ORDER BY producto;";
} else { 
  // Solo Clientes
  $sqlGastos = "SELECT libro, concasie.sucursal, asiento, dbo.concasie.detalle, dbo.concasie.idtranglob, dbo.detavtas.comprobant, dbo.detavtas.sucursal, dbo.detavtas.numero, producto.concepto, producto.detalle, dbo.detavtas.precio_vta, monto, dbo.detavtas.importe, dbo.detavtas.iva, dbo.detavtas.ingbruto, aliporiva, neto_grava, neto_nogra, total_item, percepib, dbo.clientes.nombre as clienteNombre, dbo.producto.cuentacont, dbo.clientes.concepto as clienteCodigo, fecha_asie, dbo.detavtas.cantidad, producto FROM dbo.concasie, dbo.detavtas, dbo.PRODUCTO, dbo.histvtam, dbo.clientes WHERE DBO.histvtam.idtranglob=dbo.concasie.idtranglob and dbo.histvtam.cliente=dbo.clientes.concepto AND datepart(year, dbo.concasie.fecha_asie)='$anio' AND datepart(month, dbo.concasie.fecha_asie)='$mes' AND dbo.detavtas.producto=dbo.PRODUCTO.concepto AND dbo.concasie.idtranglob=dbo.detavtas.idtranglob ORDER BY producto;";
}

switch($_POST['que']){
  case 'gastos':
  // GASTOS
  ChromePhp::log($sqlGastos);
  $stmt = odbc_exec2($mssql2, $sqlGastos, __LINE__, __FILE__);
  $tabla = "";$a=0;
  $totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas =  0;
  $comision=array();
  $totalAComisionar = array();
  $comprobantePorLibro = array();
  while($fila = sqlsrv_fetch_array($stmt)){
    // evita duplicación por comprobantes que aparecen en ambos libros
    if(isset($comprobantePorLibro[$fila['idtranglob']]['INGRESOS'])&&$fila['libro']=='VENTAS'){
      //evita duplicado
    } else {
      $comprobantePorLibro[$fila['idtranglob']][$fila['libro']] = $fila['idtranglob'];
      if(!isset($encabezaProducto)||$encabezaProducto<>$fila['concepto']){
        if(isset($encabezaProducto)&&$encabezaProducto<>$fila['concepto']){
          $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['cuentasGastosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
        }
        $encabezaProducto = $fila['concepto'];
        $sumaNeto[$fila['concepto']] = $sumaIVA[$fila['concepto']] = $sumaPrecio[$fila['concepto']] = 0;
        $tabla .= "<tr class='info comisionEncabezado viaje'><td colspan='2' ><b>{$_SESSION['cuentasGastosCargadora'][$fila['concepto']]}</b></td><td style='text-align:right'></td></tr>";
        unset($encabezaSocio);
      }
      $signo = (($fila['haber']>0)?-1:1);
      $fecha_asie=$fila['fecha']->format('d/m/Y');
      $fila['detalle'] = trim(str_replace('Proveedor:', '', $fila['detalle']));
      $tabla .= "<tr class='viaje'><td>{$fecha_asie}</td><td>$fila[detalle]</td><td class='text-right'>$".number_format($signo*($fila['debe']+$fila['haber']), 2, ',', '.')."</td></tr>";
      $sumaNeto[$fila['concepto']] = $sumaNeto[$fila['concepto']]+$signo*($fila['debe']+$fila['haber']);
      $totalPrecio = $totalPrecio + $signo*($fila['debe']+$fila['haber']);
      if(isset($tablaEncabezado)){
        $tabla.=$tablaEncabezado;
        unset($tablaEncabezado);
      }
    }
  }
  if(isset($encabezaProducto)){
    $_SESSION['cargadoraTotalGastos'] = $totalPrecio;
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['cuentasSueldosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
    $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='2' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td></tr>";
  } else {
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=3><b>No hay datos</b></td></tr>";
  }
  break;
  case 'sueldos':
  // SUELDOS
  ChromePhp::log($sqlSueldos);
  $stmt = odbc_exec2($mssql2, $sqlSueldos, __LINE__, __FILE__);
  $tabla = "";$a=0;
  $totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas =  0;
  $comision=array();
  $totalAComisionar = array();
  $comprobantePorLibro = array();
  while($fila = sqlsrv_fetch_array($stmt)){
    // evita duplicación por comprobantes que aparecen en ambos libros
    if(isset($comprobantePorLibro[$fila['idtranglob']]['INGRESOS'])&&$fila['libro']=='VENTAS'){
      //evita duplicado
    } else {
      $comprobantePorLibro[$fila['idtranglob']][$fila['libro']] = $fila['idtranglob'];
      if(!isset($encabezaProducto)||$encabezaProducto<>$fila['concepto']){
        if(isset($encabezaProducto)&&$encabezaProducto<>$fila['concepto']){
          $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['cuentasSueldosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
        }
        $encabezaProducto = $fila['concepto'];
        $sumaNeto[$fila['concepto']] = $sumaIVA[$fila['concepto']] = $sumaPrecio[$fila['concepto']] = 0;
        $tabla .= "<tr class='info comisionEncabezado viaje'><td colspan='2' ><b>{$_SESSION['cuentasSueldosCargadora'][$fila['concepto']]}</b></td><td style='text-align:right'></td></tr>";
        unset($encabezaSocio);
      }
      $signo = (($fila['haber']>0)?-1:1);
      $fecha_asie=$fila['fecha']->format('d/m/Y');
      $fila['detalle'] = trim(str_replace('Proveedor:', '', $fila['detalle']));
      $tabla .= "<tr class='viaje'><td>{$fecha_asie}</td><td>$fila[detalle]</td><td class='text-right'>$".number_format($signo*($fila['debe']+$fila['haber']), 2, ',', '.')."</td></tr>";
      $sumaNeto[$fila['concepto']] = $sumaNeto[$fila['concepto']]+$signo*($fila['debe']+$fila['haber']);
      $totalPrecio = $totalPrecio + $signo*($fila['debe']+$fila['haber']);
      if(isset($tablaEncabezado)){
        $tabla.=$tablaEncabezado;
        unset($tablaEncabezado);
      }
    }
  }
  if(isset($encabezaProducto)){
    $_SESSION['cargadoraTotalSueldos'] = $totalPrecio;
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['cuentasSueldosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
    $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='2' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td></tr>";
  } else {
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=3><b>No hay datos</b></td></tr>";
  }
  break;
  case 'ingresos':
  // Ingresos
  ChromePhp::log($sqlIngresos);
  $stmt = odbc_exec2($mssql2, $sqlIngresos, __LINE__, __FILE__);
  $tabla = "";$a=0;
  $totalB = 0;
  $totalA = 0;
  unset($encabezaProducto);
  $totalNeto = $totalPrecio = $cantidadClientes = $totalCantidad = 0;
  $comision=array();
  $totalAComisionar = array();
  $comprobantePorLibro = array();
  while($fila = sqlsrv_fetch_array($stmt)){
    // evita duplicación por comprobantes que aparecen en ambos libros
    if(isset($comprobantePorLibro[$fila['idtranglob']]['INGRESOS'])&&$fila['libro']=='VENTAS'){
      //evita duplicado
    } else {
      if(!isset($encabezaProducto)||$encabezaProducto<>$fila['producto']){
        if(isset($encabezaProducto)&&$encabezaProducto<>$fila['producto']){
          $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['productosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
        }
        $encabezaProducto = $fila['producto'];
        $sumaNeto[$fila['producto']] = $sumaIVA[$fila['producto']] = $sumaPrecio[$fila['producto']] = 0;
        $tabla .= "<tr class='info comisionEncabezado viaje'><td colspan='2' ><b>{$_SESSION['productosCargadora'][$fila['producto']]}</b></td><td style='text-align:right'></td></tr>";
        unset($encabezaSocio);
      }
      $signo = (($fila['comprobant']=='FACTURA' || $fila['comprobant']=='NOTA DE DEBITO')?1:-1);
      $fecha_asie=$fila['emision']->format('d/m/Y');
      $fila['detalle'] = trim(str_replace($_SESSION['productosCargadora'][$encabezaProducto].' -', '', $fila['detalle']));
      $fila['detalle'] = trim(str_replace($_SESSION['productosCargadora'][$encabezaProducto], '', $fila['detalle']));
      $tabla .= "<tr class='viaje'><td>{$fecha_asie}</td><td>$fila[detalle], $fila[nombre]</td><td class='text-right'>$".number_format($signo*($fila['monto']), 2, ',', '.')."</td></tr>";
      $sumaNeto[$fila['producto']] = $sumaNeto[$fila['producto']]+$signo*($fila['monto']);
      $totalPrecio = $totalPrecio +$signo*($fila['monto']);
      if(isset($tablaEncabezado)){
        $tabla.=$tablaEncabezado;
        unset($tablaEncabezado);
      }
    }
  }
  if(isset($encabezaProducto)){
    $_SESSION['cargadoraTotalIngresos'] = $totalPrecio;
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=2>Subtotal <b>{$_SESSION['productosCargadora'][$encabezaProducto]}</b></td><td class='text-right'>$".number_format($sumaNeto[$encabezaProducto], 2, ',', '.')."</td></tr>";
    $tabla .= "<tr class='info comisionEncabezado bg-warning'><td colspan='2' class='text-right'><b>Total</b></td><td class='text-right'><b>$".number_format($totalPrecio, 2, ',', '.')."</b></td></tr>";
  } else {
    $tabla .= "<tr class='info comisionEncabezado'><td colspan=3><b>No hay datos</b></td></tr>";
  }
  break;
  case 'resultado':
  if(isset($_SESSION['cargadoraTotalGastos'])&&isset($_SESSION['cargadoraTotalIngresos'])&&isset($_SESSION['cargadoraTotalSueldos'])){
    $tabla = "<tr><td colspan='2'> + Ingresos</td><td><b>$".number_format($_SESSION['cargadoraTotalIngresos'], 2, ',', '.')."</b></td></tr>";
    $tabla .= "<tr><td colspan='2'> - Gastos</td><td><b>-$".number_format($_SESSION['cargadoraTotalGastos'], 2, ',', '.')."</b></td></tr>";
    $tabla .= "<tr><td colspan='2'> - Sueldos</td><td><b>-$".number_format($_SESSION['cargadoraTotalSueldos'], 2, ',', '.')."</b></td></tr>";
    $neto = $_SESSION['cargadoraTotalIngresos']-$_SESSION['cargadoraTotalGastos']-$_SESSION['cargadoraTotalSueldos'];
    $tabla .= "<tr><td colspan='2'><b>Resultado</b></td><td><b>$".number_format($neto, 2, ',', '.')."</b></td></tr>";
    $tabla .= "<tr><td colspan='2'><b>Margen</b></td><td><b>".number_format($neto/$_SESSION['cargadoraTotalIngresos'], 2, ',', '.')."%</b></td></tr>";
  
  } else {
    $tabla ="<tr><td colspan='3' class='bg-danger'><b>Por favor refrescar página</b></td></tr>";
  }
  break;
}

echo $tabla;
?>
