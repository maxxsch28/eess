<?php
// setupProductosPorClientes.php
// Muestra IVA Ventas completo
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';



if(!isset($_POST['pedido'])){
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
      $sqlClientes = "SELECT CONCAT(a.sucursal_e, '-', a.numero) as viaje, b.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, a.fechacarga, a.loc_origen, a.mercaderia, a.cantviajes, a.detalle  Collate SQL_Latin1_General_CP1253_CI_AI as detalle FROM dbo.pediviaj a, dbo.clientes b WHERE a.cliente=b.codigo AND a.fechacarga>=DateAdd(month, -1, Convert(date, GetDate())) ORDER BY a.fechacarga DESC";
        ChromePhp::log('2');
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
  // CONCAT(a.sucursal_e, '-', a.numero) as viaje, b.nombre, a.fechacarga, a.loc_origen, a.mercaderia, a.cantviajes
  while($fila = sqlsrv_fetch_array($stmt)){
      $tabla .= "<tr class='selViaje' id='$fila[viaje]'><td>$fila[viaje]</td><td>".$fila['fechacarga']->format('d/m/y')."</td><td><b>$fila[nombre]</b><br>$fila[loc_origen]</td><td>$fila[mercaderia]</td><td>$fila[cantviajes]</td></tr>";
  }
} else {
  // levanta los datos de los camiones involucrados
  $part = explode('-', $_POST['pedido']);
  $sucurpedi = $part[0];
  $pedido = $part[1];
  $sqlClientes = "SELECT b.sucursal_e, b.parte, c.nombre, (CASE WHEN c.cuit<>d.cuil THEN d.nombre ELSE '' END) AS chofer, b.kilosreal, salida FROM dbo.rpedpart a, dbo.partes b, dbo.fleteros c, dbo.choferes d WHERE pedido='$pedido' AND sucurpedi='$sucurpedi' AND a.sucurpart=b.sucursal_e AND a.parte=b.parte AND b.chofer=d.codigo AND b.fletero=c.fletero";
  ChromePhp::log($sqlClientes);
  $stmt = odbc_exec2($mssql2, $sqlClientes, __LINE__, __FILE__);
  $tabla = "";$a=0;
  while($fila = sqlsrv_fetch_array($stmt)){
      if($fila['chofer']<>'')
        $chofer = "<br>($fila[chofer])";
      else
        $chofer = "";
      $tabla .= "<tr name='modParte' id='$fila[sucursal_e]-$fila[parte]'><td><input type='checkbox' checked='checked' name='inluye'/></td><td>".$fila['salida']->format('d/m/y')."</td><td><b>$fila[nombre]</b>$chofer<br><td>".number_format($fila['kilosreal'],0,'.','')." kgs</td><td><input type='text' class='col-xs-8' name='kgsReales'/>kgs</td></tr>";
  }
}
echo $tabla;
?>
