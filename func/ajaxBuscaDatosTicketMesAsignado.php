<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//fb($_POST);
//fechaTicket: data.fecha, fechaCanje: $(this.val())
$d1 = explode('/',$_POST['fechaTicket']);
$d2 = explode('/',$_POST['fechaCanje']);
$datetime1 = date_create("$d1[2]-$d1[1]-$d1[0]");
$datetime2 = date_create("$d2[2]-$d2[1]-$d2[0]");
$interval = date_diff($datetime1, $datetime2);
//fb($interval->days);
if($interval->days<=15){
  // mes de ticket
  $mesAsignado="$d1[2]$d1[1]";
} else {
  $mesAsignado="$d2[2]$d2[1]";
}

//array('fechaTicket'=>'28/10', 'fechaCanje'=>'12/11/2016')

echo json_encode(array('status' => 'error','message'=> $interval->days." dias entre $d1[0]-$d1[1] y $d2[0]-$d2[1].", 'mesAsignado'=> $mesAsignado));

?>
