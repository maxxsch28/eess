<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');



$sqlClientes = "select Abonado, Cliente from `table10` order by Abonado";
$result = $mysqli->query($sqlClientes);
while($row = $result->fetch_assoc()){
	$sql = "UPDATE `movistar.celulares` SET idClienteMovistar='$row[Cliente]' WHERE celular='$row[Abonado]'";
	echo "$sql<br/>";
	$result2 = $mysqli->query($sql);
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