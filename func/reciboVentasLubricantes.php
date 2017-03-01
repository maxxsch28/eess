<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
foreach($_SESSION['recibo'] as $key => $value){
    if($value[1]>0)
    echo"<tr><td>$value[0]</td><td>$ $value[1]</td><td></td></tr>";
}
?>