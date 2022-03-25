<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$mysqli4 = new mysqli($db_host, $db_user, $db_pass, $db_name4);
 // $array=array();
//$_POST['mes']='201411';
if(isset($_POST['mes'])){
   
    
	$sqlClientes = "SELECT codigo, cliente, sum(neto) as neto, ri*1.27*sum(neto) as '1', (1-ri)*1.21*sum(neto) as '598', sum(imp_int) as '2245', count(codigo) as 'comisiones', mes FROM `base_clientes` as c, `detalle_mensual_csv` as d WHERE c.celular=d.celular AND mes='$_POST[mes]' GROUP BY codigo, cliente, mes ORDER BY mes asc, ri, cliente ASC";
	echo $sqlClientes;
} 

 // echo $sqlClientes;

//id 	 idCliente 	 variosClientes 	idFacturaRecibida 	 idCliente 	numeroNC 	idItemFactura 	ingresosBrutos 	IVA10 	idFacturaRecibida 	 	
$result = $mysqli4->query($sqlClientes);
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$cantidadFacturas = $cantidadClientes = 0;
$comSocio = (isset($_POST['comSocio']))?$_POST['comSocio']:15;
$comNoSocio = (isset($_POST['comNoSocio']))?$_POST['comNoSocio']:20;
while($fila = $result->fetch_assoc()){
	$tabla .= "<tr id='g$fila[codigo]'><td class='nombreCliente'>$fila[cliente]</td><td><b>$fila[codigo]</b></td><td>".sprintf("%01.2f", ($fila['1']))."</td><td>".sprintf("%01.2f", $fila['598'])."</td><td>".sprintf("%01.2f", $fila['2245'])."</td><td>".$fila['comisiones']."</td><td>".$fila['neto']."</td></tr>";
    $total += $fila['neto'];
    $cantidadClientes++;
    $totalA += ($fila['1']>0)?1:0;
    $totalB += ($fila['598']>0)?1:0;
    $suma += $fila['1'] + $fila['598'];
    $neto += $fila['neto'];
    $imp += $fila['2245'];
}
if($tabla=="")$tabla="<tr><td colspan='7' class='label-info center'>NO HAY FACTURAS CARGADAS EN ESTE PERIODO</td></tr>";
elseif(!isset($limit)) $tabla.="<tr><td colspan='2' class='label-info center'>$cantidadClientes Clientes. Neto: \$$neto   Imp Int: \$$imp  Total \$$suma. </td><td colspan='5' class='label-info center'>Total Facturas A: $totalA</td></tr>";
$result->close();
echo $tabla;
?>