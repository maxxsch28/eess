<?php
// asignaTanques2.php
// muestra modal para descarga de camión y a la vez procesa esa descarga.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
ChromePhp::log($_POST);
//die;
/*
array(
['fecha'] => '17/01/2017'
['remito1'] => 6
['remito2'] => 1000857
['op'] => 71868068
['totalUD'] => 20999
['inputTq2'] => 6919
['inputTq3'] => 10999
['inputTq5'] => 5000
['totalEd'] => 
['inputTq1'] =>
// */
if(isset($_POST['tipo'])&&$_POST['tipo']=='yer'){
  // grabo YER, se hace antes de tener el remito
  if(is_numeric($_POST['rv'])&&(is_numeric($_POST['yerUD'])||is_numeric($_POST['yerID'])||is_numeric($_POST['yerNS'])||is_numeric($_POST['yerNI']))){
    $fecha1=explode('/', $_POST['fecha']);
    $fecha=$fecha1[2].'-'.$fecha1[1].'-'.$fecha1[0];
    $yerNS = (is_numeric($_POST['yerNS']))?$_POST['yerNS']:0;
    $yerUD = (is_numeric($_POST['yerUD']))?$_POST['yerUD']:0;
    $yerID = (is_numeric($_POST['yerID']))?$_POST['yerID']:0;
    $yerNI = (is_numeric($_POST['yerNI']))?$_POST['yerNI']:0;
    $insertYER = "INSERT INTO yer (fecha, despachos, ns, ud, ed, np, rv) VALUES ('$fecha', 0, '$yerNS', '$yerUD', '$yerID', '$yerNI', $_POST[rv]);";
    ChromePhp::log($insertYER);
    $nuevaOrden = $mysqli->query($insertYER);

    if($nuevaOrden){
      if ($mysqli->errno == 1062) {
        echo json_encode(array('tipo'=>'yer', 'status' => 'error','message'=> 'Camión ya registrado previamente'));
      } else {
        echo json_encode(array('tipo'=>'yer', 'status' => 'success','message'=> 'Camión registrado'));
      }
    } else {
    if ($mysqli->errno == 1062) {
        echo json_encode(array('tipo'=>'yer', 'status' => 'error','message'=> 'Camión ya registrado previamente'));
      } else {
        echo json_encode(array('tipo'=>'yer', 'status' => 'error','message'=> 'Por favor complete los datos solicitados'));
      }
      
    }
    /*array(
    ['tipo'] =>    'yer'
    ['fecha'] =>    '19/01/2017'
    ['remito1'] =>    6
    ['remito2'] =>    1001182
    ['yerUD'] =>    87.48
    ['yerNS'] =>
    ['yerNI'] =>
    ['yerID'] =>
    )*/
  }
} else {
  if(isset($_POST['fecha'])){
      // procesa camión
      if(is_numeric($_POST['remito1'])&&is_numeric($_POST['remito2'])&&is_numeric($_POST['op'])&&(is_numeric($_POST['inputTq1'])||is_numeric($_POST['inputTq2'])||is_numeric($_POST['inputTq3'])||is_numeric($_POST['inputTq4'])||is_numeric($_POST['inputTq5'])||is_numeric($_POST['inputTq6']))){
          $fecha1=explode('/', $_POST['fecha']);
          $fecha=$fecha1[2].$fecha1[1].$fecha1[0];
          // inserta ordenes
          $sqlOrden = "INSERT INTO ordenes (op, fechaDespachoEstimada, fechaEntregada, ultimoEstado, entregado) values ('$_POST[op]', '$fecha1[2]-$fecha1[1]-$fecha1[0]', '$fecha1[2]-$fecha1[1]-$fecha1[0]', 'Pedido Entregado', 1)";
          ChromePhp::log($sqlOrden);
          $nuevaOrden = $mysqli->query($sqlOrden);
          $idOrden = $mysqli->insert_id;
          $inputTq4 = $_POST['totalEd']-$_POST['inputTq1'];
          $inputTq5 = $_POST['totalNS']-$_POST['inputTq3'];
          //substr($_POST['fecha'],-4).substr($_POST['fecha'],3,2).substr($_POST['fecha'],0,2);
          $sql1 = "INSERT INTO recepcioncombustibles (fecha, remito1, remito2, tq1, tq2, tq3, tq4, tq5, tq6, idOrden) VALUES ('$fecha', '$_POST[remito1]', '$_POST[remito2]', '0$_POST[inputTq1]', '0$_POST[inputTq2]', '0$_POST[inputTq3]', '$inputTq4', '0$inputTq5', '0$_POST[inputTq6]', '$idOrden')";
          ChromePhp::log($sql1);
          $res1 = $mysqli->query($sql1);
          
          // si en la RV se incluyen litros reintegrados por YER se carga
          /*
          if(is_numeric($_POST['yerNS'])||is_numeric($_POST['yerUD'])||is_numeric($_POST['yerNP'])||is_numeric($_POST['yerED'])){
              $sql2 = "INSERT INTO yer (fecha, ed, ud, ns, np, idOrden) VALUES ('$fecha', '$_POST[yerED]', '$_POST[yerUD]', '$_POST[yerNS]', '$_POST[yerNP]', '$_POST[idOrden]')";
              $res2 = $mysqli->query($sql2);
          }*/
          // graba
          echo json_encode(array('tipo'=>'remito', 'status' => 'success','message'=> 'Camión registrado'));
      } else {
          // error
          echo json_encode(array('tipo'=>'remito', 'status' => 'error','message'=> 'Por favor complete los datos solicitados'));
      }
  }
}
//echo $sql1;
//echo $sql2;

//print_r($articulo);
?>
