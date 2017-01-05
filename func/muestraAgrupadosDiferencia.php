<?php
// calculaPromedios.php
include_once('../include/inicia.php');


//print_r($_SESSION['litros']);
echo "<table style='width:100%'>";
foreach($_SESSION['litrosMes'] as $mes => $litros){
    echo "<tr align='right'><td>$mes</td><td>".sprintf("%01.2f",$litros)." lts</td><td></td>";
}
echo "<br>";
foreach($_SESSION['litrosPrecio'] as $producto => $precio){
    echo "<tr ><td colspan=3>$articulo[$producto]</td></tr>";
    foreach($precio as $key => $value){
        echo "<tr align='right'><td>$key</td><td>".sprintf("%01.2f",$value)." lts</td><td><b>$".sprintf("%01.2f",$value*$key)."</b></td></tr>";
    }

}
echo "</table>";
?>