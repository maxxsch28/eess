<?php
// ajaxCargaFacturasComprasSocios.php
// En base al codigo de socio obtiene el CUIT y con ese CUIT:
// 1. Busca facturas emitidas de Calden a ese CUIT en ese período
// 2. Controla de esas facturas cuales no están en MySQL y las incorpora
// 3. Busca en Setup las facturas y NC emitidas a ese CUIT en ese período
// 4. Controla de esas facturas cuales no están en MySQL y las incorpora

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 print_r($_POST);
$cuit = $_SESSION['datosSocio'][$_POST['idSocio']];
$cuitSinGuion = str_replace ("-" , "" , $cuit);
if(isset($_POST['periodo'])){
    $mes=substr($_POST['periodo'],4,2);
    $anio=substr($_POST['periodo'],0,4);
}
//echo $cuit, $cuitSinGuion;
// Obtiene idcliente de Calden


if(isset($_POST['compras'])){
  $sqlCalden = "SELECT IdTipoMovimiento, PuntoVenta, Numero, FechaEmision, IdCategoriaIVA, IdCondicionVenta, NetoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes,  NetoCigarrillos, NetoConceptosFinancieros, dbo.MovimientosFac.IVA, dbo.MovimientosFac.ImpuestoInterno, dbo.MovimientosFac.Tasas, PercepcionIIBB, PercepcionIVA, Total, IdArticulo, dbo.MovimientosDetalleFac.Precio as PrecioRenglon, dbo.MovimientosDetalleFac.IVA as IVARenglon, dbo.MovimientosDetalleFac.ImpuestoInterno as IIRenglon, dbo.MovimientosDetalleFac.Tasas as TasasRenglon, Cantidad FROM dbo.MovimientosFac, dbo.MovimientosDetalleFac WHERE dbo.MovimientosFac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac AND IdCliente IN (SELECT IdCliente FROM dbo.Clientes WHERE NumeroDocumento='$cuit') AND DATEPART(month, Fecha) = $mes and DATEPART(year, fecha)=$anio AND IdTipoMovimiento IN ('FAA', 'FAB', 'NDA', 'NDB', 'NCA', 'NCB')";

  $sqlCaldenNDA = "SELECT IdTipoMovimiento, PuntoVenta, Numero, FechaEmision, IdCategoriaIVA, IdCondicionVenta, NetoNoGravado, NetoMercaderias, NetoCombustibles, NetoLubricantes,  NetoCigarrillos, NetoConceptosFinancieros, dbo.MovimientosFac.IVA, dbo.MovimientosFac.ImpuestoInterno, dbo.MovimientosFac.Tasas, PercepcionIIBB, PercepcionIVA, Total FROM dbo.MovimientosFac, dbo.RechazosChequesTerceros WHERE dbo.MovimientosFac.IdMovimientoFac=dbo.RechazosChequesTerceros.IdMovimientoFac AND IdCliente IN (SELECT IdCliente FROM dbo .Clientes WHERE NumeroDocumento='20-17571137-7') AND DATEPART(month, Fecha) = 02 and DATEPART(year, fecha)=2016 AND IdTipoMovimiento IN ('FAA', 'FAB', 'NDA', 'NDB', 'NCA', 'NCB')";
  
  $sqlSetup = "SELECT emision, cliente, comprobant, tipo, sucursal, numero, cantidad, importe, signo, neto_grava, neto_nogra, porceniva, iva, subtotal, percepib, aliciva, impoiva FROM dbo.histoven WHERE cliente=(SELECT codigo FROM dbo.clientes WHERE cuit='$cuit') AND DATEPART(month, emision) = $mes AND DATEPART(year, emision)=$anio AND comprobant IN ('FACTURA','NOTA DE DEBITO','NOTA DE CREDITO');";
  
  $sqlSetupFletero = "select fecha, fletero, comproban, tipo, sucursal, numero, cantidad, importe, signo, neto, iva_factu, ing_bru from dbo.histccfl where fletero=(SELECT fletero FROM dbo.fleteros WHERE cuit='$cuit') and DATEPART(month, fecha) = $mes AND DATEPART(year, fecha)=$anio and comproban IN ('FACTURA', 'NOTA DE CREDITO', 'NOTA DE DEBITO');";
  
    
  //SELECT emision, cliente, comprobant, tipo, sucursal, numero, cantidad, importe, signo, neto_grava, neto_nogra, porceniva, iva, subtotal, percepib, aliciva, impoiva FROM dbo.histoven WHERE cliente=(SELECT codigo FROM dbo.clientes WHERE cuit='$cuit') AND DATEPART(month, emision) = $mes AND DATEPART(year, emision)=$anio AND comprobant IN ('FACTURA','NOTA DE DEBITO','NOTA DE CREDITO');";


  echo $sqlCalden."\n\n";
  echo $sqlCaldenNDA."\n\n";
  echo $sqlSetup."\n\n";
  echo $sqlSetupFletero."\n\n";
//   echo $sqlSetupVentasFletero."\n\n";

  $stmt = odbc_exec( $mssql, $sqlCalden);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlCalden<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  //IdMovimientoFac	IdTipoMovimiento	PuntoVenta	Numero	Fecha	RazonSocial	Total	Cantidad	IdArticulo
  //473236	FAA	8	77650	2016-04-14 09:15:08.983	STIP NESTOR	1968.6577	108.0500	2068

  while($rowFactura = odbc_fetch_array($stmt)){
    $sqlIva = "SELECT * FROM iva_comprobantes WHERE idSocio=$_POST[idSocio] AND pv=$rowFactura[PuntoVenta] AND numero=$rowFactura[Numero] AND venta=0";
    echo $sqlIva."\n";
    $result = $mysqli->query($sqlIva);
    //$fila = $result->fetch_assoc();
    if($result->num_rows>0 && (!isset($numero) || (isset($numero) && $numero<>$rowFactura['Numero']) ) ){
      //print_r($fila);
      echo "1. Existe previamente\n";
      // existe
    } else {
      if(!isset($numero)||$numero <> $rowFactura['Numero']){
        echo "2. No existe, lo cargo\n";
        $numero = $rowFactura['Numero'];
        // no existe nada de nada, cargo el primer o unico PrecioRenglon
        
        $subtotal = $rowFactura['NetoMercaderias'] + $rowFactura['NetoLubricantes'] + $rowFactura['NetoCombustibles'] + $rowFactura['NetoCigarrillos'] + $rowFactura['NetoConceptosFinancieros'];
        $netoNoGravado = $rowFactura['NetoNoGravado'] + $rowFactura['Cantidad']*($rowFactura['TasasRenglon']+$rowFactura['IIRenglon']);
        $porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
        if($porcentajeIVA==21){
          $ivaCampo = 'iva21';
          $netoCampo = 'neto21';
          $netoX = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
        } elseif ($porcentajeIVA==27) {
          $ivaCampo = 'iva27';
          $netoX = 'neto27';
          $neto27 = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
        } else {
          $ivaCampo = 'iva10';
          $netoCampo = 'neto10';
          $netoX = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
        }
        $fecha = date_format($rowFactura['FechaEmision'], "Y-m-d H:i:s");//0000-00-00 00:00:00
        $periodo = date_format($rowFactura['FechaEmision'], "Ym");//0000-00-00 00:00:00
        
        
        $insertUpdate = "INSERT INTO iva_comprobantes (idSocio, idTercero, pv, numero, subtotal, $netoCampo, $ivaCampo, nogravado, percIIBB, total, fecha, periodo, venta, tipoDocumento) VALUES ($_POST[idSocio], 11, $rowFactura[PuntoVenta], $rowFactura[Numero], $subtotal, $netoX, $rowFactura[IVA], $netoNoGravado, $rowFactura[PercepcionIIBB], $rowFactura[Total], '$fecha', '$periodo', 0, '$rowFactura[IdTipoMovimiento]');";
      } elseif ($numero == $rowFactura['Numero']){
        if($rowFactura['IVARenglon']>0){
          $neto = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']);
          $porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
          if($porcentajeIVA==21){
            $ivaCampo = 'iva21';
            $netoCampo = 'neto21';
            $neto21 = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
          } elseif ($porcentajeIVA==27) {
            $ivaCampo = 'iva27';
            $netoCampo = 'neto27';
            $neto27 = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
          } else {
            $ivaCampo = 'iva10';
            $netoCampo = 'neto10';
            $neto10 = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon']);
          }
          echo "3. Tiene distintos renglones, lo actualizo\n";
          $insertUpdate = "UPDATE iva_comprobantes SET $netoCampo=$netoCampo+$neto WHERE idSocio=$_POST[idSocio] AND idTercero=11 AND pv=$rowFactura[PuntoVenta] AND numero=$rowFactura[Numero] AND venta=0;";
        } else {
          $insertUpdate = '';
        }
        // factura con varios renglones, tengo que recalcular para saber si sumo o que hago. Discrimino IVA.
      }
      $result = $mysqli->query($insertUpdate);
      echo $insertUpdate."\n";
    }
  }

  $stmt = odbc_exec( $mssql, $sqlCaldenNDA);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlCaldenNDA<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  //IdMovimientoFac	IdTipoMovimiento	PuntoVenta	Numero	Fecha	RazonSocial	Total	Cantidad	IdArticulo
  //473236	FAA	8	77650	2016-04-14 09:15:08.983	STIP NESTOR	1968.6577	108.0500	2068

  while($rowFactura = odbc_fetch_array($stmt)){
    $sqlIva = "SELECT * FROM iva_comprobantes WHERE idSocio=$_POST[idSocio] AND pv=$rowFactura[PuntoVenta] AND numero=$rowFactura[Numero] AND venta=0";
    echo $sqlIva."\n";
    $result = $mysqli->query($sqlIva);
    //$fila = $result->fetch_assoc();
    if($result->num_rows>0 && (!isset($numero) || (isset($numero) && $numero<>$rowFactura['Numero']) ) ){
      //print_r($fila);
      echo "1. Existe previamente\n";
      // existe
    } else {
      if(!isset($numero)||$numero <> $rowFactura['Numero']){
        echo "2. No existe, lo cargo\n";
        $numero = $rowFactura['Numero'];
        // no existe nada de nada, cargo el primer o unico PrecioRenglon
        
        $subtotal = $rowFactura['NetoMercaderias'] + $rowFactura['NetoLubricantes'] + $rowFactura['NetoCombustibles'] + $rowFactura['NetoCigarrillos'] + $rowFactura['NetoConceptosFinancieros'];
        $netoNoGravado = $rowFactura['NetoNoGravado'];
        $porcentajeIVA = round(100*($rowFactura['IVA'] / $subtotal),0);
        if($porcentajeIVA==21){
          $ivaCampo = 'iva21';
          $netoCampo = 'neto21';
          $netoX = $subtotal;
        } elseif ($porcentajeIVA==27) {
          $ivaCampo = 'iva27';
          $netoX = 'neto27';
          $neto27 = $subtotal;
        } else {
          $ivaCampo = 'iva10';
          $netoCampo = 'neto10';
          $netoX = $subtotal;
        }
        $fecha = date_format($rowFactura['FechaEmision'], "Y-m-d H:i:s");//0000-00-00 00:00:00
        $periodo = date_format($rowFactura['FechaEmision'], "Ym");//0000-00-00 00:00:00
        
        $insertUpdate = "INSERT INTO iva_comprobantes (idSocio, idTercero, pv, numero, subtotal, $netoCampo, $ivaCampo, nogravado, percIIBB, total, fecha, periodo, venta, tipoDocumento) VALUES ($_POST[idSocio], 11, $rowFactura[PuntoVenta], $rowFactura[Numero], $subtotal, $netoX, $rowFactura[IVA], $netoNoGravado, $rowFactura[PercepcionIIBB], $rowFactura[Total], '$fecha', '$periodo', 0, '$rowFactura[IdTipoMovimiento]');";
      } elseif ($numero == $rowFactura['Numero']){
        // NO VA
        // Tengo que ver si las NDA pueden tener varios renglones.
        // factura con varios renglones, tengo que recalcular para saber si sumo o que hago. Discrimino IVA.
      }
      $result = $mysqli->query($insertUpdate);
      echo $insertUpdate."\n";
    }
  }
  

  $stmt = odbc_exec( $mssql2, $sqlSetup);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlSetup<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  /*
  emision	cliente	comprobant	tipo	sucursal	numero	cantidad	importe	signo	neto_grava	neto_nogra	porceniva	iva	subtotal	percepib	aliciva	impoiva
  2016-03-01 00:00:00.000	6995	FACTURA	A	7	1855	836.8700	836.87	+	691.63	0.00	21.00	145.2400	691.63	0.00	21.0000	145.2423
  */

  while($rowFactura = odbc_fetch_array($stmt)){
    $sqlIva = "SELECT * FROM iva_comprobantes WHERE idSocio=$_POST[idSocio] AND pv=$rowFactura[sucursal] AND numero=$rowFactura[numero] AND venta=0";
    //echo $sqlIva;
    $result = $mysqli->query($sqlIva);
    $fila = $result->fetch_assoc();
    if($result->num_rows>0 && (!isset($numero) || (isset($numero) && $numero<>$rowFactura['Numero']) ) ){
      //print_r($fila);
      echo "4. Existe previamente\n";
      // existe
    } else {
      if(!isset($numero)||$numero <> $rowFactura['numero']){
        echo "5. No existe, lo cargo\n";
        $numero = $rowFactura['numero'];
        // no existe nada de nada, cargo el primer o unico PrecioRenglon
        //$neto = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']);
        //$porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
        /*if($porcentajeIVA==21){
          $ivaCampo = 'iva21';
        } elseif ($porcentajeIVA==27) {
          $ivaCampo = 'iva27';
        } else {
          $ivaCampo = 'iva10';
        }*/
        $fecha = date_format($rowFactura['emision'], "Y-m-d H:i:s");//0000-00-00 00:00:00
        $periodo = date_format($rowFactura['emision'], "Ym");//0000-00-00 00:00:00
        $tipoDocumento = ($rowFactura['comprobant']=='FACTURA')?'FAA':(($rowFactura['comprobant']=='NOTA DE CREDITO')?'NCA':'NDA');
        $insertUpdate = "INSERT INTO iva_comprobantes (idSocio, idTercero, pv, numero, subtotal, neto21, iva21, nogravado, percIIBB, total, fecha, periodo, venta, tipoDocumento) VALUES ($_POST[idSocio], 11, $rowFactura[sucursal], $rowFactura[numero], $rowFactura[neto_grava], $rowFactura[neto_grava], $rowFactura[iva], $rowFactura[neto_nogra], $rowFactura[percepib], $rowFactura[cantidad], '$fecha', '$periodo', 0, '$tipoDocumento');";
      } elseif ($numero == $rowFactura['numero']){
        if($rowFactura['IVARenglon']>0){
          $neto = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']);
          $porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
          if($porcentajeIVA==21){
            $ivaCampo = 'iva21';
          } elseif ($porcentajeIVA==27) {
            $ivaCampo = 'iva27';
          } else {
            $ivaCampo = 'iva10';
          }
          echo "6. Tiene distintos renglones, lo actualizo\n";
          $insertUpdate = "UPDATE iva_comprobantes SET $ivaCampo=$neto WHERE idSocio=$_POST[idSocio] AND idTercero=11 AND pv=$rowFactura[sucursal] AND numero=$rowFactura[Numero] AND venta=0;";
        } else {
          $insertUpdate = '';
        }
        // factura con varios renglones, tengo que recalcular para saber si sumo o que hago. Discrimino IVA.
      }
      $result = $mysqli->query($insertUpdate);
      echo $insertUpdate."\n";
    }
  }
  

  $stmt = odbc_exec( $mssql2, $sqlSetupFletero);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlSetup<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  /*
  emision	cliente	comprobant	tipo	sucursal	numero	cantidad	importe	signo	neto_grava	neto_nogra	porceniva	iva	subtotal	percepib	aliciva	impoiva
  2016-03-01 00:00:00.000	6995	FACTURA	A	7	1855	836.8700	836.87	+	691.63	0.00	21.00	145.2400	691.63	0.00	21.0000	145.2423
  */

  while($rowFactura = odbc_fetch_array($stmt)){
    $sqlIva = "SELECT * FROM iva_comprobantes WHERE idSocio=$_POST[idSocio] AND pv=$rowFactura[sucursal] AND numero=$rowFactura[numero] AND venta=0";
    echo $sqlIva;
    $result = $mysqli->query($sqlIva);
    $fila = $result->fetch_assoc();
    if($result->num_rows>0 && (!isset($numero) || (isset($numero) && $numero<>$rowFactura['Numero']) ) ){
      //print_r($fila);
      echo "7. Existe previamente\n";
      // existe
    } else {
      if(!isset($numero)||$numero <> $rowFactura['numero']){
        echo "8. No existe, lo cargo\n";
        $numero = $rowFactura['numero'];
        // no existe nada de nada, cargo el primer o unico PrecioRenglon
        //$neto = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']);
        //$porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
        /*if($porcentajeIVA==21){
          $ivaCampo = 'iva21';
        } elseif ($porcentajeIVA==27) {
          $ivaCampo = 'iva27';
        } else {
          $ivaCampo = 'iva10';
        }*/
        $fecha = date_format($rowFactura['fecha'], "Y-m-d H:i:s");//0000-00-00 00:00:00
        $periodo = date_format($rowFactura['fecha'], "Ym");//0000-00-00 00:00:00
        $tipoDocumento = ($rowFactura['comproban']=='FACTURA')?'FAA':(($rowFactura['comprobant']=='NOTA DE CREDITO')?'NCA':'NDA');
        $insertUpdate = "INSERT INTO iva_comprobantes (idSocio, idTercero, pv, numero, subtotal, neto21, iva21, nogravado, percIIBB, total, fecha, periodo, venta, tipoDocumento) VALUES ($_POST[idSocio], 11, $rowFactura[sucursal], $rowFactura[numero], $rowFactura[neto], $rowFactura[neto], $rowFactura[iva_factu], 0, $rowFactura[ing_bru], $rowFactura[cantidad], '$fecha', '$periodo', 0, '$tipoDocumento');";
      } elseif ($numero == $rowFactura['numero']){

        // factura con varios renglones, tengo que recalcular para saber si sumo o que hago. Discrimino IVA.
      }
      $result = $mysqli->query($insertUpdate);
      echo $insertUpdate."\n";
    }
  }
} elseif(isset($_POST['ventas'])){

  $sqlSetupVentasFletero = "select fecha, fletero, comproban, tipo, sucursal, numero, cantidad, importe, signo, neto, iva_factu, ing_bru, idcompcomp FROM dbo.histccfl WHERE fletero=(SELECT fletero FROM dbo.fleteros WHERE cuit='$cuit') and DATEPART(month, fecha) = $mes AND DATEPART(year, fecha)=$anio and (idcompcomp = 1 OR idcompcomp=2 or idcompcomp=3 or idcompcomp=4);";
  echo $sqlSetupVentasFletero."\n\n";
  // cargo las facturas que este socio haya presentado en Setup
  $stmt = odbc_exec( $mssql2, $sqlSetupVentasFletero);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlSetupVentasFletero<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  /*
  emision	cliente	comprobant	tipo	sucursal	numero	cantidad	importe	signo	neto_grava	neto_nogra	porceniva	iva	subtotal	percepib	aliciva	impoiva
  2016-03-01 00:00:00.000	6995	FACTURA	A	7	1855	836.8700	836.87	+	691.63	0.00	21.00	145.2400	691.63	0.00	21.0000	145.2423
  */

  while($rowFactura = odbc_fetch_array($stmt)){
    $tipoDocumento = ($rowFactura['idcompcomp']==1||$rowFactura['idcompcomp']==4)?'FAA':(($rowFactura['idcompcomp']==2)?'NCA':'NDA');
    $sqlIva = "SELECT * FROM iva_comprobantes WHERE idSocio=$_POST[idSocio] AND pv=$rowFactura[sucursal] AND numero=$rowFactura[numero] AND tipoDocumento='$tipoDocumento' AND venta=1";
    echo $sqlIva;
    $result = $mysqli->query($sqlIva);
    $fila = $result->fetch_assoc();
    if($result->num_rows>0 && (!isset($numero) || (isset($numero) && $numero<>$rowFactura['Numero']) ) ){
      //print_r($fila);
      echo "7. Existe previamente\n";
      // existe
    } else {
      if(!isset($numero)||$numero <> $rowFactura['numero']){
        echo "8. No existe, lo cargo\n";
        $numero = $rowFactura['numero'];
        // no existe nada de nada, cargo el primer o unico PrecioRenglon
        //$neto = $rowFactura['Cantidad']*($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']);
        //$porcentajeIVA = round(100*($rowFactura['IVARenglon'] / ($rowFactura['PrecioRenglon']-$rowFactura['IVARenglon']-$rowFactura['TasasRenglon']-$rowFactura['IIRenglon'])),0);
        /*if($porcentajeIVA==21){
          $ivaCampo = 'iva21';
        } elseif ($porcentajeIVA==27) {
          $ivaCampo = 'iva27';
        } else {
          $ivaCampo = 'iva10';
        }*/
        $fecha = date_format($rowFactura['fecha'], "Y-m-d H:i:s");//0000-00-00 00:00:00
        $periodo = date_format($rowFactura['fecha'], "Ym");//0000-00-00 00:00:00
       
        $insertUpdate = "INSERT INTO iva_comprobantes (idSocio, idTercero, pv, numero, subtotal, neto21, iva21, nogravado, percIIBB, total, fecha, periodo, venta, tipoDocumento) VALUES ($_POST[idSocio], 11, $rowFactura[sucursal], $rowFactura[numero], $rowFactura[neto], $rowFactura[neto], $rowFactura[iva_factu], 0, $rowFactura[ing_bru], $rowFactura[cantidad], '$fecha', '$periodo', 1, '$tipoDocumento');";
      } elseif ($numero == $rowFactura['numero']){

        // factura con varios renglones, tengo que recalcular para saber si sumo o que hago. Discrimino IVA.
      }
      $result = $mysqli->query($insertUpdate);
      echo $insertUpdate."\n";
    }
  }

}

?>
