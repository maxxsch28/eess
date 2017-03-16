<?php
// muestraDetalleMovimientoTransporte.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


$limit=11;
$offset=0;

$parametros = explode('_', $_GET['idAsiento']);
$idTranglob = $parametros[0];
$tipo = $parametros[1];

fb($tipo);
switch($tipo){
  case '54':
  // extracción de efectivo de cuenta bancaria
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    echo "<b>Extraccion de cuenta bancaria - Cambio EESS</b>";
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº$rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;  
  case '1003':
  // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº$rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;  
  case '1025':
  // factura de proveedor
   echo "$idTranglob";
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº$rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;  
  case '1031':
  // pago a fletero
    $sqlMovimiento = "select p.sucursal as pagoSucursal, p.numero as pagoNumero, fecha, detalle, p.importe as pagoImporte, reten_ib, banco, nombre, abs(d.numero) as detalleNumero, d.importe as detalleImporte, d.vencimien from dbo.pagos as p, dbo.detapago as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $$rowMovimiento[pagoImporte]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº $rowMovimiento[detalleNumero], $$rowMovimiento[detalleImporte], ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break;   
  case '1039':
  // adelanto a fletero
    $sqlMovimiento = " select p.numero, fecha, detalle, p.importe, banco, nombre_ban, abs(d.numero_che) as numero_che, d.importe as detalleImporte, fecha_che from dbo.moctacte as p, dbo.detaadfl as d where p.idtranglob=$idTranglob AND p.idtranglob=d.idtranglob AND abs(p.numero)=abs(d.numero_che);";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>";
      }
      echo "$rowMovimiento[nombre_ban] Nº $rowMovimiento[numero_che], $".round($rowMovimiento['detalleImporte'],2).", ".date_format($rowMovimiento['fecha_che'], "d/m/Y")."<br/>";
    }
    break;  
  case '1030':
  // pago a proveedor
    $sqlMovimiento = "select p.numero, p.fecha, detalle, p.importe, d.nombre, abs(d.numero) as numeroCheque, d.importe as detalleImporte, d.vencimien, reten_ib from dbo.pagos as p, dbo.detapago as d where p.idtranglob=17915 AND p.idtranglob=d.idtranglob;";
    $stmt2 = odbc_exec2( $mssql2, $sqlMovimiento, __LINE__, __FILE__);
    while($rowMovimiento = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
      if(!isset($tituloDetalle)){
        $tituloDetalle = 1;
        echo "<b>$rowMovimiento[detalle]</b> (".date_format($rowMovimiento['fecha'], "d/m/Y").")</br>Total: $$rowMovimiento[importe]<br/>IIBB: $$rowMovimiento[reten_ib]<br>";
      }
      echo "$rowMovimiento[nombre] Nº $rowMovimiento[numeroCheque], $".round($rowMovimiento['detalleImporte'],2).", ".date_format($rowMovimiento['vencimien'], "d/m/Y")."<br/>";
    }
    break; 

  default:
          echo "$tipo";
          break;
		
}
fb($sqlMovimiento);


?>
