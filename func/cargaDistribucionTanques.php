<?php
// detalleOrden.php
// actauliza cuadrito con las ultimas ordenes cargadas con links para ver detalles

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
if(isset($_GET['idOrden'])){
	$sqlTanques = "select top 6 dbo.CierresTurno.idCierreTurno as idT, CONVERT(VARCHAR(5), dbo.CierresTurno.Fecha,4) AS Fecha, CONVERT(VARCHAR(8), dbo.CierresTurno.Fecha, 108), Descarga, Medicion, Vendido, StockActual, Capacidad, CAST(round(Medicion/Capacidad*100,2) AS decimal(4, 2)) as Ocupado, (Capacidad-Medicion) as Disponible, dbo.CierresDetalleTanques.IdTanque,  dbo.CierresDetalleTanques.IdArticulo as idArticulo, dbo.CierresTurno.Fecha as fechaCierre from dbo.Tanques, dbo.CierresDetalleTanques, dbo.Articulos, dbo.CierresTurno WHERE dbo.CierresDetalleTanques.IdArticulo=dbo.Articulos.IdArticulo AND dbo.CierresTurno.IdCierreTurno=dbo.CierresDetalleTanques.IdCierreTurno AND dbo.Tanques.idTanque=dbo.CierresDetalleTanques.idTanque order by dbo.CierresDetalleTanques.IdCierreTurno DESC, idTanque;";
	$stmt = odbc_exec($mssql, $sqlTanques);
	if( $stmt === false ){
		 echo "Error in executing query.</br>";
		 die( print_r( sqlsrv_errors(), true));
	}
	while($row = odbc_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		$tanque[$row['idArticulo']][$row['IdTanque']] = $row;
	}




	// pide tooltip detalles orden
	$sqlOrdenes = "SELECT * FROM pedidos WHERE idOrden='".substr($_GET['idOrden'],1)."';";
	echo $sqlOrdenes;
	$result = $mysqli->query($sqlOrdenes);
	// print_r($tanque);echo "<br><br><br>";
	// print_r($tanque[2068]);
	while ($fila = $result->fetch_assoc()){
		echo "<tr><td>";
		if(!isset($idorden)){echo "<input type='hidden' name='idOrden' value='".substr($_GET['idOrden'],1)."'/>"; $idorden=true;}
		// echo "UD";
		// [2] => Array ( [idT] => 2026 [Fecha] => 20.11 [] => 05:43:59 [Descarga] => .00 [Medicion] => 10097.00 [Vendido] => 433.40 [StockActual] => 8540.96 [Capacidad] => 21000 [Ocupado] => 48.08 [Disponible] => 10903.00 [IdTanque] => 2 [idArticulo] => 2069 [fechaCierre] => DateTime Object ( [date] => 2012-11-20 05:43:59 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) ) [6] => Array ( [idT] => 2026 [Fecha] => 20.11 [] => 05:43:59 [Descarga] => .00 [Medicion] => 16222.00 [Vendido] => 1078.96 [StockActual] => 30737.88 [Capacidad] => 40000 [Ocupado] => 40.56 [Disponible] => 23778.00 [IdTanque] => 6 [idArticulo] => 2069 [fechaCierre] => DateTime Object ( [date] => 2012-11-20 05:43:59 [timezone_type] => 3 [timezone] => America/Argentina/Buenos_Aires ) ) )
		if($fila['idArticulo']=='2068'||$fila['idArticulo']=='2069'){
			$st=" style='width:222px'";
			$multiple=true;
		} else {
			$st="";
			unset($multiple);
		}
		echo "<div class='dibuTanques'><ul class='barGraph'$st>";
		$derecha=0;
		$distriTanques = "";
		foreach($tanque[$fila['idArticulo']] as $idTanque => $detalleTanque){
			$alturaTanque = ($detalleTanque['Capacidad']/300);
			$ocupado = ($detalleTanque['Medicion'] + $detalleTanque['Vendido'])/300;
			$libre = round($detalleTanque['Capacidad'] - ($detalleTanque['Medicion'] + $detalleTanque['Vendido']));
			echo"<li class='p1' style='height: {$alturaTanque}px; left: {$derecha}px;'>$libre lts disponibles</li>";
			echo"<li class='p2 p$fila[idArticulo]' style='height: {$ocupado}px; left: {$derecha}px;'>".round($detalleTanque['Medicion'] + $detalleTanque['Vendido'])." lts</li>";
			$derecha += 120;
			$lts = (!isset($multiple))?(1000*(round(($fila['litrosDespachados'])/1000))):0;
			$maximo = (1000*(round(($libre*1.25)/1000)));
			$digitos = strlen((string) $maximo);
			$control = (!isset($multiple))?(1000*(round(($fila['litrosDespachados'])/1000))):((1000*(round(($fila['litrosDespachados'])/1000)))/2);
			$distriTanques.="Tanque $idTanque: <input type='text' name='tq[$idTanque]' class='input-sm asignacion' value='$lts' max='$maximo' size='$digitos' /> lts<br/>";
			$distriTanques.="<input type='hidden' name='controlIngreso[$idTanque]' id='controlIngreso[$idTanque]' class='controlIngreso' value='$control'/>";
		}
		echo "</ul></div>";
		echo"</td><td><h4>{$articulo[$fila['idArticulo']]}: <b>".(1000*(round($fila['litrosDespachados']/1000)))." lts</b></h4><br/><div class='distriTanques'>$distriTanques</div></td></tr>";
	}
	echo "<tr><td><label class='checkbox'><input type='checkbox' name='observado' id='observado' value='1'/>Recepción OBSERVADA</label><textarea id='observaciones' name='observaciones' class='textarea' style='display:none' placeholder='Escribir las observaciones que se hayan hecho' rows='3'></textarea></td><td><br/><br/><br/><button class='btn btn-primary btn-big' id='x$_GET[idOrden]'>Cargar tanques &raquo;</button></td></tr>";
}
?>
