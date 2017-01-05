<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
// print_r($_POST);
if(isset($_POST['actualiza'])){
	// borro todos los registros de este cliente
	$sqlOrdenes = "DELETE FROM `movistar.celulares` WHERE idCliente='$_POST[idCliente]';";
	//echo $sqlOrdenes;
	$result = $mysqli->query($sqlOrdenes);
}
$a=0;
foreach($_POST['celu'] as $celu){
	if($celu<>''){
		$a++;
		$bb = (isset($_POST['bb'][$a])&&$_POST['bb'][$a]==1)?1:'0';
		$socio = (isset($_POST['socio'])&&$_POST['socio']==1)?1:'0';
		$facturaB = (isset($_POST['facturaB'])&&$_POST['facturaB']==1)?1:'0';
		$variosClientes = (isset($_POST['variosClientes'])&&$_POST['variosClientes']==1)?1:'0';
		$sqlOrdenes = "INSERT INTO `movistar.celulares` (celular, idCliente, bb) VALUES ('$celu', '$_POST[idCliente]', $bb);";
		// echo $sqlOrdenes;
		$result = $mysqli->query($sqlOrdenes);
		$sql2 = "UPDATE `movistar.clientes` SET socio='$socio', variosClientes='$variosClientes', facturaB='$facturaB' , idClienteMovistar='$_POST[idClienteMovistar]' WHERE idCliente='$_POST[idCliente]';";
		// echo $sql2;
		$result2 = $mysqli->query($sql2);
	}
}
if($result)echo"yes";
?>