<?php
// listaUltimasFacturasSocio.php
// muestra en una tabla todas las facturas de ventas y compras cargadas para el socio determinado
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
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
  $sql = "SELECT * FROM iva_comprobantes, iva_terceros WHERE iva_comprobantes.idTercero=iva_terceros.idtercero AND idSocio=$_POST[idSocio] AND periodo=$_POST[periodo] ORDER BY venta, fecha, pv, numero, razonSocial;";
  //echo $sql;
  $result = $mysqli->query($sql);
  $tabla = "";$a=0;$q=0;
  if($result->num_rows>0){
  while($fila = $result->fetch_assoc()){
    if(!isset($encabezado)||$encabezado<>$fila['venta']){
      $encabezado = $fila['venta'];
      if($fila['venta']==1){
        $tabla .= "<tr><td colspan='13' class='alert alert-success'><center><strong>VENTAS</strong></center></td></tr>";
      } else {
        $tabla .= "<tr><td colspan='13' class='alert alert-success'><center><strong>COMPRAS</strong></center></td></tr>";
      }
    }
    $ignorado = ($fila['ignorado']==1)?' disabled':'';
    $suma = $fila['subtotal']+$fila['iva21']+$fila['iva27']+$fila['iva10']+$fila['nogravado']+$fila['percIIBB']+$fila['percIVA']+$fila['percGAN'];
    $error = (round($suma,1)<>round($fila['total'],1))?"<span class='glyphicon glyphicon-exclamation-sign  alert-danger' aria-hidden='true' title='$$suma'></span> ":"";
    $errorTR = (round($suma,1)<>round($fila['total'],1))?"alert-danger":"";
    $nc = ($fila['tipoDocumento']=='NCA')?'-':'';
    $tabla .= "<tr id='g$fila[idComprobanteVenta]' class='{$errorTR}{$ignorado}'><td>$fila[fecha]</td>
    <td>$fila[razonSocial] ($fila[cuit])</td><td><b>$fila[tipoDocumento] $fila[pv]-$fila[numero]</b></td><td>$nc".sprintf("%01.2f", $fila['subtotal'])."</td><td>$nc".sprintf("%01.2f", $fila['iva21'])."</td><td>$nc".sprintf("%01.2f", $fila['iva27'])."</td><td>$nc".sprintf("%01.2f", $fila['iva10'])."</td><td>$nc".sprintf("%01.2f", $fila['nogravado'])."</td><td>$nc".sprintf("%01.2f", $fila['percIIBB'])."</td><td>$nc".sprintf("%01.2f", $fila['percIVA'])."</td><td>$nc".sprintf("%01.2f", $fila['percGAN'])."</td><td>$nc".sprintf("%01.2f", $fila['total'])."</td><td>$error<span class='glyphicon glyphicon-random' title='Trasladar mes siguiente'>&nbsp;</span><span class='glyphicon glyphicon-remove' title='Ignorar / Eliminar'></span></td></tr>";
  }
  } else {
    $tabla = "<tr><td colspan='13' class='alert alert-danger'><center><strong>Este socio no posee documentos cargados</strong></center></td></tr>";
  }
  $result->close();
  echo $tabla;
  //print_r($_SESSION['datosSocio']);
}
?>
