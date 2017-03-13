<?php
// calculaPromedios.php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

//fb($_POST, 'POST');

$r2 = explode("/", $_REQUEST['rangoFin']);
$r1 = explode("/", $_REQUEST['rangoInicio']);
$rangoFin="$r2[0]/$r2[1]/20$r2[2]";
$rangoInicio="$r1[0]/$r1[1]/20$r1[2]";

$fuzziness=(isset($_REQUEST['fuzzy']))?" AND dbo.asiecont.cantidad>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))."  AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.asiecont WHERE cantidad>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness'])).") AND dbo.asiecont.cantidad<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness'])." AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.asiecont WHERE cantidad<=".floor(($_REQUEST['importe']+$_REQUEST['fuzziness'])).")":" AND dbo.asiecont.cantidad=$_REQUEST[importe] AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.asiecont WHERE cantidad=$_REQUEST[importe])";


$fuzziness2=(isset($_REQUEST['fuzzy']))?" AND dbo.diario.cantidad>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))."  AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.asiecont WHERE cantidad>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness'])).") AND dbo.diario.cantidad<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness'])." AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.diario WHERE cantidad<=".floor(($_REQUEST['importe']+$_REQUEST['fuzziness'])).")":" AND dbo.diario.cantidad=$_REQUEST[importe] AND dbo.concasie.idtranglob in (SELECT idtranglob FROM dbo.diario WHERE cantidad=$_REQUEST[importe])";

$leyenda=(isset($_REQUEST['leyenda'])&&strlen($_REQUEST['leyenda'])>1)?" AND detalle LIKE ('%".ms_escape_string($_REQUEST['leyenda'])."%')":'';

$cuenta=(isset($_REQUEST['cuentaTransporte'])&&$_REQUEST['cuentaTransporte']>0)?" AND cuentacont=$_REQUEST[cuentaTransporte]":'';

if(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda||$cuenta)<>''){
  $fuzziness='';
} elseif(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda&&$cuenta)==''){
  echo "<tbody><tr><td colspan='2' class='act'>Ingrese parámetros de búsqueda</td></tr></tbody>";
  die;
}


// tmpBuscaAsiento
if(isset($_POST['idBuscaAsiento'])){
  // búsqueda basada en una ya grabada
  $sql = "UPDATE tmpBuscaAsientos SET cantidadusos=cantidadusos+1 WHERE id=$_REQUEST[idBuscaAsiento]";
} else{
  // inserto una nueva búsqueda // 28-09-1977
  $fuzyness = (isset($_REQUEST['fuzzy']))?$_REQUEST['fuzziness']:0;
  //$rangoInicio = substr($_REQUEST['rangoInicio'], 6).'-'.substr($_REQUEST['rangoInicio'], 0,2).'-'.substr($_REQUEST['rangoInicio'], 3,2);
  //$rangoFin = substr($_REQUEST['rangoFin'], 6).'-'.substr($_REQUEST['rangoFin'], 0,2).'-'.substr($_REQUEST['rangoFin'], 3,2);
  $sql = "INSERT INTO tmpBuscaAsientos (ambito, importe, rangoInicio, rangoFin, fuzzyness, leyenda, cuentaTransporte, user_id) VALUES ('$_REQUEST[ambito]', '$_REQUEST[importe]', '$r1[2]/$r1[1]/$r1[0]', '$r2[2]/$r2[1]/$r2[0]', $fuzyness, '".((isset($_REQUEST['leyenda'])&&$_REQUEST['leyenda']>'')?mysqli_real_escape_string($mysqli, $_REQUEST['leyenda']):'')."', '".((isset($_REQUEST['cuentaTransporte'])&&$_REQUEST['cuentaTransporte']>0)?$_REQUEST['cuentaTransporte']:0)."', $loggedInUser->user_id)";
}
fb($sql);
if(!isset($_SESSION['ultimoSQL'])||$_SESSION['ultimoSQL']<>$sql){
  $result = $mysqli->query($sql);
  $_SESSION['ultimoSQL']=$sql;
} else {
  $sql = "SELECT 1;";
}
//$result = $mysqli->query($sql);


$andFecha=(isset($_REQUEST['rangoInicio']))?" AND dbo.concasie.fecha_asie>='{$rangoInicio}' AND dbo.concasie.fecha_asie<='{$rangoFin} 23:59:59'":"";

if($_REQUEST['importe']<>''){
  $sqlAsientos = trim("SELECT DISTINCT dbo.asiecont.asiento, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, fecha, asiecont.transaccio, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[concasie].idtranglob, dbo.asiecont.cod_libro FROM dbo.asiecont, dbo.concasie WHERE dbo.asiecont.asiento=dbo.concasie.asiento AND dbo.asiecont.cod_libro=dbo.concasie.cod_libro{$fuzziness}{$leyenda}{$cuenta}{$andFecha} UNION SELECT DISTINCT dbo.diario.asiento, detalle Collate SQL_Latin1_General_CP1253_CI_AI, fecha, diario.transaccio, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[concasie].idtranglob, dbo.diario.cod_libro FROM dbo.diario, dbo.concasie WHERE dbo.diario.asiento=dbo.concasie.asiento AND dbo.diario.cod_libro=dbo.concasie.cod_libro{$fuzziness2}{$leyenda}{$cuenta}{$andFecha} order by fecha asc");
} else {
  $sqlAsientos = trim("SELECT DISTINCT dbo.asiecont.asiento, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, fecha, asiecont.transaccio, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[concasie].idtranglob, dbo.asiecont.cod_libro FROM dbo.asiecont, dbo.concasie WHERE dbo.asiecont.asiento=dbo.concasie.asiento AND dbo.asiecont.cod_libro=dbo.concasie.cod_libro{$fuzziness}{$leyenda}{$cuenta}{$andFecha} order by fecha asc");
}
//$sqlAsientos = trim("SELECT DISTINCT dbo.asiecont.asiento, fecha, asiecont.transaccio, dbo.concasie.concepto, dbo.concasie.idtranglob, dbo.asiecont.cod_libro FROM dbo.asiecont, dbo.concasie WHERE dbo.asiecont.asiento=dbo.concasie.asiento AND dbo.asiecont.cod_libro=dbo.concasie.cod_libro{$fuzziness}{$leyenda}{$cuenta}  ");
$sqlAsientos = trim("SELECT DISTINCT dbo.asiecont.asiento, detalle Collate SQL_Latin1_General_CP1253_CI_AI as detalle, fecha, asiecont.transaccio, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[concasie].idtranglob, dbo.asiecont.cod_libro FROM dbo.asiecont, dbo.concasie WHERE dbo.asiecont.asiento=dbo.concasie.asiento AND dbo.asiecont.cod_libro=dbo.concasie.cod_libro{$fuzziness}{$leyenda}{$cuenta}{$andFecha} ");
fb($sqlAsientos);
//die;
// select * from dbo.asiecont, dbo.concasie where cantidad = 308807.83 and dbo.asiecont.asiento=dbo.concasie.asiento and dbo.asiecont.cod_libro=dbo.concasie.cod_libro

// AND (dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable OR dbo.asientos.IdModeloContable is NULL)

// , dbo.ModelosContables

//, dbo.ModelosContables.Nombre
$dbg=0;

//odbc_free_result($stmt);

if($dbg)echo "<thead ><tr><td colspan=4 style='height:5em'>$sqlAsientos</td></tr></thead>";
//$stmt = odbc_exec($mssql4, $sqlAsientos);
//echo $sqlAsientos;
$stmt = odbc_exec2($mssql4, $sqlAsientos, __LINE__, __FILE__);

while($rowAsientos = odbc_fetch_array($stmt)){
  $sqlDetalles = "SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[asiecont].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[asiecont].idtranglob, [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[asiecont], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[asiecont].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[asiecont].transaccio=$rowAsientos[transaccio] AND [sqlcoop_dbimplemen].[dbo].[concasie].transaccio=$rowAsientos[transaccio] AND [sqlcoop_dbimplemen].[dbo].[asiecont].asiento=$rowAsientos[asiento] UNION SELECT cantidad, [sqlcoop_dbimplemen].[dbo].[diario].asiento, [sqlcoop_dbimplemen].[dbo].[concasie].detalle, debe, haber, fecha, [sqlcoop_dbshared].[dbo].[plancuen].nombre, cuentacont, ordenamien, [sqlcoop_dbimplemen].[dbo].[concasie].concepto, [sqlcoop_dbimplemen].[dbo].[diario].idtranglob, [sqlcoop_dbimplemen].[dbo].[diario].cod_libro FROM [sqlcoop_dbimplemen].[dbo].[diario], [sqlcoop_dbimplemen].[dbo].[concasie], [sqlcoop_dbshared].[dbo].[plancuen] WHERE [sqlcoop_dbshared].[dbo].[plancuen].codigo=cuentacont AND [sqlcoop_dbimplemen].[dbo].[diario].cod_libro=[sqlcoop_dbimplemen].[dbo].[concasie].cod_libro AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=[sqlcoop_dbimplemen].[dbo].[concasie].asiento AND [sqlcoop_dbimplemen].[dbo].[diario].transaccio=$rowAsientos[transaccio] AND [sqlcoop_dbimplemen].[dbo].[concasie].transaccio=$rowAsientos[transaccio] AND [sqlcoop_dbimplemen].[dbo].[diario].asiento=$rowAsientos[asiento] ORDER BY debe DESC, haber DESC";
            
  fb($sqlDetalles);
    
  $stmt2 = odbc_exec( $mssql2, $sqlDetalles);
  if( $stmt2 === false ){
    echo "2. Error in executing query.</br>$sqlDetalles<br/>";
    die( print_r( odbc_errormsg().' -- '.odbc_error(), true));
  }
  $fecha = substr($rowAsientos['fecha'], 0,10);
  unset($nombre);
  $detalle = utf8_encode($rowAsientos['detalle']);
  
  if(!isset($_SESSION['concepto'][$rowAsientos['concepto']])){
    $sqlConcepto = "SELECT TOP 1 concepto FROM CONCECON WHERE codigo=$rowAsientos[concepto];";
    fb($sqlConcepto);
    $stmt5 = odbc_exec($mssql2, $sqlConcepto);
     if( $stmt5 === false ){
      echo "2. Error in executing query.</br>$sqlConcepto<br/>";
      die( print_r( odbc_errormsg().' - '.odbc_error(), true));
    }
    $tmp = odbc_fetch_array($stmt5,0);print_r(debug_backtrace());
    $tmp2 = substr($tmp3['concepto'], 5);
    $_SESSION['concepto'][$rowAsientos['concepto']] = trim($tmp2);
  }
  fb($_SESSION['concepto']);
  switch(intval($rowAsientos['concepto'])) {
    case 30:
      // if concepto==30 buscar datos de fletero.
      //$sqlFletero = "select * from [sqlcoop_dbimplemen].[dbo].ordservi, [sqlcoop_dbimplemen].[dbo].fleteros where [sqlcoop_dbimplemen].[dbo].ordservi.importe=$rowDetalles[cantidad] and [sqlcoop_dbimplemen].[dbo].ordservi.idtranglob=$rowAsientos[idtranglob] and [sqlcoop_dbimplemen].[dbo].ordservi.fletero=[sqlcoop_dbimplemen].[dbo].fleteros.fletero";
      
      $sqlFletero = "SELECT nombre FROM [sqlcoop_dbimplemen].[dbo].ordservi, [sqlcoop_dbimplemen].[dbo].fleteros WHERE [sqlcoop_dbimplemen].[dbo].ordservi.idtranglob=$rowAsientos[idtranglob] AND [sqlcoop_dbimplemen].[dbo].ordservi.fletero=[sqlcoop_dbimplemen].[dbo].fleteros.fletero";
      fb($sqlFletero, $rowAsientos['asiento']);
      $stmt3 = odbc_exec( $mssql, $sqlFletero);
      if( $stmt3 === false ){
        echo "3. Error in executing query.</br>$sqlFletero<br/>";
        die( print_r( sqlsrv_errors(), true));
      }
      $rowFletero = odbc_fetch_array($stmt3, 0);
      $nombre = $rowFletero['nombre'];
      break;
    case 1039:
      $sqlFletero = "SELECT nombre FROM [sqlcoop_dbimplemen].[dbo].histccfl, [sqlcoop_dbimplemen].[dbo].fleteros WHERE [sqlcoop_dbimplemen].[dbo].histccfl.idtranglob=$rowAsientos[idtranglob] AND [sqlcoop_dbimplemen].[dbo].histccfl.fletero=[sqlcoop_dbimplemen].[dbo].fleteros.fletero";
      fb($sqlFletero, $rowAsientos['asiento']);
      $stmt3 = odbc_exec( $mssql, $sqlFletero);
      if( $stmt3 === false ){
        echo "3. Error in executing query.</br>$sqlFletero<br/>";
        die( print_r( sqlsrv_errors(), true));
      }
      $arrayDetalle=explode("DESDE VI", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $rowFletero = odbc_fetch_array($stmt3);
      $nombre = $rowFletero['nombre'];
      break;
    case 1057:
      // ajuste negativo cliente
      $arrayDetalle=explode("Cliente:", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre="".$arrayDetalle[1];
      break;
    case 1024:
      // deposito bancario
      $arrayDetalle=explode("Observación:", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre=$arrayDetalle[1];
      break;
    case 1029:
    case 1030:
      // deposito bancario
      $arrayDetalle=explode("Nombre:", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre="Proveedor ".$arrayDetalle[1];
      break;
      

    case 1031:
      // deposito bancario
      $arrayDetalle=explode("Nombre:", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre="".$arrayDetalle[1];
      break;
    case 1002:
      // anulacion contable
      // Anulación de Asiento : COMPRAS : 0007 - 0000000482 
      $arrayDetalle=explode("Anulación de Asiento :", utf8_encode($rowAsientos['detalle']));
      $detalle="Anulación de Asiento";
      $nombre="".$arrayDetalle[1];
      break;
    case 1025:
      // FACTURA A 0001-00000315 Fletero: Fogel Daniel Albino 
      if(strpos ( $rowAsientos['detalle'], 'Proveedor:')){
        $arrayDetalle=explode("Proveedor:", utf8_encode($rowAsientos['detalle']));
        @$nombre="Proveedor ".$arrayDetalle[1];
      }elseif(strpos ( $rowAsientos['detalle'], 'Fletero:')){
        $arrayDetalle=explode("Fletero:", utf8_encode($rowAsientos['detalle']));
        @$nombre="Fletero ".$arrayDetalle[1];
      }
      $detalle=$arrayDetalle[0];
      break;
      
    case 1033:
      // pago vario
      //COMPROBANTE DE CAJA 0007-00000086 por FLETES Y EXTRACTORA MORGADO // 38
      $detalle=trim(substr(utf8_encode($rowAsientos['detalle']),0,34));
      $nombre=trim(substr(utf8_encode($rowAsientos['detalle']),38));
      break;
    case 1034:
      // recibo vario
      // COMPROBANTE DE CAJA 0007-00000097 por saldo ddjj IVA octubre 2015. 
      $arrayDetalle=explode("Deudor:", utf8_encode($rowAsientos['detalle']));
      $arrayDetalle=explode(" por ", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre=$arrayDetalle[1];
      break;
    case 24:
      // liquidacion de fletero
      // FACTURA A 0002-0000000009 al fletero Mainini Miguel Angel 
      $arrayDetalle=explode("al fletero", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre="Fletero ".$arrayDetalle[1];
      break;
    case 6:
    case 11:
    case 1035:
    case 4:
    case 8:
    case 9:
    case 1018:
      // facturas simples y recibos a clientes
      $arrayDetalle=explode("Cliente:", utf8_encode($rowAsientos['detalle']));
      $detalle=$arrayDetalle[0];
      $nombre=$arrayDetalle[1];
      break;
    case 22:
      $nombre="Asiento Manual";
  }
  //(var_dump($rowAsientos['concepto']));
//select * from dbo.ctafle where idtranglob=6472
    
  $label = ($_SESSION['transporte_libros_contables'][$rowAsientos['cod_libro']]=='DIARIO')?'danger':'success';
  echo "<tbody class='asientoTransporte' id='$rowAsientos[idtranglob]_$rowAsientos[concepto]'><tr class='encabezaAsiento encabezado2' style='line-height:12em;' title='".$rowAsientos['concepto']."-{$_SESSION['concepto'][$rowAsientos['concepto']]}'><td align='left' rowspan='".((isset($nombre))?'1':'2')."'>$detalle</td><td colspan='2'>($fecha) Nº $rowAsientos[asiento]</td></tr><tr class='encabezaAsiento2'>".(isset($nombre)?"<td><b>$nombre</b></td>":'')."<td colspan='2'><span class='label label-$label'>{$_SESSION['transporte_libros_contables'][$rowAsientos['cod_libro']]}</span></td></tr>";
  if($dbg)echo "<tr><td colspan=2>$sqlDetalles</td></tr>";
  $debe  =$haber=0;
  while($rowDetalles = odbc_fetch_array($stmt2)){
    $monto = sprintf("%.2f",$rowDetalles['cantidad']);
    $monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
    if(isset($_REQUEST['fuzzy'])){
      $act = (($rowDetalles['cantidad']>=($_REQUEST['importe']-$_REQUEST['fuzziness'])) && ($rowDetalles['cantidad']<=($_REQUEST['importe']+$_REQUEST['fuzziness'])))?" montoBuscado":'';
    } else {
      $act = ($rowDetalles['cantidad']==$_REQUEST['importe'])?" montoBuscado":'';
    }
    if($rowDetalles['debe']<>0){
      echo "<tr class='fila'><td class='cuentaD'>($rowDetalles[cuentacont]) $rowDetalles[nombre]</td><td class='debe$act'>$monto</td><td class='haber'>&nbsp;</td></tr>";
      $debe+=$rowDetalles['cantidad'];
    }else{
      echo "<tr class='fila'><td class='cuentaH'>($rowDetalles[cuentacont]) $rowDetalles[nombre]</td><td class='debe'></td>&nbsp;<td class='haber$act'>$monto</td></tr>";
      $haber+=$rowDetalles['cantidad'];
    }
  }
  echo "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debe)), 2, ',', '.')."</td><td class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haber)), 2, ',', '.')."</td></tr></tbody>";	
}

if(!isset($sqlDetalles))echo "<tbody><tr><td colspan='2'>No hay resultados TRANSPORTE</td></tr></tbody>";
?>
