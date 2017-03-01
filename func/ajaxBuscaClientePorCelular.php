<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
if(isset($_GET['term'])){
	// borro todos los registros de este cliente
	$sqlClientes = "SELECT celular, `movistar.celulares`.idCliente, `movistar.celulares`.idClienteMovistar FROM `movistar.celulares` WHERE (`movistar.celulares`.celular LIKE ('%$_GET[term]%') OR `movistar.celulares`.idClienteMovistar LIKE ('%$_GET[term]%')) ORDER BY `movistar.celulares`.idCliente;";
	//echo $sqlOrdenes;
	$result = $mysqli->query($sqlClientes);
	$array = "[";
	while($fila = $result->fetch_assoc()){
		//echo "$fila[celular]";
		$array.="{\"label\":\"$fila[celular] - $fila[idClienteMovistar]\", \"value\":\"$fila[idCliente]\"},";
		//[ { "id": "Sylvia borin", "label": "Garden Warbler", "value": "Garden Warbler" }, { "id": "Anas querquedula", "label": "Garganey", "value": "Garganey" } ]
	}
	$array = substr($array,0,-1)."]";
	$result->close();
	echo($array);
}
if(isset($_POST['idCliente'])){
	$sqlClientes = "select RazonSocial, Identificador from dbo.Clientes where IdCliente='$_POST[idCliente]'";
	$stmt = odbc_exec( $mssql, $sqlClientes);
	$arrayReemplazo = array('TELEFONO', 'TELEFONOS');
	$row = odbc_fetch_array($stmt);
	if(trim(str_replace($arrayReemplazo, '', $row['Identificador']))<>''){
		$identificador = "(".str_replace($arrayReemplazo, '', $row['Identificador']).")";
	} else $identificador = "";
	echo "$row[RazonSocial] $identificador";
}
?>