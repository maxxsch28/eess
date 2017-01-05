<?php
include('include/inicia.php');



$sqlClientes = "select IdCliente, Codigo, RazonSocial, Identificador from dbo.Clientes where IdZonaCliente=2 order by RazonSocial";
$stmt = sqlsrv_query( $mssql, $sqlClientes);
$options="<option value='' selected='selected'>Seleccione cliente</option>";
$arrayReemplazo = array('TELEFONO', 'TELEFONOS');

while($row = sqlsrv_fetch_array($stmt)){
	if(trim(str_replace($arrayReemplazo, '', $row['Identificador']))<>''){
		$identificador = " (".str_replace($arrayReemplazo, '', $row['Identificador']).")";
	} else $identificador = "";
	$sql = "INSERT INTO `movistar.clientes`(`idCliente`, `nombre`, `variosClientes`, `socio`, `codigo`) VALUES ('$row[IdCliente]','$row[RazonSocial]$identificador',0,0, '$row[Codigo]')";
	echo "$sql<br/>";
	$result = $mysqli->query($sql);
}
/*$sql2 = "SELECT variosClientes, socio, idCliente FROM `movistar.celulares` GROUP BY idCliente";
echo "<br/>$sql2<br><Br>";
$result2 = $mysqli->query($sql2);
while($fila = $result2->fetch_assoc()){
	$variosClientes = ($fila['variosClientes']==1)?1:0;
	$socio = ($fila['socio']==1)?1:0;
	$sql3 = "UPDATE `movistar.clientes` SET `variosClientes`='$variosClientes', `socio`='$socio' WHERE idCliente=$fila[idCliente]";
	echo "$sql3<br>";
	$result3 = $mysqli->query($sql3);
}*/

?>