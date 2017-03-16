<?php
/*
  ajaxBuscaAsientosPorImporte.php
  
*/
include_once('../include/inicia.php');

$limit=11;
$offset=0;



$rangoFin=($_REQUEST['rangoFin']=='12/31/69')?'12/31/2069':$_REQUEST['rangoFin'];
$andFecha=(isset($_REQUEST['rangoInicio']))?" AND Fecha>='{$_REQUEST['rangoInicio']}' AND Fecha<='{$rangoFin} 23:59:59'":'';

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
    $rangoInicio = substr($_REQUEST['rangoInicio'], 6).'-'.substr($_REQUEST['rangoInicio'], 0,2).'-'.substr($_REQUEST['rangoInicio'], 3,2);
    $rangoFin = substr($_REQUEST['rangoFin'], 6).'-'.substr($_REQUEST['rangoFin'], 0,2).'-'.substr($_REQUEST['rangoFin'], 3,2);
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

/* $sqlAsientos = ";WITH Results_CTE AS (SELECT ROW_NUMBER() OVER (ORDER BY dbo.AsientosDetalle.idAsiento) AS RowNum, DISTINCT dbo.AsientosDetalle.idAsiento, , Detalle, Fecha, dbo.ModelosContables.Nombre FROM dbo.AsientosDetalle, dbo.Asientos, dbo.ModelosContables WHERE dbo.AsientosDetalle.idAsiento=dbo.Asientos.idAsiento AND dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable AND Importe=$_REQUEST[importe] AND Detalle NOT LIKE ('Transf. de PLAYA a Tesoreria') $andFecha) SELECT * FROM Results_CTE WHERE RowNum >= $offset AND RowNum < $offset + $limit;"; */

$sqlAsientos = trim("SELECT DISTINCT dbo.AsientosDetalle.idAsiento, Detalle, Fecha FROM dbo.AsientosDetalle, dbo.Asientos WHERE dbo.AsientosDetalle.idAsiento=dbo.Asientos.idAsiento{$fuzziness}{$leyenda}{$cuenta} AND (Detalle NOT LIKE ('Transf. de %') OR Detalle is NULL) $andFecha{$excluyeAnulados};");
fb($sqlAsientos);

// AND (dbo.asientos.IdModeloContable=dbo.ModelosContables.IdModeloContable OR dbo.asientos.IdModeloContable is NULL)

// , dbo.ModelosContables

//, dbo.ModelosContables.Nombre

//echo $sqlAsientos;

$stmt = sqlsrv_query( $mssql, $sqlAsientos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlAsientos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowAsientos = sqlsrv_fetch_array($stmt)){
	$sqlDetalles = "SELECT Importe, dbo.asientosdetalle.IdCuentaContable, Descripcion, DebitoCredito, Codigo FROM dbo.asientosdetalle, dbo.CuentasContables WHERE dbo.CuentasContables.IdCuentaContable=dbo.AsientosDetalle.IdCuentaContable AND dbo.AsientosDetalle.idAsiento=$rowAsientos[idAsiento] ORDER BY DebitoCredito ASC$orden;";
	//echo $sqlDetalles;
	$stmt2 = sqlsrv_query( $mssql, $sqlDetalles);
	if( $stmt2 === false ){
		 echo "2. Error in executing query.</br>$sqlDetalles<br/>";
		 die( print_r( sqlsrv_errors(), true));
	}
	$fecha = date_format($rowAsientos['Fecha'], "d/m/Y");
	echo "<tbody class='asiento' id='$rowAsientos[idAsiento]'><tr class='encabezaAsiento'><td align='left'>$rowAsientos[Detalle], $fecha</td><td colspan='3'>Nº $rowAsientos[idAsiento]</td></tr>";
	$debe=$haber=0;
	while($rowDetalles = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)){
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
