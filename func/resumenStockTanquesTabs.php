<?php
// resumenStockTanquesTabs.php
// arma 4 boxes y 3 graficos de torta como excel YPF

include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
if(isset($_POST['mes'])){
    $mes=substr($_POST['mes'],4,2);
    $anio=substr($_POST['mes'],0,4);
}

$sqlAforadores = "SELECT aforadores.fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ed2, ud3, ed4, ud5, ud6, ed7, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, recepcion.tq1, recepcion.tq2, recepcion.tq3, recepcion.tq4, recepcion.tq5, recepcion.tq6 FROM cierres_cem_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = DATE( aforadores.fechaCierre ) WHERE (YEAR(aforadores.fechaCierre) = $anio AND MONTH(aforadores.fechaCierre)= $mes) OR (DATE(aforadores.fechaCierre) = LAST_DAY('$anio-$mes-01' - INTERVAL 1 MONTH)) ORDER BY aforadores.fechaCierre ASC";

$sqlAforadores = "SELECT distinct aforadores.fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ed2, ud3, ed4, ud5, ud6, ed7, turno, tanques.tq1, tanques.tq2, tanques.tq3, tanques.tq4, tanques.tq5, tanques.tq6, sum(recepcion.tq1) as r1, sum(recepcion.tq2) as r2, sum(recepcion.tq3) as r3, sum(recepcion.tq4) as r4, sum(recepcion.tq5) as r5, sum(recepcion.tq6) as r6 FROM cierres_cem_aforadores aforadores INNER JOIN cierres_cem_tanques tanques ON aforadores.fechaCierre = tanques.fechaCierre LEFT JOIN recepcioncombustibles recepcion ON recepcion.fecha = DATE( aforadores.fechaCierre ) WHERE (YEAR(aforadores.fechaCierre) = $anio AND MONTH(aforadores.fechaCierre)= $mes) OR (DATE(aforadores.fechaCierre) = LAST_DAY('$anio-$mes-01' - INTERVAL 1 MONTH)) GROUP BY aforadores.fechaCierre ORDER BY aforadores.fechaCierre ASC";

//echo $sqlAforadores;

$result = $mysqli->query($sqlAforadores);


$tabla = "";$a=0;$q=0;
while($fila = $result->fetch_assoc()){
    if(!isset($anterior)){
        $anterior = $stockInicial = $fila; // define stock inicial de tanques al último día del mes anterior.
        asort($arrayPicosTanques);
        $tableTanques = array();
        foreach($arrayPicosTanques as $pico => $tanque){
            if(!isset($tableTanques[$tanque])){
                // defino tabla para este tanque
                @$i++;
                $tableTanques[$tanque]="<div role='tabpanel' class='tab-pane' id='tq$tanque'><table class='table table-hover table-condensed'><thead><tr><th class='nombre'>Fecha</th><th align=center>Ingresos</th><th>Ventas</th><th>Teórico</th><th>Medido</th><th colspan=2>&delta;</th></tr></thead><tbody><tr><td>Inicial</td><td></td><td></td><td></td><td>{$fila['tq'.$tanque]}</td><td></td></tr>";
            }
            @$stockInicialTanque[$tanque] += $fila['tq'.$tanque];
            @$stockInicialProducto[$tanques[$tanque]] += $fila['tq'.$tanque];
        }
        $tableProducto = array();
        $tabla .= "<td></td>";
        ksort($stockInicialProducto);
        foreach($stockInicialProducto as $idProducto => $despachado){
            if(!isset($tableProducto[$idProducto])){
                // defino tabla para este tanque
                $tableProducto[$idProducto]="<div role='tabpanel' class='tab-pane' id='tq$tanque'><table class='table table-hover alert alert-{$classArticulo[$tanques[$tanque]]}'><thead><tr><th class='nombre'>Fecha</th><th align=center>Ingresos</th><th>Ventas</th><th>Teórico</th><th>Medido</th><th colspan='2'>&delta;</th></tr></thead><tbody><tr><td></td><td></td><td></td><td></td><td>{$fila['tq'.$tanque]}</td><td></td><td></td></tr>";
            }
            $tabla.="<td></td><td>".sprintf("%.2f", $despachado)."</td>";
        }
        $tabla .= "</tr>";
    } else {
        if($fila['fechaCierre']==$anterior['fechaCierre']){
            // si el dia no cambió es porque ese día hubo mas de una descarga de combustible. Tengo que sumarlas y mostrarlas pero no duplicar renglones.
            
        }
        // tabla con venta por tanque mas columnas de venta por producto
        $tabla .= "<tr><td><b>".substr($fila['fechaCierre'],0,-3)."</b></td>";
        asort($arrayPicosTanques);
        $totalDespachoProductoPorAforadores=array();
        if(!isset($sumaDiferencias))$sumaDiferencias=array(0,0,0,0,0,0,0);
        if(!isset($totalRecibidoTanque))$totalRecibidoTanque=array(0,0,0,0,0,0,0);
        
        foreach($arrayPicosTanques as $pico => $tanque){
            @$combustibleDespachadoTanque[$tanque] += $fila[$pico]-$anterior[$pico];
            @$totalDespachoTanque[$tanque] += $fila[$pico]-$anterior[$pico];
            @$combustibleDespachadoProducto[$tanques[$tanque]] += $fila[$pico]-$anterior[$pico];
            @$totalDespachoProducto[$tanques[$tanque]] += $fila[$pico]-$anterior[$pico];
            @$totalDespachoProductoPorAforadores[$tanques[$tanque]] += $fila[$pico]-$stockInicial[$pico];
            $totalRecibidoTanque[$tanque] += $fila['r'.$tanque];
            @$totalRecibidoPorAforadores[$tanques[$tanque]] += $fila['r'.$tanque];
        }
        foreach($combustibleDespachadoTanque as $tanque => $despachado){
            $tableTanques[$tanque].="<tr><td><b>".substr($fila['fechaCierre'],0,-3)."</b></td>";
            $teorico = intval($anterior['tq'.$tanque] + $fila['r'.$tanque] - $despachado);
            $diferenciaMedidoTeorico = $fila['tq'.$tanque]-$teorico;
            $sumaDiferencias[$tanque] = $sumaDiferencias[$tanque] + $diferenciaMedidoTeorico;
            $porcentajeDiferenciaSobreVentas = ($despachado<>0)?round(100 * $diferenciaMedidoTeorico / $despachado, 2):0;
            
            $tableTanques[$tanque] .= "<td>".(($fila['r'.$tanque]<>(NULL||0))?$fila['r'.$tanque]:'')."</td><td>".sprintf("%.2f", $despachado)."</td><td>$teorico</td><td>{$fila['tq'.$tanque]}</td><td>$diferenciaMedidoTeorico lts</td><td>$porcentajeDiferenciaSobreVentas%</td></tr>";
        }

        ksort($combustibleDespachadoProducto);
        foreach($combustibleDespachadoProducto as $idProducto => $despachado){
            $tabla.="<td></td><td>".sprintf("%.2f", $despachado)."</td>";
        }
        $tabla .= "</tr>";
        $anterior=$fila;
        $stockFinal = $fila;
        $combustibleDespachadoTanque=array();
        $combustibleDespachadoProducto=array();
    }
}
//$tableTanques[$tanque] .= "<tr><td></td>";

foreach($totalDespachoTanque as $tanque => $despachado){
    $tableTanques[$tanque] .= "<tr><td>Final</td><td>".sprintf("%.2f", $totalRecibidoTanque[$tanque])."</td><td>".sprintf("%.2f", $despachado)."</td><td></td><td></td><td>$sumaDiferencias[$tanque] lts</td><td>".sprintf("%.2f", $sumaDiferencias[$tanque]/$despachado*100)."%</td></tr>";
}
$tabla.="<td></td>";
ksort($totalDespachoProducto);
foreach($totalDespachoProducto as $idProducto => $despachado){
    $tabla.="<td colspan='2'><p class='h6'>".sprintf("%.2f", $totalDespachoProductoPorAforadores[$idProducto])."</p></td>";
}
$tabla.="</tr><tr><td colspan=14></td><td colspan=4><b>".sprintf("%.2f", ($totalDespachoProducto[2068]/($totalDespachoProducto[2068]+$totalDespachoProducto[2069]))*100)."% Euro/Gasoil</b></td><td colspan=4><b>".sprintf("%.2f", ($totalDespachoProducto[2076]/($totalDespachoProducto[2076]+$totalDespachoProducto[2078]))*100)."% Infinia/Naftas</b></td>";
//echo $tabla."</tr>";
foreach($arrayPicosTanques as $pico => $tanque){
    echo $tableTanques[$tanque]."</tbody></table></div>";
}
?>