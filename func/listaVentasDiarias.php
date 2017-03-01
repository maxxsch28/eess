<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';


// levanto la información de los cierresCEM de los últimos 30 días
$sqlCierresCem = "SELECT * FROM cierres_cem_aforadores WHERE fechaCierre >=  (NOW() - INTERVAL 30 DAY);";

$result = $mysqli->query($sqlCierresCem);
echo "<table class='table'><tr>";
while($fila = $result->fetch_assoc()){
	//$sqlClientes2 = "SELECT SUM(cargoFijo) AS cF, SUM(cargoBB) AS cB, SUM(cargoVariable) AS cV, SUM(usoRed) AS uR, SUM(interesMora) AS iM, SUM(ingresosBrutos) AS iB, SUM(IVA21) AS IV1, SUM(IVA27) AS IV7, SUM(impuestosInternos) AS iI, SUM(otros) AS o FROM `movistar.facturasitems` AS items WHERE items.idFacturaRecibida=$fila[idFacturaRecibida]";
    if(!isset($primero)){
        $anterior = $fila;
        $primero = true;
    } else {
        echo "<tr><td>$fila[fechaCierre]</td><td>".sprintf("%.2f", $fila['ns1']-$anterior['ns1'])."</td><td>".sprintf("%.2f", $fila['ns2']-$anterior['ns2'])."</td><td>".sprintf("%.2f", $fila['ni1']-$anterior['ni1'])."</td><td>"
                .sprintf("%.2f", $fila['ni2']-$anterior['ni2'])."</td><td>".sprintf("%.2f", $fila['ed1']-$anterior['ed1'])."</td><td>"
                .sprintf("%.2f", $fila['ed2']-$anterior['ed2'])."</td><td>".sprintf("%.2f", $fila['ed4']-$anterior['ed4'])."</td><td>".sprintf("%.2f", $fila['ed7']-$anterior['ed7'])."</td><td>"
                .sprintf("%.2f", $fila['ud3']-$anterior['ud3'])."</td><td>".sprintf("%.2f", $fila['ud5']-$anterior['ud5'])."</td><td>".sprintf("%.2f", $fila['ud6']-$anterior['ud6'])."</td></tr>";
        $anterior = $fila;
    }
    
	
}
echo "</table>";

//echo $tabla;
?>