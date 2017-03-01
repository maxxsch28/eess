<?php
// listaUltimasFacturasSocio.php
// muestra en una tabla todas las facturas de ventas y compras cargadas para el socio determinado
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
 //print_r($_POST);
 // $array=array();
 
 /*
  1     idComprobanteVenta 
  2 	idSocio 
  3 	idTercero
  4 	venta
  5 	pv
  6 	numero
  7 	neto
  8 	iva10
  9 	iva21
  10 	iva27
  11 	nogravado
  12 	percIVA
  13 	percIIBB
  14 	percGAN
*/	

	
if(isset($_POST['idSocio'])){ 
  $sql = "SELECT dbo.ordservi.sucursal_e AS sucos, dbo.ordservi.numero as numos, dbo.ordservi.fecha, dbo.ordservi.importe, dbo.ordservi.reten_ib, dbo.impupagf.sucursal_e, dbo.impupagf.pago, dbo.ordservi.observacio Collate SQL_Latin1_General_CP1253_CI_AI as observacio, dbo.ordservi.numeinter Collate SQL_Latin1_General_CP1253_CI_AI as numeinter, dbo.pagos.fecha AS fechaorden FROM dbo.ordservi, dbo.detaorse, dbo.impupagf, dbo.pagos where dbo.impupagf.pago=dbo.pagos.numero AND dbo.ordservi.numero=dbo.detaorse.ordenservi and dbo.ordservi.proveedor=1 AND tipoadelan in (2, 25) and dbo.ordservi.numero = dbo.impupagf.numero and dbo.ordservi.sucursal_e = dbo.impupagf.sucursal_e and dbo.detaorse.sucursal_e = dbo.impupagf.sucursal_e and dbo.pagos.sucursal = dbo.impupagf.sucursal_e AND dbo.impupagf.fletero = dbo.ordservi.fletero and dbo.ordservi.fletero=$_POST[idSocio] ORDER BY fechaorden Desc, sucos, numos;";
  // falta filtrar por tiempos
  fb($sql);
  $stmt = odbc_exec2( $mssql2, $sql, __LINE__, __FILE__);
  $tabla = "";$a=$q=0;
  while($fila = odbc_fetch_array($stmt)){fb($fila);
    $fechaOrden = $fila['fechaorden'];
    $fecha = substr($fila['fecha'],0,10);
    $ultimaOrden = "Orden de pago $fila[sucursal_e]-$fila[pago] del ".$fecha;
    if(!isset($ordenDePago)||$ordenDePago<>$fila['pago']){
      if(isset($sumaOrden)&&$sumaOrden>0){
        // termino orden anterior
        $tabla .= "<tr><td colspan='5' class='alert alert-danger'><center><strong>Orden de pago $sucOrdenDePago-$ordenDePago del $fechaOrdenDePago | Total imputado \$ $sumaOrden</strong></center></td></tr><tr><td colspan=5>&nbsp;</td></tr>";
      }
      $ordenDePago = $fila['pago'];
      $sucOrdenDePago = $fila['sucursal_e'];
      $fechaOrdenDePago = $fechaOrden;
      $sumaOrden = 0;
    }
    $sumaOrden = $sumaOrden + $fila['importe'];
    $tabla .= "<tr id='g$fila[sucos]$fila[numos]'><td>$fecha</td><td>$fila[sucos]-$fila[numos]</td><td>$fila[observacio] / $fila[numeinter]</td><td>$ ".sprintf("%01.2f", $fila['importe'])."</td><td>$ ".sprintf("%01.2f", $fila['reten_ib'])."</td></tr>";
    $a++;
  }
  if(isset($sumaOrden)&&$sumaOrden>0&&$a>1){
    // termino orden anterior
    $tabla .= "<tr><td colspan='5'class='alert alert-danger' ><center><strong>$ultimaOrden | Total imputado \$ $sumaOrden</strong></center></td></tr>";
  } elseif($a==0) {
    $tabla = "<tr><td colspan='5' class='alert alert-danger'><center><strong>Este socio no posee documentos cargados</strong></center></td></tr>";
  }
  echo $tabla;
  //print_r($_SESSION['datosSocio']);
}
?>
