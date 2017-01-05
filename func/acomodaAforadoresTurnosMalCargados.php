<?php
/*   
Acomoda aforadores cuando se mandan una cagada y se arrastra varios días.
   */
$picos = array(1 => 'ed1', 2 => 'ns1', 3 => 'ni1', 4 => 'ed2', 5 => 'ns2', 6 => 'ni2', 7 => 'ud3', 8 => 'ed4', 9 => 'ud5', 10 => 'ud6', 11 => 'ed7');
// defino desvios
$desvio['ed1'] = 0;
$desvio['ns1'] = 0;
$desvio['ni1'] = 0;
$desvio['ed2'] = 0;
$desvio['ns2'] = 0;
$desvio['ni2'] = 0;
$desvio['ud3'] = 0;
$desvio['ed4'] = 0;
$desvio['ud5'] = 0;
$desvio['ud6'] = 0;
$desvio['ed7'] = 0;
foreach ($picos as $idManguera => $producto){
  $sql = "update dbo.CierresDetalleSurtidores set AforadorElectronico=AforadorElectronico+$desvio[$producto], AforadorMecanico=AforadorMecanico+$desvio[$producto] where IdManguera=$idManguera and IdCierreSurtidores>$IdCierreSurtidores;";
}

?>
  
