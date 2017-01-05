<?php
// ultimasOrdenes.php
// actauliza cuadrito con las ultimas ordenes cargadas con links para ver detalles
// DEPRECATED.

include('../include/inicia.php');
{
	$sqlOrdenes = "SELECT DISTINCT(ordenes.idOrden), op, DATE_FORMAT(fechaPedido, '%e/%m/%Y') as fechaPedido, fechaDespacho, fechaEstado, estado FROM ordenes, pedidos, estados WHERE ordenes.idOrden=pedidos.idOrden AND estados.idOrden=ordenes.idOrden ORDER BY fechaPedido DESC, idOrden DESC LIMIT 5;";
	/* Select queries return a resultset */
	$tabla="";
	if ($result = $mysqli->query($sqlOrdenes)){
		//idOrden 	op 	fechaPedido Descendente 	fechaDespacho 	idPedido 	idOrden 	idArticulo 	litrosPedidos 	litrosEntregados 	idEstado 	idOrden 	fechaEstado 	estado
		/* free result set */
		while ($fila = $result->fetch_assoc()) {
			//Array ( [idOrden] => 7 [op] => 4294967295 [fechaPedido] => 2012-05-29 [fechaDespacho] => 0000-00-00 [fechaEstado] => 2012-05-29 [estado] => ) 
			$op		= ($fila['op']<>'')?$fila['op']:$fila['idOrden'];
			$estado	= ($fila['estado']<>'')?$fila['estado']:'S/D';
			$tabla .= "<tr><td>$op</td><td>$fila[fechaPedido]</td><td>$estado</td><td><a href='#' class='btn btn-danger infoOP' rel='popover' title='Detalle orden'>i</a></td></tr>";
		}
		$result->close();
	}
}
?>
