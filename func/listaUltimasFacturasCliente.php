<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
 // print_r($_POST);
 // $array=array();
if(isset($_POST['idCliente'])){
	// borro todos los registros de este cliente
	$sqlClientes = "SELECT DISTINCT socio, numeroFactura, periodo, facturas.idFacturaRecibida, bb, cargoFijo, cargoVariable, usoRed, interesMora, IVA27, cargoBB, IVA21, impuestosInternos, otros, celulares.celular FROM `movistar.clientes` as clientes, `movistar.facturasrecibidas` as facturas, `movistar.facturasitems` as items, `movistar.celulares` as celulares WHERE clientes.idCliente='$_POST[idCliente]' AND items.idFacturaRecibida=facturas.idFacturaRecibida AND clientes.idCliente=facturas.idCliente AND celulares.celular=items.celular ORDER BY periodo DESC, numeroFactura ASC;";
	 echo $sqlClientes;
	//id 	celular 	idCliente 	bb 	socio 	variosClientes 	idFacturaRecibida 	numeroFactura 	periodo 	idCliente 	numeroNC 	idItemFactura 	cargoFijo 	cargoBB 	cargoVariable 	usoRed 	interesMora 	ingresosBrutos 	IVA21 	IVA27 	IVA10 	impuestosInternos 	idFacturaRecibida 	celular 	otros
	
	
	//id 	 idCliente 	 variosClientes 	idFacturaRecibida 	 idCliente 	numeroNC 	idItemFactura 	ingresosBrutos 	IVA10 	idFacturaRecibida 	 	
	$result = $mysqli->query($sqlClientes);
	$tabla = "";$a=0;$q=0;
	while($fila = $result->fetch_assoc()){
		$socio = ($fila['socio']==1)?' | <b>SOCIO</b>':'';
		if(!isset($encabezado)||$encabezado<>$fila['numeroFactura']){
			$q++;
			if($q==4)break;
			$tabla .= "<tr><td colspan='8'><b>".substr($fila['numeroFactura'],-9)." | ".substr($fila['periodo'],4).'/'.substr($fila['periodo'],0,4)."$socio</b> <a href='/ypf/cargaMovistar.php?id=$fila[idFacturaRecibida]'><i class='glyphicon glyphicon-pencil'></i></a></td></tr>";
			$encabezado=$fila['numeroFactura'];
		}
		$bb = ($fila['bb']==1)?'BB/Internet':'';
		$total = sprintf("%01.2f", $fila['cargoFijo']+$fila['cargoVariable']+$fila['usoRed']+$fila['interesMora']+$fila['IVA27']+$fila['cargoBB']+$fila['IVA21']+$fila['impuestosInternos']+$fila['otros']);
		
		$tabla .= "<tr id='g$fila[celular]'><td>$fila[celular]</td>
		<td>".sprintf("%01.2f", ($fila['cargoFijo']+$fila['cargoVariable']+$fila['usoRed']+$fila['interesMora']))."</td><td>".sprintf("%01.2f", $fila['IVA27'])."</td><td>".sprintf("%01.2f", $fila['cargoBB'])."</td><td>".sprintf("%01.2f", $fila['IVA21'])."</td><td>".sprintf("%01.2f", $fila['impuestosInternos'])."</td><td>".sprintf("%01.2f", $fila['otros'])."</td><td>$total</td></tr>";
		// <td><input type='text' name='ingresosBrutos[]' class='input-sm' value='0' required='required' pattern='[0-9\.]{1,}' maxlength='5'/></td>
	}
	$result->close();
	echo $tabla;
}
?>