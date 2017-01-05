<?php
// celularesCliente.php
// Devuelve el formulario con los celulares de cada cliente

include('../include/inicia.php');
$sqlOrdenes = "SELECT * FROM `movistar.clientes` where idCliente=$_GET[idCliente] LIMIT 1";
//echo $sqlOrdenes;//celular, bb, socio, variosClientes
/* Select queries return a resultset */
$tabla="";
$celu=0;
if($result = $mysqli->query($sqlOrdenes)){
	$sql2 = "SELECT celular, bb, idClienteMovistar FROM `movistar.celulares` WHERE  idCliente=$_GET[idCliente]";
	$result2 = $mysqli->query($sql2);
	$fila0 = $result->fetch_assoc();
	 // echo $sqlOrdenes;
	//idOrden 	op 	fechaPedido Descendente 	fechaDespacho 	idPedido 	idOrden 	idArticulo 	litrosPedidos 	litrosEntregados 	idEstado 	idOrden 	fechaEstado 	estado
	/* free result set */
	while($fila = $result2->fetch_assoc()){
		if($tabla==''){
			// $tabla="<div class='btn-group' data-toggle='buttons-radio'><button type='button' class='btn btn-default".(($fila0['socio'])?" active'":'')."'>Socio</button><button type='button' class='btn btn-default".(($fila0['socio'])?'':" active'")."'>NO Socio</button></div>";
			// $tabla.="<div class='btn-group' data-toggle='buttons-radio'><button type='button' class='btn btn-default".(($fila0['variosClientes'])?" active'":'')."'>Factura incluye varios clientes</button><button type='button' class='btn btn-default".(($fila0['variosClientes'])?'':" active'")."'>Factura común</button></div>";
			// $tabla.="<div class='btn-group' data-toggle='buttons-radio'><button type='button' class='btn btn-default".(($fila0['facturaB'])?" active'":'')."'>Factura B</button><button type='button' class='btn btn-default".(($fila0['facturaB'])?'':" active'")."'>Factura A</button></div>";
			$tabla.="<div class=\"form-group\"><label for='idClienteMovistar' class=\"control-label\">Cliente Movistar</label><div class=\"controls\"><input type='text' name='idClienteMovistar' id='idClienteMovistar' value='$fila0[idClienteMovistar]' class=\"input-medium\" /></div></div><div class=\"form-group\"><label for='socio' class=\"control-label\">Es socio</label><div class=\"controls\"><input type='checkbox' name='socio' id='socio' value='1' ".(($fila0['socio'])?"checked='checked'":'')."/></div></div><div class=\"form-group\"><label for='variosClientes' class=\"control-label\">Misma factura varios clientes?</label><div class=\"controls\"><input type='checkbox' name='variosClientes' id='variosClientes' value='1' ".(($fila0['variosClientes'])?"checked='checked'":'')."/></div></div><div class=\"form-group\"><label for='facturaB' class=\"control-label\">Hace Factura B?</label><div class=\"controls\"><input type='checkbox' name='facturaB' id='facturaB' value='1' ".(($fila0['facturaB'])?"checked='checked'":'')."/></div></div>";
		}
		$celu++;
		$tabla .= "<div class=\"form-group\"><label for='periodo' class=\"control-label\">Teléfono $celu</label><div class=\"controls\"><input type='text' name='celu[$celu]' id='celu[$celu]' class=\"input-medium\" placeholder=\"Número...\" pattern=\"[0-9]{4,}\" maxlength=\"15\" value='$fila[celular]'/> <input type='checkbox' name='bb[$celu]' value='1' ".(($fila['bb']==1)?"checked='checked'":'')."/><input type='text' name='idClienteMovistar' id='idClienteMovistar' value='$fila[idClienteMovistar]' class=\"input-medium\" /></div></div>";
	}
	$result->close();
	if($tabla<>'')$tabla .="<input type='hidden' name='actualiza' id='actualiza' value='1'/>";
} 
// else echo "fallo query $sqlOrdenes";
for($i=$celu+1;$i<=$celu+5;$i++){
	if($tabla=='')$tabla="<div class=\"form-group\"><label for='idClienteMovistar' class=\"control-label\">Cliente Movistar</label><div class=\"controls\"><input type='text' name='idClienteMovistar' id='idClienteMovistar' value='$fila0[idClienteMovistar]' class=\"input-medium\" /></div></div><div class=\"form-group\"><label for='socio' class=\"control-label\">Es socio</label><div class=\"controls\"><input type='checkbox' name='socio' id='socio' value='1' ".(($fila0['socio'])?"checked='checked'":'')."/></div></div><div class=\"form-group\"><label for='variosClientes' class=\"control-label\">Misma factura varios clientes?</label><div class=\"controls\"><input type='checkbox' name='variosClientes' id='variosClientes' value='1' ".(($fila0['variosClientes'])?"checked='checked'":'')."/></div></div><div class=\"form-group\"><label for='facturaB' class=\"control-label\">Hace Factura B?</label><div class=\"controls\"><input type='checkbox' name='facturaB' id='facturaB' value='1' ".(($fila0['facturaB'])?"checked='checked'":'')."/></div></div>";
	$tabla .= "<div class=\"form-group\"><label for='periodo' class=\"control-label\">Teléfono $i</label><div class=\"controls\"><input type='text' name='celu[$i]' id='celu[$i]' class=\"input-medium\" placeholder=\"Número...\" pattern=\"[0-9]{4,}\" maxlength=\"15\"/> <input type='checkbox' name='bb[$i]' value='1'/> </div></div>";
}
echo $tabla;
?>