<?php
// listaUltimasFacturasSocio.php
// muestra en una tabla todas las facturas de ventas y compras cargadas para el socio determinado
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
 print_r($_POST);
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
if(isset($_POST['comprimido'])){
  $_POST['idSocio'] = substr($_POST['idSocio'],8);
  
}


// 	and dbo.ordservi.sucursal_e = dbo.impupagf.sucursal_e
if(isset($_POST['idSocio'])){ 
  $sql = "SELECT DISTINCT dbo.ordservi.sucursal_e AS sucos, dbo.ordservi.numero as numos, dbo.ordservi.fecha, dbo.ordservi.importe, dbo.ordservi.reten_ib, dbo.impupagf.sucursal_e, dbo.impupagf.pago, dbo.ordservi.observacio, dbo.ordservi.numeinter, dbo.pagos.fecha AS fechaorden, dbo.ordservi.proveedor FROM dbo.ordservi, dbo.detaorse, dbo.impupagf, dbo.pagos where dbo.impupagf.pago=dbo.pagos.numero AND dbo.ordservi.numero=dbo.detaorse.ordenservi and dbo.ordservi.proveedor IN (1, 321) AND dbo.detaorse.tipoadelan in (2, 3, 25, 24) and dbo.ordservi.numero = dbo.impupagf.numero  and dbo.detaorse.sucursal_e = dbo.impupagf.sucursal_e and dbo.pagos.sucursal = dbo.impupagf.sucursal_e AND dbo.impupagf.fletero = dbo.ordservi.fletero and dbo.ordservi.fletero=$_POST[idSocio]  AND totadelant=dbo.impupagf.importe ORDER BY fechaorden Desc, sucos, numos;";
  $sql = "SELECT DISTINCT dbo.ordservi.sucursal_e AS sucos, dbo.ordservi.numero as numos, dbo.ordservi.fecha, dbo.ordservi.importe, dbo.ordservi.reten_ib, dbo.impupagf.sucursal_e, dbo.impupagf.pago, dbo.ordservi.observacio, dbo.ordservi.numeinter, dbo.pagos.fecha AS fechaorden, dbo.ordservi.proveedor FROM dbo.ordservi, dbo.detaorse, dbo.impupagf, dbo.pagos where dbo.impupagf.pago=dbo.pagos.numero AND dbo.ordservi.numero=dbo.detaorse.ordenservi and dbo.ordservi.proveedor IN (1, 321) AND dbo.detaorse.tipoadelan in (2, 3, 25, 24) and dbo.ordservi.numero = dbo.impupagf.numero  and dbo.detaorse.sucursal_e = dbo.ordservi.sucursal_e and dbo.pagos.sucursal = dbo.impupagf.sucursal_e AND dbo.impupagf.fletero = dbo.ordservi.fletero and dbo.ordservi.fletero=$_POST[idSocio]  AND totadelant=dbo.impupagf.importe ORDER BY fechaorden Desc, sucos, numos;";
  // falta filtrar por tiempos
  ChromePhp::log($sql);
  $stmt = odbc_exec2( $mssql2, $sql, __LINE__, __FILE__);
  $tabla = "";$a=$q=0;
  while($fila = sqlsrv_fetch_array($stmt)){
    //ChromePhp::log($fila);
    $fechaOrden = $fila['fechaorden']->format('d/m/Y');
    $fecha = $fila['fecha']->format('d/m/Y');
    $ultimaOrden = "Orden de pago $fila[sucursal_e]-$fila[pago] del ".$fecha;
    if(!isset($ordenDePago)||$ordenDePago<>$fila['pago']){
      if(isset($sumaOrden)&&$sumaOrden>0){
        // termino orden anterior
        // chequeo si recibió variable período, y si coincide con el mes de este descuento, en ese caso en vez de alert-danger por alert-success
        
        // TODO
        if(isset($_POST['periodo']) && $_POST['periodo'] == $mesOrden){
          $alert = "success";
        } else {
          $alert = "danger";
        }
        
         $tabla .= "<tr><td colspan='6' class='alert alert-$alert'><center><strong>Orden de pago $sucOrdenDePago-$ordenDePago del $fechaOrdenDePago | Total \$ $sumaOrden</strong></center></td></tr><tr><td colspan=6>&nbsp;</td></tr>";
      }
      $ordenDePago = $fila['pago'];
      $sucOrdenDePago = $fila['sucursal_e'];
      $fechaOrdenDePago = $fechaOrden;
      $mesOrden = $fila['fechaorden']->format('Ym');
      $sumaOrden = 0;
    }
    $sumaOrden = $sumaOrden + $fila['importe'];
    
    $tabla .= "<tr id='g$fila[sucos]$fila[numos]'><td>$fecha</td>".((!isset($_POST['comprimido']))?"<td>$fila[sucos]-$fila[numos]</td>":"")."<td>$fila[observacio] / $fila[numeinter]</td><td>$ ".sprintf("%01.2f", $fila['importe'])."</td><td>$ ".sprintf("%01.2f", $fila['reten_ib'])."</td>".((!isset($_POST['comprimido']))?"<td>$fila[proveedor]</td>":"")."</tr>";
    $a++;
  }
  if(isset($sumaOrden)&&$sumaOrden>0&&$a>1){
    // termino orden anterior
    $tabla .= "<tr><td colspan='6'class='alert alert-danger' ><center><strong>$ultimaOrden | Total \$ $sumaOrden</strong></center></td></tr>";
  } elseif($a==0) {
    $tabla = "<tr><td colspan='6' class='alert alert-danger'><center><strong>Este socio no posee documentos cargados</strong></center></td></tr>";
  }
  echo $tabla;
  //print_r($_SESSION['datosSocio']);
}
?>
