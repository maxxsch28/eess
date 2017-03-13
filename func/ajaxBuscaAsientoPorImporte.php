﻿<?php
// calculaPromedios.php
include_once($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$r2 = explode("/", $_REQUEST['rangoFin']);
$r1 = explode("/", $_REQUEST['rangoInicio']);
// $rangoFin="$r2[1]/$r2[0]/$r2[2]"; //odbc
// $rangoInicio="$r1[1]/$r1[0]/$r1[2]"; //odbc
$rangoFin="$r2[2]/$r2[0]/$r2[1]";
$rangoInicio="$r1[2]/$r1[0]/$r1[1]";

$rangoFin=($rangoFin=='12/31/69')?'12/31/2069':$rangoFin;
$andFecha=(isset($rangoInicio))?" AND Fecha>='{$rangoInicio}' AND Fecha<='{$rangoFin} 23:59:59'":'';

$fuzziness=(isset($_REQUEST['fuzzy']))?" AND Importe>=".floor(($_REQUEST['importe']-$_REQUEST['fuzziness']))." AND Importe<=".ceil($_REQUEST['importe']+$_REQUEST['fuzziness']):" AND Importe=$_REQUEST[importe]";

$leyenda=(isset($_REQUEST['leyenda'])&&strlen($_REQUEST['leyenda'])>1)?" AND Detalle LIKE ('%$_REQUEST[leyenda]%')":'';

$cuenta=(isset($_REQUEST['cuentaEESS'])&&$_REQUEST['cuentaEESS']>0)?" AND IdCuentaContable=$_REQUEST[cuentaEESS]":'';



// Podría estar bueno agregar este filtro :)
$excluyeAnulados=(isset($_REQUEST['excluyeAnulados'])&&$_REQUEST['excluyeAnulados']>0)?" AND IdAsientoAnulado IS NULL AND dbo.Asientos.IdAsiento NOT IN (SELECT IdAsientoAnulado FROM dbo.Asientos WHERE IdAsientoAnulado IS NOT NULL $andFecha)":'';





if(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda||$cuenta)<>''){
  $fuzziness='';
} elseif(!isset($_REQUEST['importe'])||$_REQUEST['importe']==0&&($leyenda&&$cuenta)==''){
  echo "<tbody><tr><td colspan='2' class='act'>Ingrese parámetros de búsqueda</td></tr></tbody>";
  die;
}
fb($_POST);

// tmpBuscaAsiento
if($_REQUEST['ambito']<>'integral'){
  if(isset($_POST['idBuscaAsiento'])){
    // búsqueda basada en una ya grabada
    $sql = "UPDATE tmpbuscaasientos SET cantidadusos=cantidadusos+1 WHERE id=$_REQUEST[idBuscaAsiento]";
  } else{
    // inserto una nueva búsqueda // 28-09-1977
    $fuzyness = (isset($_REQUEST['fuzzy']))?$_REQUEST['fuzziness']:0;
    $rangoInicio = substr($rangoInicio, 6).'-'.substr($rangoInicio, 0,2).'-'.substr($rangoInicio, 3,2);
    $rangoFin = substr($rangoFin, 6).'-'.substr($rangoFin, 0,2).'-'.substr($rangoFin, 3,2);
    $sql = "INSERT INTO tmpbuscaasientos (ambito, importe, rangoInicio, rangoFin, fuzzyness, leyenda, cuentaEESS, user_id) VALUES ('$_REQUEST[ambito]', '$_REQUEST[importe]', '$rangoInicio', '$rangoFin', $fuzyness, '".((isset($_REQUEST['leyenda'])&&$_REQUEST['leyenda']>'')?mysqli_real_escape_string($mysqli, $_REQUEST['leyenda']):'')."', '".((isset($_REQUEST['cuentaEESS'])&&$_REQUEST['cuentaEESS']>0)?$_REQUEST['cuentaEESS']:0)."', $loggedInUser->user_id)";
    fb($sql);
  }
  if(!isset($_SESSION['ultimoSQL'])||$_SESSION['ultimoSQL']<>$sql){
    $result = $mysqli->query($sql);
    $_SESSION['ultimoSQL']=$sql;
  } else {
    $sql = "SELECT 1;";
  }
  //$result = $mysqli->query($sql);
}


$orden=(isset($_REQUEST['ord_imp']))?", Importe DESC":"";
$conciliando=(isset($_REQUEST['conciliando']))?"<input type='checkbox''>":"";


$sqlAsientos = trim("SELECT DISTINCT dbo.AsientosDetalle.idAsiento, Detalle, Fecha FROM dbo.AsientosDetalle, dbo.Asientos WHERE dbo.AsientosDetalle.idAsiento=dbo.Asientos.idAsiento{$fuzziness}{$leyenda}{$cuenta} AND (Detalle NOT LIKE ('Transf. de %') OR Detalle is NULL) $andFecha{$excluyeAnulados};");
//$sqlAsientos = trim("SELECT DISTINCT dbo.AsientosDetalle.idAsiento, Detalle, Fecha FROM dbo.AsientosDetalle, dbo.Asientos WHERE dbo.AsientosDetalle.idAsiento=dbo.Asientos.idAsiento{$fuzziness}{$leyenda}{$cuenta} AND (Detalle NOT LIKE ('Transf. de %') OR Detalle is NULL)  AND Fecha>='01/01/17' AND Fecha<='12/31/17 23:59:59';");
fb($sqlAsientos);

// AND (dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable OR dbo.asientos.IdModeloContable is NULL)

// , dbo.ModelosContables

//, dbo.ModelosContables.Nombre

//echo $sqlAsientos;

$stmt = odbc_exec2($mssql, $sqlAsientos, __LINE__, __FILE__);
while($rowAsientos = sqlsrv_fetch_array($stmt)){
  $sqlDetalles = "SELECT Importe, dbo.asientosdetalle.IdCuentaContable, Descripcion, DebitoCredito, Codigo FROM dbo.asientosdetalle, dbo.CuentasContables WHERE dbo.CuentasContables.IdCuentaContable=dbo.AsientosDetalle.IdCuentaContable AND dbo.AsientosDetalle.idAsiento=$rowAsientos[idAsiento] ORDER BY DebitoCredito ASC$orden;";
  //echo $sqlDetalles;
  $stmt2 = odbc_exec( $mssql, $sqlDetalles);
  if( $stmt2 === false ){
            echo "2. Error in executing query.</br>$sqlDetalles<br/>";
            die( print_r( odbc_errormsg().' -- '.odbc_error(), true));
  }
  //$fecha = date_format($rowAsientos['Fecha'], "d/m/Y");
  $fecha = $rowAsientos['Fecha'];
  echo "<tbody class='asiento' id='$rowAsientos[idAsiento]'><tr class='encabezaAsiento'><td align='left'>$rowAsientos[Detalle], $fecha</td><td colspan='3'>Nº $rowAsientos[idAsiento]</td></tr>";
  $debe=$haber=0;
  while($rowDetalles = sqlsrv_fetch_array($stmt2)){
          $monto = sprintf("%.2f",$rowDetalles['Importe']);
          $monto = number_format(str_replace(',', '.', $monto), 2, ',', '.');
          if(isset($_REQUEST['fuzzy'])){
                  $act = (($rowDetalles['Importe']>=floor($_REQUEST['importe']-$_REQUEST['fuzziness'])) && ($rowDetalles['Importe']<=ceil($_REQUEST['importe']+$_REQUEST['fuzziness'])))?" montoBuscado":'';
          } else {
                  $act = ($rowDetalles['Importe']==$_REQUEST['importe'])?" montoBuscado":'';
          }
          if($rowDetalles['DebitoCredito']==0){
                  echo "<tr class='fila'><td class='cuentaD'>($rowDetalles[IdCuentaContable] | $rowDetalles[Codigo]) $rowDetalles[Descripcion]</td><td class='debe$act'>$monto</td><td class='haber'>&nbsp;</td><td>$conciliando</td></tr>";
                  $debe+=$rowDetalles['Importe'];
          }else{
                  echo "<tr class='fila'><td class='cuentaH'>($rowDetalles[IdCuentaContable] | $rowDetalles[Codigo]) $rowDetalles[Descripcion]</td><td class='debe'>&nbsp;</td><td class='haber$act'>$monto</td><td>$conciliando</td></tr>";
                  $haber+=$rowDetalles['Importe'];
          }
  }
  echo "<tr class='fila'><td class='cuentaH'>&nbsp;</td><td class='debe cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$debe)), 2, ',', '.')."</td><td class='haber cierre'>".number_format(str_replace(',', '.', sprintf("%.2f",$haber)), 2, ',', '.')."</td><td></td></tr></tbody>";	
}
if(!isset($sqlDetalles))echo "<tbody><tr><td colspan='3'>No hay resultados EESS</td></tr></tbody>";
?>
