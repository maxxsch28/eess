<?php
// detalleOrden.php
// actauliza cuadrito con las ultimas ordenes cargadas con links para ver detalles

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
if(isset($_GET['id'])){
	// pide tooltip detalles orden
	$sqlOrdenes = "SELECT op, DATE_FORMAT(fechaPedido, '%e/%m/%Y') as fechaPedido, DATE_FORMAT(fechaDespacho, '%e/%m/%Y') as fechaDespacho, idArticulo, litrosPedidos, litrosEntregados  FROM ordenes, pedidos WHERE ordenes.idOrden=pedidos.idOrden AND ordenes.idOrden='$_GET[id]' ORDER BY fechaPedido DESC;";
	$result = $mysqli->query($sqlOrdenes);
	while ($fila = $result->fetch_assoc()){
		if(!isset($encabezado)){
			echo "<table><thead><tr><th colspan=3>Pedida el $fila[fechaPedido]</th></tr>";
			if($fila['fechaDespacho']<>'0/00/0000')echo "<tr><th colspan=3>Descargado el $fila[fechaDespacho]</th></tr>";
			$encabezado=1;
			echo "</thead><tbody>";
		}
		echo "<tr><td>".$articulo[$fila['idArticulo']]."</td><td>$fila[litrosPedidos] lts</td><td>";
		if($fila['litrosEntregados']>0)echo " (<b>$fila[litrosEntregados] lts</b>)";
		echo"</td></tr>";
	}
	$sqlEstados = "SELECT fechaEstado, estado FROM estados WHERE idOrden='$_GET[id]' ORDER BY fechaEstado DESC;";
	$result = $mysqli->query($sqlEstados);
	while ($fila = $result->fetch_assoc()){
		echo "<tr><td>$fila[fechaEstado]</td><td colspan=2>$fila[estado]</td></tr>";
	}
	echo"</tbody></table>";
	die;
}
?>
