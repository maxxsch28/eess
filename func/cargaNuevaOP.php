<?php
// cargaNuevaOP.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

if(!isset($_POST['actualiza'])){
	if(isset($_POST['op'])){
		$sqlOrdenes = "SELECT * FROM `ordenes` WHERE op='$_POST[op]';";
		$result = $mysqli->query($sqlOrdenes);
	}
	// echo "sdsdd";
	/* Select queries return a resultset */
	if(isset($result)||!isset($_POST['op'])){
		if(isset($result)&&$result->num_rows>0){
			// esta OP ya está cargada
			echo "Atención, esta orden ya está cargada";
			$result->close();
		} else {
			// grabo los datos en la db
			if(isset($result))$result->close();
			//$fechaPedido = substr($_POST['fechaPedido'],6).'-'.substr($_POST['fechaPedido'],3,2).'-'.substr($_POST['fechaPedido'],0,2);
			$fechaPedido = explode('/', $_POST['fechaPedido']);
			if(isset($_POST['fechaEstimada'])&&$_POST['fechaEstimada']<>'00/00/0000'){
				$fechaEstimada = explode('/', $_POST['fechaEstimada']);
				$fEstimada = "$fechaEstimada[2]-$fechaEstimada[1]-$fechaEstimada[0]";
			} else $fEstimada='00/00/0000';
			$op=(isset($_POST['op']))?$_POST['op']:'';
			$sqlOrden = "INSERT INTO `ordenes`(`op`, `fechaPedido`, `fechaEstimada`) VALUES ('$op', '$fechaPedido[2]-$fechaPedido[1]-$fechaPedido[0]', '$fEstimada');";
			//echo $sqlOrden;
			if($result = $mysqli->query($sqlOrden)){
				$idOrden = $mysqli->insert_id;
				$insert="";
				if(isset($_POST['c2068']))$insert.=" ($idOrden, '2068', $_POST[c2068]),";
				if(isset($_POST['c2069']))$insert.=" ($idOrden, '2069', $_POST[c2069]),";
				if(isset($_POST['c2076']))$insert.=" ($idOrden, '2076', $_POST[c2076]),";
				if(isset($_POST['c2078']))$insert.=" ($idOrden, '2078', $_POST[c2078]),";
				$insert = substr ($insert, 0, -1);
				$sqlPedido = "INSERT INTO `pedidos` (idOrden, idArticulo, litrosPedidos) VALUES $insert;";
				//echo $sqlPedido;
				$result2 = $mysqli->query($sqlPedido);
				$sqlEstado = "INSERT INTO `estados` (idOrden, fechaEstado, estado) VALUES ($idOrden, NOW(), '$_POST[estado]')";
				$result3 = $mysqli->query($sqlEstado);
				if($result2&&$result3){
					// exito
					echo "Orden cargada correctamente";
				} else {
					echo "Ocurrió un error mientras cargaba la orden. Verificar.";
				}
				
			}
		}
	}
} else {
	// actualiza orden cargada
	// verifica que si trae cargada numero de OP o no exista previamente o sea esta misma orden
	if(isset($_POST['op'])){
		$sqlOrdenes = "SELECT * FROM `ordenes` WHERE op='$_POST[op]';";
		$result = $mysqli->query($sqlOrdenes);
		$orden = $result->fetch_assoc();
	}
	//echo $sqlOrdenes;
	/* Select queries return a resultset */
	if(isset($result)||!isset($_POST['op'])){
		// echo "aca";
		$idOrden = $_POST['actualiza'];
		if(isset($result)&&$result->num_rows>0&&$orden['idOrden']<>$_POST['actualiza']){
			// esta OP ya está cargada
			echo "Atención, el número de orden ya está cargada previamente";
			$result->close();
		} else {
			// grabo los datos en la db
			if(isset($result))$result->close();
			$fechaPedido = explode('/', $_POST['fechaPedido']);
			$fechaEstimada = explode('/', $_POST['fechaEstimada']);
			$fechaDespacho = "";
			if(isset($_POST['entregado'])&&$_POST['entregado']){
				$fechaDespacho = ", fechaDespacho='$fechaEstimada[2]-$fechaEstimada[1]-$fechaEstimada[0]'";
				$_POST['estado']="Entregado";
			}
			$op=(isset($_POST['op']))?$_POST['op']:'';
			$sqlOrden = "UPDATE `ordenes` SET `op`='$op',`fechaPedido`='$fechaPedido[2]-$fechaPedido[1]-$fechaPedido[0]',`fechaEstimada`='$fechaEstimada[2]-$fechaEstimada[1]-$fechaEstimada[0]'$fechaDespacho WHERE idOrden=$idOrden;";
			//echo $sqlOrden."<br/>";
			if($result = $mysqli->query($sqlOrden)){
				$update="";
				if(isset($_POST['c2068']))$insert2068=" litrosPedidos=$_POST[c2068]";
				if(isset($_POST['e2068'])&&$_POST['e2068']>0)$insert2068.=", litrosEntregados='$_POST[e2068]'";
				if(isset($insert2068))$update[]=("UPDATE `pedidos` SET $insert2068 WHERE idOrden='$idOrden' AND idArticulo='2068'; ");
				
				if(isset($_POST['c2069']))$insert2069=" litrosPedidos=$_POST[c2069]";
				if(isset($_POST['e2069'])&&$_POST['e2069']>0)$insert2069.=", litrosEntregados=$_POST[e2069]";
				if(isset($insert2069))$update[]=("UPDATE `pedidos` SET $insert2069 WHERE idOrden='$idOrden' AND idArticulo='2069'; ");
				
				if(isset($_POST['c2076']))$insert2076=" litrosPedidos='$_POST[c2076]'";
				if(isset($_POST['e2076'])&&$_POST['e2076']>0)$insert2076.=", litrosEntregados='$_POST[e2076]'";
				if(isset($insert2076))$update[]=("UPDATE `pedidos` SET $insert2076 WHERE idOrden='$idOrden' AND idArticulo='2076'; ");
				
				if(isset($_POST['c2078']))$insert2078=" litrosPedidos=$_POST[c2078]";
				if(isset($_POST['e2078'])&&$_POST['e2078']>0)$insert2078.=", litrosEntregados=$_POST[e2078]";
				if(isset($insert2078))$update[]=("UPDATE `pedidos` SET $insert2078 WHERE idOrden='$idOrden' AND idArticulo='2078'; ");
				//print_r($update);
				foreach($update as $key){
					$mysqli->query($key);
				}
				$sqlEstado = "SELECT idEstado, estado FROM `estados` WHERE idOrden='$idOrden' ORDER BY idEstado DESC LIMIT 1;";
				$result = $mysqli->query($sqlEstado);
				$orden = $result->fetch_assoc();
				if(isset($orden)&&$orden['estado']<>$_POST['estado']){
					$sqlEstado = "INSERT INTO `estados` (idOrden, fechaEstado, estado) VALUES ($idOrden, NOW(), '$_POST[estado]')";
					//echo $sqlEstado;
					$actEstado=true;
					$result3 = $mysqli->query($sqlEstado);
				}
				if(((isset($actEstado)&&$result3)||(!isset($actEstado)&&1))){
					// exito
					echo "Orden cargada correctamente";
				} else {
					echo "Ocurrió un error mientras cargaba la orden. Verificar.";
				}
			}
		}
	} else {
		// echo "asa";
	}
}
?>