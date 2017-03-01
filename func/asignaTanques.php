<?php
// detalleOrden.php
// actauliza cuadrito con las ultimas ordenes cargadas con links para ver detalles

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
// print_r($_POST);
//Array ( [totalCalculado] => 34000 [tq] => Array ( [2] => 18000 [6] => 6000 [5] => 5000 [3] => 5000 ) [controlIngreso] => Array ( [2] => 12000 [6] => 12000 [5] => 5000 [3] => 5000 ) )
$sqlOrdenes = "SELECT * FROM pedidos WHERE idOrden='$_POST[idOrden]';";
// echo $sqlOrdenes;
$result = $mysqli->query($sqlOrdenes);
// [2068] => D-Euro [2069] => Ultra [2076] => Premium [2078] => Super )
while($fila = $result->fetch_assoc()){
	// sumar para productos multiples
	if($fila['idArticulo']==2068)$litros=$_POST['tq'][1]+$_POST['tq'][4];
	elseif($fila['idArticulo']==2069)$litros=$_POST['tq'][2]+$_POST['tq'][6];
	elseif($fila['idArticulo']==2076)$litros=$_POST['tq'][5];
	else $litros=$_POST['tq'][3];
	
	$sql1 = "UPDATE pedidos SET litrosEntregados=$litros WHERE idArticulo='$fila[idArticulo]' AND idOrden='$_POST[idOrden]'";
	$res1 = $mysqli->query($sql1);
	// echo $sql1;
}
$litrosParaMail='';
foreach($_POST['tq'] as $tank => $litros){
	// idOrden	int(4)			No	Ninguna		  Cambiar	  Eliminar	 Más 
	//idTanque	enum('1', '2', '3', '4', '5', '6')	utf8_spanish_ci		No	Ninguna		  Cambiar	  Eliminar	 Más 
	//	litrosDespachados
	$sql3 = "INSERT INTO recepcion VALUES ('$_POST[idOrden]', '$tank', '$litros') ON DUPLICATE KEY UPDATE litrosDespachados='$litros'";
	$litrosParaMail .= $articulo[$tanques[$tank]].", Tanque $tank, $litros litros\n";
	
	//$tanques = array(1=>2068, 2=>2069, 3=>2078, 4=>2068, 5=>2076, 6=>2069);
	// echo $sql3.'<br/>';
	$res3 = $mysqli->query($sql3);
}

$sql2 = "UPDATE ordenes SET entregado=1, fechaEntregada=now(), observado='$_POST[observado]', observaciones='$_POST[observaciones]' WHERE idOrden='$_POST[idOrden]'";
$res2 = $mysqli->query($sql2);
// Mando mail
ini_set("SMTP","localhost");
ini_set("SMTP_PORT", 25);
ini_set('sendmail_from', 'estcotrans@gmail.com');
$Name = "Coope"; //senders name
$email = "estcotrans@gmail.com"; //senders e-mail adress
$recipient = "Maxi <maxi.schimmel@gmail.com>";//, José <josedenk@hotmail.com.ar>"; //recipient
$mail_body = "Se recibió combustible:\n$litrosParaMail\n\n<a href='http://cooptransp.dyndns.org:3128/ypf/estadoTanques.php'>Ver el estado de tanques</a>"; //mail body
$subject = "Combustible recibido"; //subject
$header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields
mail($recipient, $subject, $mail_body, $header); //mail command :)
if($res2) echo "yes||<div class='alert'>Camión recibido correctamente.<br/> <b>No olvidar registrar en CEM.</b></div>";
//print_r($articulo);
?>
