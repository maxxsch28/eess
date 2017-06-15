<?php
// muestraDetalleMovimientoTransporte.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


$limit=11;
$offset=0;

$parametros = explode('_', $_GET['idAsiento']);
$idTranglob = $parametros[0];
$tipo = $parametros[1];
fb($_GET);
fb($tipo);
switch($tipo){
  case '54':
  // extracción de efectivo de cuenta bancaria
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    echo "<b>Extraccion de cuenta bancaria - Cambio EESS</b>";
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: ".sprintf("%.2f",$row['pagoImporte'])."<br/>IIBB: $$row[reten_ib]<br>";
      }
      echo "$row[nombre] Nº$row[detalleNumero], $$row[detalleImporte], ".date_format($row['vencimien'], "d/m/Y")."<br/>";
    }
    break;  
  case '1003':
    // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien, operador from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: ".sprintf("%.2f",$row['pagoImporte'])."<br/>IIBB: $$row[reten_ib]<br>";
      }
      echo "$row[nombre] Nº$row[detalleNumero], $$row[detalleImporte], ".date_format($row['vencimien'], "d/m/Y")."<br/>";
      $operador = $row['operador'];
    }
    if(sqlsrv_num_rows($stmt2)==0){
      
      // caso del detalle de los cheques
      $sqlMovimiento = "select * from dbo.histcomp where idtranglob=$idTranglob;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['ingreso'], "d/m/Y").")</br>";
        }
        echo "$row[nombre] Nº".abs($row['numeche']).", $".sprintf("%.2f",abs($row['importe'])).", ".date_format($row['vencimien'], "d/m/Y")."<br/>";
        $operador = $row['operador'];
      }
    }
    echo "<small>Operador $operador</small><br/>";
    break; 
  case '30':
    // orden de servicio a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien, operador from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: ".sprintf("%.2f",$row['pagoImporte'])."<br/>IIBB: $$row[reten_ib]<br>";
      }
      echo "$row[nombre] Nº$row[detalleNumero], $$row[detalleImporte], ".date_format($row['vencimien'], "d/m/Y")."<br/>";
      $operador = $row['operador'];
    }
    if(sqlsrv_num_rows($stmt2)==0){
      
      // caso del detalle de los cheques
      $sqlMovimiento = "select * from dbo.histcomp where idtranglob=$idTranglob;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['ingreso'], "d/m/Y").")</br>";
        }
        echo "$row[nombre] Nº".abs($row['numeche']).", $".sprintf("%.2f",abs($row['importe'])).", ".date_format($row['vencimien'], "d/m/Y")."<br/>";
        $operador = $row['operador'];
      }
    }
    echo "<small>Operador $operador</small><br/>";
    break;  
  case '1025':
  // factura de proveedor
   //echo "$idTranglob";
   
    $sqlMovimiento = "select a.comprobant, a.tipo, a.sucursal, a.numero, a.neto_nogra, a.neto_grava, a.iva, a.importe, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, * from dbo.detaivco a, dbo.proveedo p, dbo.histocom h where a.idtranglob=$idTranglob and a.codproviva=p.codigo AND h.idtranglob=a.idtranglob;";
    fb($sqlMovimiento);
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      fb($row);
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[comprobant] $row[sucursal]-$row[numero]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: ".sprintf("%.2f",$row['importe'])."<br/>";
        if($row['detalle']<>'')echo "<b>$row[detalle]</b><br>";
        $operador = $row['operador'];
      }
      echo "Proveedor: <b>$row[nombre]</b><br/>";
      
      echo "<small>Operador $operador</small><br/>";
    }
    break;   
  case '1024':
  case '1002':
  // deposito bancario
   //echo "$idTranglob";
    $sqlMovimiento = "select a.fecha, a.orden Collate SQL_Latin1_General_CP1253_CI_AI as orden, a.detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, a.importe, a.conciliado, a.operador, b.nombre, b.numero_val, c.detalle_s, a.anulado, b.tipomovi, an_numero, an_tipomov from dbo.moctacte a, dbo.detamocc b, dbo.histcomp c where a.idtranglob=b.idtranglob AND a.idtranglob=c.idtranglob AND a.idtranglob=$idTranglob;";
    $stmt2 = odbc_exec2($mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: ".sprintf("%.2f",$row['importe'])."<br/>";
        echo "$row[detalle_s]<br/>";
        if($row['anulado']==1)echo"<span class='bg bg-danger'>&nbsp;ANULADO&nbsp;</span><br/>";
      }
      echo "$row[nombre] Nº$row[numero_val], $$row[importe]<br/>";
      echo "<small>Operador $row[operador]</small><br/>";
    }
    break;  
  case '1033':
  case '1034':
  // deposito bancario
   //echo "$idTranglob";
    $sqlMovimiento = "select * from dbo.idtranco a, dbo.concasie b, dbo.histvalo c where a.idtranglob=c.idtranglob AND a.idtranglob=b.idtranglob AND a.idtranglob=$idTranglob;";
    $stmt2 = odbc_exec2($mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2)){
      fb($row);
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        if($tipo=='1034')echo "<b>RECIBO VARIO</b><br/>"; else echo "<b>PAGO VARIO</b><br/>";
        echo "<b>$row[detalle]</b><br/>(".$row[13]->format("d/m/Y").")</br>Total: $".sprintf("%.2f",abs($row['importe']))."<br/>";
        echo "$row[detalle_s]<br/>";
        if($row['anulado']==1)echo"<span class='bg bg-danger'>&nbsp;ANULADO&nbsp;</span><br/>";
      }
      echo "$row[nombre] Nº$row[numero_val], $".sprintf("%.2f",abs($row['importe']))."<br/>";
      echo "<small>Operador $row[operador]</small><br/>";
    }
    
    if(sqlsrv_num_rows($stmt2)==0){
      $sqlMovimiento = "select h.nombre as moneda, f.nombre as fondofijo, detalle_s, h.fechamovi, h.importe, h.operador from dbo.histcomp h, dbo.fondofij f where idtranglob=$idTranglob AND h.fondofijo=f.codigo;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['fechamovi'], "d/m/Y").")</br>";
        }
        echo "$row[moneda] de $row[fondofijo], $".abs(round($row['importe'],2))."<br/>";
        $operador = $row['operador'];
      }
    } 
    if(sqlsrv_num_rows($stmt2)==0){
      $sqlMovimiento = "select h.nombre as moneda, detalle_s, h.fechamovi, h.importe, h.operador, nombre, numeche, cantidad, vencimien from dbo.histcomp h where idtranglob=$idTranglob AND h.fondofijo=0;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['fechamovi'], "d/m/Y").")</br>";
        }
        echo "$row[nombre] Nº $row[numeche], $".abs(round($row['cantidad'],2))." (".date_format($row['vencimien'], "d/m/Y").")<br/>";
        $operador = $row['operador'];
      }
    }
    break;
  case '1046':
  // ajuste fleteros
   //echo "$idTranglob";
    $sqlMovimiento = "select * from histccfl a, fleteros b where a.fletero=b.fletero AND a.idtranglob=$idTranglob;";
    $stmt2 = odbc_exec2($mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2)){
      fb($row);
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>AJUSTE A FLETERO</b><br/>";
        echo "<b>$row[35]</b><br/>(".$row[0]->format("d/m/Y").")</br>Total: $".sprintf("%.2f",abs($row['importe']))."<br/>";
        echo "$row[detalle_s]<br/>";
        if($row['anulado']==1)echo"<span class='bg bg-danger'>&nbsp;ANULADO&nbsp;</span><br/>";
      }
      echo "$row[nombre] Nº$row[numero_val], $".sprintf("%.2f",abs($row['importe']))."<br/>";
      echo "<small>Operador $row[31]</small><br/>";
    }
    break;  
  case '6':
  case '1035':
    // factura CONTADO a fletero
    $sqlMovimiento = "select h.nombre as moneda, f.nombre as fondofijo, nomdeudor, detalle_e, fechamovi, importe, operador FROM dbo.histcomp h, dbo.fondofij f WHERE idtranglob=$idTranglob AND h.fondofijo=f.codigo;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      fb($row);
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle_e]</b><br/>(".date_format($row['fechamovi'], "d/m/Y").")</br>";
      }
      echo "$row[moneda] de $row[fondofijo], $".abs(round($row['importe'],2))."<br/>";
      echo "<small>Operador $row[operador]</small><br/>";
    }
    //echo "$row[nombre] Nº $row[detalleNumero], $$row[detalleImporte], ".date_format($row['vencimien'], "d/m/Y")."<br/>";
    break;
  case '1031':
  // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>Total: $$row[pagoImporte]<br/>IIBB: $$row[reten_ib]<br>";
      }
      echo "$row[nombre] Nº $row[detalleNumero], $$row[detalleImporte], ".date_format($row['vencimien'], "d/m/Y")."<br/>";
    }
    break;
  case '1039':
  // adelanto a fletero
    $sqlMovimiento = " select p.numero, fecha, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, p.importe, banco, nombre_ban, abs(d.numero_che) as numero_che, d.importe as detalleImporte, fecha_che, operador from dbo.moctacte as p, dbo.detaadfl as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob AND abs(p.numero)=abs(d.numero_che);";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br/>(".date_format($row['fecha'], "d/m/Y").")</br>";
      }
      echo "$row[nombre_ban] Nº $row[numero_che], $".round($row['detalleImporte'],2).", ".date_format($row['fecha_che'], "d/m/Y")."<br/>";
      $operador = $row['operador'];
    }
    if(sqlsrv_num_rows($stmt2)==0){
      $sqlMovimiento = "select h.nombre as moneda, f.nombre as fondofijo, detalle_s, h.fechamovi, h.importe, h.operador from dbo.histcomp h, dbo.fondofij f where idtranglob=$idTranglob AND h.fondofijo=f.codigo;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['fechamovi'], "d/m/Y").")</br>";
        }
        echo "$row[moneda] de $row[fondofijo], $".abs(round($row['importe'],2))."<br/>";
        $operador = $row['operador'];
      }
    } 
    if(sqlsrv_num_rows($stmt2)==0){
      $sqlMovimiento = "select h.nombre as moneda, detalle_s, h.fechamovi, h.importe, h.operador, nombre, numeche, cantidad, vencimien from dbo.histcomp h where idtranglob=$idTranglob AND h.fondofijo=0;";
      $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
      while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
        fb($row);
        if(!isset($tituloDetalle)){
          $tituloDetalle = 1;
          echo "<b>$row[detalle_s]</b><br/>(".date_format($row['fechamovi'], "d/m/Y").")</br>";
        }
        echo "$row[nombre] Nº $row[numeche], $".abs(round($row['cantidad'],2))." (".date_format($row['vencimien'], "d/m/Y").")<br/>";
        $operador = $row['operador'];
      }
    }
    echo "<small>Operador $operador</small><br/>";
    break;
  case '1030':
  // pago a proveedor
    $sqlMovimiento = "select p.numero, p.fecha, detalle, p.importe, d.nombre, abs(d.numero) as numeroCheque, d.importe as detalleImporte, d.vencimien, reten_ib, observacio from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$row[detalle]</b><br><i>".date_format($row['fecha'], "d/m/Y").", $row[observacio]</i></br>Total: $$row[importe]<br/>";
        if($row['reten_ib']>0)echo "IIBB: $$row[reten_ib]<br>";
      }
      echo "$row[nombre] Nº $row[numeroCheque], $".round($row['detalleImporte'],2).", ".date_format($row['vencimien'], "d/m/Y")."<br/>";
    }
    break;

  default:
          echo "$tipo";
          break;
		
}
fb($sqlMovimiento);


?>
