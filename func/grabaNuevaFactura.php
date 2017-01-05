<?php
// cargaNuevaFactura.php
// procesa los datos de una nueva factura y la graba en la base de datos
include('../include/inicia.php');
// print_r($_POST);//die;
if(isset($_POST['idFactura'])){
	// borro todos los registros de este cliente
	$sqlOrdenes = "DELETE FROM `movistar.facturasrecibidas` WHERE idCliente='$_POST[idCliente]' AND idFacturaRecibida='$_POST[idFactura]';";
	//echo $sqlOrdenes;
	$result = $mysqli->query($sqlOrdenes);
	$sqlOrdenes = "DELETE FROM `movistar.facturasitems` WHERE idFacturaRecibida='$_POST[idFactura]';";
	//echo $sqlOrdenes;
	$result = $mysqli->query($sqlOrdenes);
}
$sqlFactura = "INSERT INTO `movistar.facturasrecibidas` (`numeroFactura`, `periodo`, `idCliente`) VALUES ('$_POST[numeroFactura]', '$_POST[periodo]', '$_POST[idCliente]');";
$result = $mysqli->query($sqlFactura);
$idFactura = $mysqli->insert_id;

foreach($_POST['cargoFijo'] as $key => $value){
	if($value<>''){
		$iva27 = ($_POST['cargoVariable'][$key] + $_POST['usoRed'][$key] + $_POST['cargoFijo'][$key]) *.27;
		$ivaBB = (isset($_POST['cargoBB'][$key])&&$_POST['cargoBB'][$key]>0)?($_POST['cargoBB'][$key]*.21):0;
		$ivaBB += (isset($_POST['interesMora'][$key])&&$_POST['interesMora'][$key]>0)?($_POST['interesMora'][$key]*.21):0;
		$cargoBB = (isset($_POST['cargoBB'][$key])&&$_POST['cargoBB'][$key]>0)?$_POST['cargoBB'][$key]:0;
		$iibb = (isset($_POST['ingresosBrutos'][$key])&&$_POST['ingresosBrutos'][$key]>0)?$_POST['ingresosBrutos'][$key]:0;
		$mora = (isset($_POST['interesMora'][$key])&&$_POST['interesMora'][$key]>0)?$_POST['interesMora'][$key]:0;
		// esto es para no incluir lineas que no tengan informacion cargada
		// si el data entry no borra una linea aca evitamos que se cargue en 0 y luego compute un abono adicional
		if(($iva27 + $ivaBB + $iibb + $mora)>0){
			$sqlOrdenes = "INSERT INTO `movistar.facturasitems`(`idFacturaRecibida`, `celular`, `cargoFijo`, `cargoVariable`,  `cargoBB`,  `usoRed`, `interesMora`, `ingresosBrutos`, `IVA21`, `IVA27`, `impuestosInternos`, `otros`) VALUES ('$idFactura', '{$_POST['celular'][$key]}', '$value', '{$_POST['cargoVariable'][$key]}', '$cargoBB', '{$_POST['usoRed'][$key]}', '$mora', '$iibb', '$ivaBB', '$iva27', '{$_POST['impuestosInternos'][$key]}', '{$_POST['otros'][$key]}');";
			//echo $sqlOrdenes;
			$result = $mysqli->query($sqlOrdenes);
		}
	}
}
if($result)echo"$sqlFactura yes";
?>