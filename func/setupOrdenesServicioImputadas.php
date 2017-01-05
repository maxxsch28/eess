<?php
// listaUltimasFacturasSocio.php
// muestra en una tabla todas las facturas de ventas y compras cargadas para el socio determinado
include('../include/inicia.php');
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
  $sql = "SELECT dbo.ordservi.sucursal_e AS sucOS, dbo.ordservi.numero as numOS, dbo.ordservi.fecha, dbo.ordservi.importe, dbo.ordservi.reten_ib, dbo.impupagf.sucursal_e, dbo.impupagf.pago, dbo.ordservi.observacio, dbo.ordservi.numeinter, dbo.pagos.fecha AS fechaOrden FROM dbo.ordservi, dbo.detaorse, dbo.impupagf, dbo.pagos where dbo.impupagf.pago=dbo.pagos.numero AND dbo.ordservi.numero=dbo.detaorse.ordenservi and dbo.ordservi.proveedor=1 AND tipoadelan in (2, 25) and dbo.ordservi.numero = dbo.impupagf.numero and dbo.ordservi.sucursal_e = dbo.impupagf.sucursal_e and dbo.detaorse.sucursal_e = dbo.impupagf.sucursal_e and dbo.pagos.sucursal = dbo.impupagf.sucursal_e AND dbo.impupagf.fletero = dbo.ordservi.fletero and dbo.ordservi.fletero=$_POST[idSocio] ORDER BY fechaOrden Desc, sucOS, numOS;";
  // falta filtrar por tiempos
  fb($sql);
  $stmt = sqlsrv_query( $mssql2, $sql);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sql<br/>";
      die( print_r( sqlsrv_errors(), true));
  } else {
    $tabla = "";$a=0;$q=0;
    if(sqlsrv_has_rows( $stmt )){
      while($fila = sqlsrv_fetch_array($stmt)){
        $ultimaOrden = "Orden de pago $fila[sucursal_e]-$fila[pago] del ".date_format($fila['fechaOrden'],'d/m/Y');
        if(!isset($ordenDePago)||$ordenDePago<>$fila['pago']){
          if(isset($sumaOrden)&&$sumaOrden>0){
            // termino orden anterior
            $tabla .= "<tr><td colspan='5' class='alert alert-danger'><center><strong>Orden de pago $sucOrdenDePago-$ordenDePago del ".date_format($fechaOrdenDePago,'d/m/Y')." | Total imputado \$ $sumaOrden</strong></center></td></tr><tr><td colspan=5>&nbsp;</td></tr>";
          }
          $ordenDePago = $fila['pago'];
          $sucOrdenDePago = $fila['sucursal_e'];
          $fechaOrdenDePago = $fila['fechaOrden'];
          $sumaOrden = 0;
        }
        $sumaOrden = $sumaOrden + $fila['importe'];
        $tabla .= "<tr id='g$fila[sucOS]$fila[numOS]'><td>".date_format($fila['fecha'],'d/m/Y')."</td><td>$fila[sucOS]-$fila[numOS]</td><td>$fila[observacio] / $fila[numeinter]</td><td>$ ".sprintf("%01.2f", $fila['importe'])."</td><td>$ ".sprintf("%01.2f", $fila['reten_ib'])."</td></tr>";
      }
      if(isset($sumaOrden)&&$sumaOrden>0){
        // termino orden anterior
        $tabla .= "<tr><td colspan='5'class='alert alert-danger' ><center><strong>$ultimaOrden | Total imputado \$ $sumaOrden</strong></center></td></tr>";
      }
    } else {
      $tabla = "<tr><td colspan='5' class='alert alert-danger'><center><strong>Este socio no posee documentos cargados</strong></center></td></tr>";
    }
    echo $tabla;
    //print_r($_SESSION['datosSocio']);
  }
}
?>
