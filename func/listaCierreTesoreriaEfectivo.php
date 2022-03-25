<?php
// listaCierreTesoreriaEfectivo.php
// Muestra lo que debería haber en la caja de tesorería al cierre de cada mes 
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST); 
// $array=array();
 // 14/08/2018
 $sql = array();
if(isset($_POST['fechaCierre'])&&$_POST['fechaCierre']<>''){
  $mm=substr($_POST['fechaCierre'],3,2);
  $aa=substr($_POST['fechaCierre'],6,4);
  $dd=substr($_POST['fechaCierre'],0,2);
  $teresa = "";
  $fechaHasta = date("Y-m-d", strtotime("$aa-$mm-$dd")+32400);
} elseif(isset($_POST['saldoCaja'])){
  $mm=date("m");
  $aa=date("Y");
  $dd=date("d");
//   TODO: hacer que no muestre vales desde turnos
  $fechaHasta = date("Y-m-d", strtotime("tomorrow"));
  $teresa = " AND IdCierreTurno IS NOT NULL";
}
if($_POST['mensual']==1&&$_POST['saldoCaja']==0){
  $mensual = true;
  $hasta = date('t', strtotime("$aa/$mm/$dd"));
  $desde = 1;
} else {
  $mensual = false;
  $hasta = $dd;
  $desde = $dd;
}




$tablaEfectivo = "";$a=$q=0;
$jsonCierre = array();


for($dia=$desde; $desde<=$hasta; $desde++){
//   if($desde<10)$desde='0'.$desde;
  if("$aa$mm$desde">date('Ymd')){
    ChromePhp::log("\n\n"."$aa$mm$desde".' --- '.date('Ymd'));
    break;
  }
  ChromePhp::log("\n\n"."$aa$mm$desde".' --- '.date('Ymd'));
  // obtengo los datos desde el último cierre de tesorería
  $sqlUltimoCierre = "select TOP 1 IdCierreCajaTesoreria, FechaCierre, Numero, ArqueoEfectivo, ArqueoChequesTerceros, UserName from dbo.CierresCajaTesoreria WHERE FechaCierre<='$aa-$mm-$desde 23:59:59' order by fechacierre desc;";

  ////ChromePhp::log('Ultimo Cierre '.$sqlUltimoCierre);
  ////////////////////////////////////////////////////////////////////
  $stmt = odbc_exec2( $mssql, $sqlUltimoCierre, __LINE__, __FILE__);
  $ultimoCierre = sqlsrv_fetch_array($stmt);

  $jsonCierre[] = array('t' => 'Efectivo', 'clase' => '', 'txt' => "Según cierre Nº$ultimoCierre[Numero] del ".$ultimoCierre['FechaCierre']->format('d/m/Y H:i'), 'importe' => peso($ultimoCierre['ArqueoEfectivo']));

  $jsonCierre[] = array('t' => 'Cheques', 'clase' => '', 'txt' => "Según cierre Nº$ultimoCierre[Numero] del ".$ultimoCierre['FechaCierre']->format('d/m/Y H:i'), 'importe' => peso($ultimoCierre['ArqueoChequesTerceros']));


  $efectivo = $ultimoCierre['ArqueoEfectivo'];
  $cheques = $ultimoCierre['ArqueoChequesTerceros'];
  $ultimoCierreMes = $ultimoCierre['IdCierreCajaTesoreria'];
  $cierreSiguiente = $ultimoCierreMes + 1;



  ////////////////////////////////////////////////////////////////////
  // Obtengo los datos de todas las transferencias que hayan recibido luego del cierre durante ese mismo día
  $sql['transferencias'] = "select Detalle, Fecha, Efectivo, IngresoEgreso, IdCierreTurno from dbo.OtrosMovimientosCajaTesoreria WHERE (IdCierreCajaTesoreria>$ultimoCierre[IdCierreCajaTesoreria] OR IdCierreCajaTesoreria IS NULL) AND Efectivo>0 AND Fecha>='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND Fecha<'$aa-$mm-$desde 23:59:59' ORDER BY IdCierreTurno, fecha asc, detalle;";
  ChromePhp::log('Transferencias: '. $sql['transferencias']);

  $stmt = odbc_exec2( $mssql, $sql['transferencias'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array($stmt) ){
    if(!isset($turno)&&$fila['IdCierreTurno']>0){
      $turno = $fila['IdCierreTurno'];
      // primer turno
    } else if ( isset($turno) && $turno <> $fila['IdCierreTurno']){
      $jsonCierre[] = array('t' => 'Efectivo', 'clase' => "bold", 'txt' => "Subtotal transferencias ".$datosTurno[$turno], 'importe' => peso($totalTransferenciasTurno[$turno]));
      $turno = $fila['IdCierreTurno'];
      // cambio turno
    }
    if( isset($turno) && !isset($datosTurno[$turno]) ){
      $sqlTurno = "SELECT Fecha, IdCaja FROM dbo.cierresturno WHERE  IdCierreTurno=$turno;";
      
      $stmt2 = odbc_exec2( $mssql, $sqlTurno, __LINE__, __FILE__);
      $fila2 = sqlsrv_fetch_array($stmt2);
      $datosTurno[$turno] = (($fila2['IdCaja']==1)?'PLAYA':(($fila2['IdCaja']==2)?'SHOP':'ADMINISTRATIVA')).' '.$fila2['Fecha']->format('d/m/Y H:i');
    }
    $fila['Detalle']=(substr($fila['Detalle'],0,11)=='Transf. de ')?trim(substr($fila['Detalle'],11)):$fila['Detalle'];
    if($fila['IngresoEgreso']==1){
      $signo = '-';
      $neg = 'neg';
      $multiplica = -1;
    } else {
      $signo = '';
      $neg = '';
      $multiplica = 1;
      if(isset($turno)){
        $totalTransferenciasTurno[$turno] += $multiplica * $fila['Efectivo'];
      }
    }
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => "$neg", 'txt' => "$fila[Detalle], ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Efectivo']));
    $efectivo += $multiplica*$fila['Efectivo'];
  }
  if(isset($datosTurno)){
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => "bold", 'txt' => "Subtotal transferencias ".$datosTurno[$turno], 'importe' => peso($totalTransferenciasTurno[$turno]));
  }
  $sql['transferenciaCheques'] = "select Detalle, a.Fecha, a.Importe, IngresoEgreso, c.Nombre, b.Numero, b.Importe, b.Emisor from dbo.OtrosMovimientosCajaTesoreria a, dbo.ChequesTerceros b, dbo.Bancos c WHERE c.IdBanco=b.IdBanco AND (a.IdOtroMovimientoCajaTesoreria=b.IdOtroMovimientoCajaTesoreriaEntrada OR a.IdOtroMovimientoCajaTesoreria=b.IdOtroMovimientoCajaTesoreriaSalida) AND (a.IdCierreCajaTesoreria>$ultimoCierre[IdCierreCajaTesoreria] OR a.IdCierreCajaTesoreria IS NULL) AND a.Fecha>='".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND a.Fecha<'$aa-$mm-$desde 23:59:59';";
  ChromePhp::log('Transferencias Cheques: '. $sql['transferenciaCheques']);

  $stmt = odbc_exec2( $mssql, $sql['transferenciaCheques'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $fila['Detalle']=(substr($fila['Detalle'],0,11)=='Transf. de ')?trim(substr($fila['Detalle'],11)):$fila['Detalle'];
    if($fila['IngresoEgreso']==1){
      $signo = '-';
      $neg = 'neg';
      $multiplica = -1;
    } else {
      $signo = '';
      $neg = '';
      $multiplica = 1;
    }
    $jsonCierre[] = array('t' => 'Cheques', 'clase' => "$neg", 'txt' => "$fila[Detalle], $fila[Nombre] Nº$fila[Numero] ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Importe']));
    $cheques += $multiplica*$fila['Importe'];
  }

  ////////////////////////////////////////////////////////////////////
  // Recibos administrativos
  $sql['recibos'] = "select Fecha, PuntoVenta, Numero, RazonSocial, Efectivo from dbo.recibos a, dbo.clientes b where (IdCierreCajaTesoreria=$cierreSiguiente OR IdCierreCajaTesoreria IS NULL)  AND Fecha>'".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND Fecha<'".$ultimoCierre['FechaCierre']->format('Y-m-d')." 23:59:58' AND Efectivo>0 AND a.IdCliente=b.IdCliente AND IdCaja IN (1,3)"; // 
  // La falla acá es si cobramos Silvana o yo plata, eso contablemente está en caja, el script solo lo registra si hacemos transferencia, sinó no lo mostraría pero en la contabilidad si estaría
  //ChromePhp::log('Recibos '.$sql['recibos']);

  $stmt = odbc_exec2( $mssql, $sql['recibos'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => '', 'txt' => "Recibo $fila[PuntoVenta]-$fila[Numero], $fila[RazonSocial], ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Efectivo']));
    $efectivo += $fila['Efectivo'];
  }

  $sql['chequesRecibos'] = "select a.Fecha, Emisor, Importe, a.Numero, Nombre from dbo.ChequesTerceros a, dbo.bancos b, dbo.CierresTurno c, dbo.Clientes d WHERE d.IdCliente=a.IdCliente AND a.IdCierreTurno=c.IdCierreTurno AND a.IdBanco=b.IdBanco AND a.IdRecibo>0 AND FechaEntrada>='".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND FechaEntrada<'".$ultimoCierre['FechaCierre']->format('Y-m-d')." 23:59:58' AND (a.IdCierreCajaTesoreria=$cierreSiguiente OR a.IdCierreCajaTesoreria IS NULL) AND Ubicacion=1 UNION select Fecha, Emisor, Importe, Numero, Nombre from dbo.ChequesTerceros a, dbo.bancos b, dbo.Clientes d WHERE d.IdCliente=a.IdCliente AND Idcierreturno IS NULL AND a.IdBanco=b.IdBanco AND a.IdRecibo>0 AND FechaEntrada>='".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND FechaEntrada<'".$ultimoCierre['FechaCierre']->format('Y-m-d')." 23:59:58' AND (a.IdCierreCajaTesoreria=$cierreSiguiente OR a.IdCierreCajaTesoreria IS NULL) AND Ubicacion=1 UNION select Fecha, Emisor, Importe, Numero, Nombre from dbo.ChequesTerceros a, dbo.bancos b WHERE a.IdCliente IS NULL AND Idcierreturno IS NULL AND a.IdBanco=b.IdBanco AND a.IdAcreditacionTarjetasCredito>0 AND FechaEntrada>='".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND FechaEntrada<'".$ultimoCierre['FechaCierre']->format('Y-m-d')." 23:59:58' AND (a.IdCierreCajaTesoreria=$cierreSiguiente OR a.IdCierreCajaTesoreria IS NULL) AND Ubicacion=1;";
  ChromePhp::log('Recibos '.$sql['chequesRecibos']);

  $stmt = odbc_exec2( $mssql, $sql['chequesRecibos'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $jsonCierre[] = array('t' => 'Cheques', 'clase' => '', 'txt' => "$fila[Nombre], Nº$fila[Numero], ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Importe']));
    $cheques += $fila['Importe'];
  }


  $sql['depositosEfectivo'] = "select FechaContable, IdTipoMovimientoBancario, Detalle, Importe from dbo.MovimientosBancarios a, dbo.DepositosBancarios b where a.IdDepositoBancario=b.IdDepositoBancario AND b.Fecha>'".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND b.Fecha<'$aa-$mm-$desde 23:59:59' AND (a.IdCierreCajaTesoreria>$ultimoCierre[IdCierreCajaTesoreria] OR a.IdCierreCajaTesoreria IS NULL)";
  //ChromePhp::log('Depositos '.$sql['depositosEfectivo']);
  $stmt = odbc_exec2( $mssql, $sql['depositosEfectivo'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $t = ($fila['IdTipoMovimientoBancario']=='DHC')?'Cheques':'Efectivo';
    $jsonCierre[] = array('t' => "$t", 'clase' => 'neg', 'txt' => "$fila[Detalle], ".$fila['FechaContable']->format('d/m/Y H:i'), 'importe' => peso($fila['Importe']));
    $efectivo -= ($fila['IdTipoMovimientoBancario']=='DHC')?0:$fila['Importe'];
    $cheques -= ($fila['IdTipoMovimientoBancario']=='DHC')?$fila['Importe']:0;
  }

  $sql['depositosCheques'] = "";



  ////////////////////////////////////////////////////////////////////
  // Entrega de vales a clientes y empleados
  //  AND  Numero NOT IN (SELECT Numero FROM dbo.CierresDetalleVales WHERE Tipo=0 AND Fecha>='".$ultimoCierre['FechaCierre']->format('Y-m-d')."') // Agregado para que no muestre los vales entregados en turnos.
  // Si hago de esto una opción "Teresa", debe quedar así, entonces este script muestra lo que debe dar la caja sin tomar los turnos.
  // Sacar para la opción de cierre de caja contrastable contre la Contabilidad, además que debemos incorporar un histórico de los Vales pendientes de cancelación.

  $sql['vales'] = "select -1 as signo, b.RazonSocial as nombre, Prefijo, Numero, Importe, IdCierreCajaTesoreria, IdCierreTurno, Fecha from dbo.ValesClientes a, dbo.clientes b WHERE a.IdCliente=b.IdCliente AND Convert(date, Fecha)>='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND Convert(date, Fecha)<'$fechaHasta' AND (IdCierreCajaTesoreria IS NULL OR IdCierreCajaTesoreria>$ultimoCierreMes) AND  Numero NOT IN (SELECT Numero FROM dbo.CierresDetalleVales WHERE Tipo=0 AND Fecha>='".$ultimoCierre['FechaCierre']->format('Y-m-d')."') $teresa UNION 

  select -1 as signo, b.Empleado as nombre, Prefijo, Numero, Importe, IdCierreCajaTesoreria, IdCierreTurno, Fecha from dbo.ValesEmpleados a, dbo.Empleados b WHERE a.IdEmpleado=b.IdEmpleado AND Convert(date, Fecha)>='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND Convert(date, Fecha)<'$fechaHasta' AND (IdCierreCajaTesoreria IS NULL OR IdCierreCajaTesoreria>$ultimoCierreMes) UNION 

  select 1 as signo, concat('RECIBO ',b.Empleado) as nombre, Prefijo, Numero, Importe, IdCierreCajaTesoreria, 0 as IdCierreTurno, Fecha from dbo.RecibosValesEmpleados a, dbo.Empleados b WHERE a.IdEmpleado=b.IdEmpleado AND Convert(date, Fecha)='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND (IdCierreCajaTesoreria IS NULL OR IdCierreCajaTesoreria>$ultimoCierreMes) UNION 

  select 1 as signo, concat('RECIBO ',b.RazonSocial) as nombre, Prefijo, Numero, Importe, IdCierreCajaTesoreria, 0 as IdCierreTurno, Fecha from dbo.RecibosValesClientes a, dbo.Clientes b WHERE a.IdCliente=b.IdCliente AND Convert(date, Fecha)='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND (IdCierreCajaTesoreria IS NULL OR IdCierreCajaTesoreria>$ultimoCierreMes) order by fecha;";

  // //echo $sql['vales'] ;
  //ChromePhp::log('Vales '.$sql['vales']);
  $stmt = odbc_exec2( $mssql, $sql['vales'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $class=($fila['signo']<0)?'neg':'';
    $texto=($fila['signo']>0)?'Cancela vale':'Vale';
    
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => "$class", 'txt' => "$texto $fila[Prefijo]-$fila[Numero], $fila[nombre], ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Importe']));
    $efectivo += $fila['signo']*$fila['Importe'];
  }






  ////////////////////////////////////////////////////////////////////
  // Efectivo declarado en turnos
  $sql['efectivoTurnos'] = "select Fecha, b.Descripcion, Efectivo, CambioParaTurnoSiguiente from dbo.cierresTurno a, dbo.Cajas b WHERE a.IdCaja=b.IdCaja AND Convert(date, Fecha)='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND (IdCierreCajaTesoreria IS NULL OR IdCierreCajaTesoreria>$ultimoCierreMes)";

  //ChromePhp::log('Efectivo en turnos '. $sql['efectivoTurnos']);

  $stmt = odbc_exec2( $mssql, $sql['efectivoTurnos'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    if($fila['Efectivo']>0){
      $jsonCierre[] = array('t' => 'Efectivo', 'clase' => '', 'txt' => "Efectivo declarado en caja $fila[Descripcion] del ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['Efectivo']));
      $efectivo += $fila['Efectivo'];
    }
  }
  ////////////////////////////////////////////////////////////////////
  // Cheques declarado en turnos
  $sql['chequesTurnos'] = "select c.Fecha, b.Nombre, a.Importe, a.Numero from dbo.ChequesTerceros a, dbo.bancos b, dbo.cierresTurno c WHERE a.IdCierreTurno=c.IdCierreTurno AND a.IdBanco=b.IdBanco AND a.IdCierreTurno>0 AND Convert(date, FechaEntrada)='".$ultimoCierre['FechaCierre']->format('Y-m-d')."' AND (a.IdCierreCajaTesoreria>$ultimoCierreMes OR a.IdCierreCajaTesoreria IS NULL) AND IdOtroMovimientoCajaTesoreriaEntrada IS NULL AND a.IdRecibo IS NULL;";
  ChromePhp::log('Cheques en turnos '. $sql['chequesTurnos']);

  $stmt = odbc_exec2( $mssql, $sql['chequesTurnos'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    if($fila['Importe']>0){
      $jsonCierre[] = array('t' => 'Cheques', 'clase' => '', 'txt' => "Cheque en turno del ".$fila['Fecha']->format('d/m/Y H:i')." $fila[Nombre] Nº$fila[Numero]", 'importe' => peso($fila['Importe']));
      $cheques += $fila['Importe'];
    }
  }





  ////////////////////////////////////////////////////////////////////
  // Ordenes de pago en efectivo
  $sql['ordenesPago'] = "select b.RazonSocial, PagoEfectivo, Numero, Fecha from dbo.OrdenesPago a, dbo.Proveedores b WHERE a.IdProveedor=b.IdProveedor AND Fecha>'".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND Fecha<'$aa-$mm-$desde 23:59:59' AND (IdCierreCajaTesoreria>$ultimoCierre[IdCierreCajaTesoreria] OR IdCierreCajaTesoreria IS NULL) AND PagoEfectivo>0 AND IdFondoFijoCierre IS NULL;";
  //ChromePhp::log($sql['ordenesPago']);

  $stmt = odbc_exec2( $mssql, $sql['ordenesPago'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => 'neg', 'txt' => "Orden de pago Nº $fila[Numero], $fila[RazonSocial], ".$fila['Fecha']->format('d/m/Y H:i'), 'importe' => peso($fila['PagoEfectivo']));
    $efectivo -= $fila['PagoEfectivo'];
  }
  // Ordenes de pago con cheques de ChequesTerceros
  $sql['ordenesPagoCheques'] = "select b.RazonSocial, PagoEfectivo, a.Numero, a.Fecha, d.Nombre, c.Numero as NumeroCheque, c.Importe from dbo.OrdenesPago a, dbo.Proveedores b, dbo.ChequesTerceros c, dbo.bancos d WHERE a.IdProveedor=b.IdProveedor AND a.Fecha>'".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND a.Fecha<'$aa-$mm-$desde 23:59:59' AND (a.IdCierreCajaTesoreria>$ultimoCierre[IdCierreCajaTesoreria] OR a.IdCierreCajaTesoreria IS NULL) AND a.IdOrdenPago=c.IdOrdenPago AND c.IdBanco=d.IdBanco;";
  //ChromePhp::log('OP con cheques '.$sql['ordenesPagoCheques']);

  $stmt = odbc_exec2( $mssql, $sql['ordenesPagoCheques'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $jsonCierre[] = array('t' => 'Cheques', 'clase' => 'neg', 'txt' => "OP Nº $fila[Numero], $fila[RazonSocial], ".$fila['Fecha']->format('d/m/Y H:i').', Cheque Nº '.$fila['NumeroCheque'], 'importe' => peso($fila['Importe']));
    $cheques -= $fila['Importe'];
  }





  /////////////////////////////////////////////////////////////////////
  // Pagos a proveedores en efectivo
  $sql['comprasContado'] = "select Fecha, LastUpdated, LastUpdated-Fecha as dias, IdTipoMovimientoProveedor, PuntoVenta, Numero, RazonSocial, PagoEfectivo from dbo.movimientospro where PagoEfectivo>0 and (IdCierreCajaTesoreria=$cierreSiguiente OR IdCierreCajaTesoreria IS NULL) AND PagoEfectivo>0 AND IdCajaEfectivo IS NULL AND IdFondoFijoCierre IS NULL AND LastUpdated>'".$ultimoCierre['FechaCierre']->format('Y-m-d H:i:s')."' AND LastUpdated<'$aa-$mm-$desde 23:59:59';";
  //ChromePhp::log($sql['comprasContado']);

  $stmt = odbc_exec2( $mssql, $sql['comprasContado'], __LINE__, __FILE__);
  while($fila = sqlsrv_fetch_array(($stmt))){
    $jsonCierre[] = array('t' => 'Efectivo', 'clase' => 'neg', 'txt' => "Pago contado $fila[IdTipoMovimientoProveedor] $fila[PuntoVenta]-$fila[Numero], $fila[RazonSocial], ".$fila['LastUpdated']->format('d/m/Y H:i'), 'importe' => peso($fila['PagoEfectivo']));
    $efectivo += $multiplica*$fila['PagoEfectivo'];
  }
  if($mensual){
    $jsonCierre2[] = array('t' => 'Efectivo', 'clase' => 'bold', 'txt' => "$desde/$mm", 'importe' => peso($efectivo));
    $jsonCierre2[] = array('t' => 'Cheques', 'clase' => 'bold', 'txt' => "$desde/$mm", 'importe' => peso($cheques));
  } else {
    $jsonCierre2 = $jsonCierre;
    $jsonCierre2[] = array('t' => 'Efectivo', 'clase' => 'bold', 'txt' => "Total", 'importe' => peso($efectivo));
    $jsonCierre2[] = array('t' => 'Cheques', 'clase' => 'bold', 'txt' => "Total", 'importe' => peso($cheques));
  }
  
}
echo json_encode($jsonCierre2);
?>
