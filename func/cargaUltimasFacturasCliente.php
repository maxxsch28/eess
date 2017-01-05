<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';
if(isset($_POST['mes'])){
	$periodo = "";
	$sqlClientes = "SELECT socio, GROUP_CONCAT( DISTINCT items.idFacturaRecibida ) as IDS, GROUP_CONCAT(DISTINCT numeroFactura) as numeroFactura, COUNT(DISTINCT numeroFactura) as cantidadFacturas, periodo, celulares.idCliente, SUM(cargoFijo) AS cF, SUM(cargoBB) AS cB, SUM(cargoVariable) AS cV, SUM(usoRed) AS uR, SUM(interesMora) AS iM, SUM(ingresosBrutos) AS iB, SUM(IVA21) AS IV1, SUM(IVA27) AS IV7, SUM(impuestosInternos) AS iI, SUM(otros) AS o, facturas.idFacturaRecibida, clientes.nombre, COUNT(DISTINCT celulares.celular) as q, codigo, facturaB FROM `movistar.celulares` AS celulares, `movistar.facturasrecibidas` AS facturas, `movistar.clientes` AS clientes, `movistar.facturasitems` AS items WHERE items.idFacturaRecibida = facturas.idFacturaRecibida AND clientes.idCliente = celulares.idCliente AND celulares.idCliente = facturas.idCliente AND celulares.celular = items.celular AND periodo='$_POST[mes]' GROUP BY clientes.idCliente ORDER BY clientes.facturaB DESC, clientes.nombre ASC";//AND clientes.idCliente=1641
	
    
    
    
	$sqlClientes = "SELECT clientes.idCliente as idC, socio, GROUP_CONCAT( DISTINCT items.idFacturaRecibida ) as IDS, GROUP_CONCAT( DISTINCT numeroFactura ) as numeroFactura , COUNT( DISTINCT numeroFactura ) as cantidadFacturas, periodo, facturas.idCliente, SUM( cargoFijo ) AS cF, cargoFijo, (SUM( cargoFijo ) - cargoFijo) as Dif, SUM( cargoBB ) AS cB, SUM( cargoVariable ) AS cV, SUM( usoRed ) AS uR, SUM( interesMora ) AS iM, SUM( ingresosBrutos ) AS iB, SUM( IVA21 ) AS IV1, SUM( IVA27 ) AS IV7, SUM( impuestosInternos ) AS iI, SUM( otros ) AS o, facturas.idFacturaRecibida, clientes.nombre, (SELECT count(celular) FROM `movistar.celulares` WHERE idCliente=idC) AS q, codigo, facturaB, empleado FROM  `movistar.celulares` AS celulares,  `movistar.facturasrecibidas` AS facturas,  `movistar.clientes` AS clientes, `movistar.facturasitems` AS items WHERE items.idFacturaRecibida = facturas.idFacturaRecibida AND clientes.idCliente = celulares.idCliente AND celulares.celular = items.celular AND periodo = '$_POST[mes]' GROUP BY clientes.idCliente ORDER BY clientes.facturaB DESC , clientes.nombre ASC";// AND clientes.idCliente =1641
	echo $sqlClientes;
} else {
	$limit=' LIMIT 5';
	$sqlClientes = "select clientes.idCliente as idC, clientes.socio, facturas.idFacturaRecibida, sum(usoRed) AS uR, sum( interesMora ) AS iM, sum( ingresosBrutos ) AS iB,facturaB, (SELECT count(celular) FROM `movistar.celulares` WHERE idCliente=idC) AS q,numeroFactura, nombre, codigo, periodo, facturas.idCliente, sum(cargoFijo) as cF, cargoFijo, (SUM( cargoFijo ) - cargoFijo) as Dif, sum(cargoVariable) as cV, sum(cargoBB) as cB, sum(IVA21) as IV1, sum(IVA27) as IV7, sum(otros) as o, sum(impuestosInternos) as iI, empleado from `movistar.facturasrecibidas` as facturas, `movistar.facturasitems` as items, `movistar.clientes` as clientes WHERE facturas.idFacturaRecibida=items.idFacturaRecibida and clientes.idCliente=facturas.idCliente group by facturas.idFacturaRecibida order by facturas.idFacturaRecibida desc $limit";
}

 // echo $sqlClientes;

//id 	 idCliente 	 variosClientes 	idFacturaRecibida 	 idCliente 	numeroNC 	idItemFactura 	ingresosBrutos 	IVA10 	idFacturaRecibida 	 	
$result = $mysqli->query($sqlClientes);
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$comSocio = (isset($_POST['comSocio']))?$_POST['comSocio']:15;
$comNoSocio = (isset($_POST['comNoSocio']))?$_POST['comNoSocio']:20;
while($fila = $result->fetch_assoc()){
	$sqlClientes2 = "SELECT SUM(cargoFijo) AS cF, SUM(cargoBB) AS cB, SUM(cargoVariable) AS cV, SUM(usoRed) AS uR, SUM(interesMora) AS iM, SUM(ingresosBrutos) AS iB, SUM(IVA21) AS IV1, SUM(IVA27) AS IV7, SUM(impuestosInternos) AS iI, SUM(otros) AS o FROM `movistar.facturasitems` AS items WHERE items.idFacturaRecibida=$fila[idFacturaRecibida]";
	//echo "<br>$sqlClientes2";
	//$result2 = $mysqli->query($sqlClientes2);
	//$fila2 = $result2->fetch_assoc();
	// $sqlClientes = "select IdCliente, Codigo, RazonSocial, Identificador from dbo.Clientes where IdCliente='$fila[idCliente]'";
	// $stmt = sqlsrv_query( $mssql, $sqlClientes);
	// $cliente = sqlsrv_fetch_array($stmt);
	if(!isset($facturaB)&&$fila['facturaB']==0&&!isset($limit)){
		$facturaB=false;
		$tabla.="<tr><td colspan='14' class='label-info center'>Total Facturas B: $totalB</td></tr>";
	}
	$socio = ($fila['socio']==1)?"<span class='no' | <b>SOCIO</b></span>":'';
	$tdSocio = ($fila['socio']==1)?"<td>$fila[q]</td><td></td>":"<td></td><td>$fila[q]</td>";
    if($fila['empleado']==1)$tdSocio="<td colspan=2>-- NO --</td>";
	// $total = sprintf("%01.2f", ($fila2['cF']+$fila2['cV']+$fila2['uR'])*1.27+$fila2['iM']+($fila2['cB']*1.21)+$fila2['iI']+$fila2['o']);
	// distingue si es factura B no calcula IVA 27%
	$alicuotaIVA = ($fila['facturaB']==0)?1.27:1.21;
    if($fila['Dif']<>0 && $fila['q']==1 && $fila['cF']==2*$fila['cargoFijo']){
        // caso Giorgi, 1 factura, duplica monto.
        $fila['cF'] = $fila['cF']/2;
        $fila['cV'] = $fila['cV']/2;
        $fila['uR'] = $fila['uR']/2;
        $fila['iM'] = $fila['iM']/2;
        $fila['cB'] = $fila['cB']/2;
        $fila['iI'] = $fila['iI']/2;
        $fila['o'] = $fila['o']/2;
    }
	$total = sprintf("%01.2f", ($fila['cF']+$fila['cV']+$fila['uR'])*$alicuotaIVA+$fila['iM']+($fila['cB']*1.21)+$fila['iI']+$fila['o']);
    
	$totalFacturasCompras = sprintf("%01.2f", ($fila['cF']+$fila['cV']+$fila['uR'])*1.27+$fila['iM']+($fila['cB']*1.21)+$fila['iI']+$fila['o']);
	
    if($fila['empleado']==1){
        $totalAFacturar=sprintf("%01.2f",$total);
    }else{
        $totalAFacturar=sprintf("%01.2f",((($fila['q'] * (($fila['socio']==1)?$comSocio:$comNoSocio))*1.21)+$total));
    }
	// if(strlen($fila['nombre'])>
	//cantidadFacturas
	if(isset($fila['cantidadFacturas'])&&$fila['cantidadFacturas']>1){
		$multiple=true;
		$numeros = explode(',',$fila['numeroFactura']);
		$ids = explode(',',$fila['IDS']);
		$enlaceModificar = '';
		foreach($numeros as $key => $n){
			//$enlaceModificar .= "<a href='/ypf/cargaMovistar.php?id=$ids[$key]'>$n</a><br/>";
			$enlaceModificar .= "&nbsp;<a href='/ypf/cargaMovistar.php?id=$ids[$key]'>$n</a>";
		}
		//$enlaceModificar = substr($enlaceModificar,0,-5);
	} else {
		$multiple=false;
		$enlaceModificar = "<a href='/ypf/cargaMovistar.php?id=$fila[idFacturaRecibida]'>".substr($fila['numeroFactura'],-8)."</a>";
	}
	//$tabla .= "<tr id='g$fila[idCliente]'><td class='nombreCliente'>$fila[nombre] $socio </td><td><b>$fila[codigo]</b></td><td>$enlaceModificar</td><td>".sprintf("%01.2f", (($fila2['cF']+$fila2['cV']+$fila2['uR']+$fila2['iM'])*1.27))."</td><td>".sprintf("%01.2f", ($fila2['cB']*1.21))."</td><td>".sprintf("%01.2f", $fila2['iI'])."</td><td>".sprintf("%01.2f", $fila2['o'])."</td>$tdSocio<td class='no'>".sprintf("%01.2f", $fila2['IV7'])."</td><td class='no'>".sprintf("%01.2f", $fila2['IV1'])."</td><td class=''>$total</td><td>$totalAFacturar</td></tr>";
	$montoAbono=($fila['facturaB']==0)?"<td></td><td>".sprintf("%01.2f", (($fila['cF']+$fila['cV']+$fila['uR']+$fila['iM'])*$alicuotaIVA)).
	"</td>":"<td>".sprintf("%01.2f", (($fila['cF']+$fila['cV']+$fila['uR']+$fila['iM'])*$alicuotaIVA))."</td><td></td>";
	//print_r($fila);
	$tabla .= "<tr id='g$fila[idCliente]'><td class='nombreCliente'>$fila[nombre] $socio </td><td><b>$fila[codigo]</b></td><td class='no".(($multiple)?" multiplesFacturas":'')."'>$enlaceModificar</td>$montoAbono<td>".sprintf("%01.2f", ($fila['cB']*1.21))."</td><td>".sprintf("%01.2f", $fila['iI'])."</td><td>".sprintf("%01.2f", $fila['o'])."</td>$tdSocio<td class='no'>".sprintf("%01.2f", $fila['IV7'])."</td><td class='no'>".sprintf("%01.2f", $fila['IV1'])."</td><td class=''>$totalFacturasCompras</td><td>$totalAFacturar</td></tr>";
	// <td><input type='text' name='ingresosBrutos[]' class='input-sm' value='0' required='required' pattern='[0-9\.]{1,}' maxlength='5'/></td>
	if($fila['facturaB']==1)$totalB+=$totalAFacturar;
	else $totalA+=$totalAFacturar;
}
if($tabla=="")$tabla="<tr><td colspan='14' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
elseif(!isset($limit)) $tabla.="<tr><td colspan='14' class='label-info center'>Total Facturas A: $totalA</td></tr><tr><td colspan='14' class='label-info center'>Total: ".($totalA+$totalB)."</td></tr>";
$result->close();
echo $tabla;
?>