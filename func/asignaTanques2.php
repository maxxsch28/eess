<?php
// asignaTanques2.php
// muestra modal para descarga de camión y a la vez procesa esa descarga.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);

if(isset($_GET['idOrden'])){
    // muestra modal
    // levanto datos de combustible incluido en la OP.
    $sql = "SELECT * FROM pedidos WHERE idOrden=$_GET[idOrden]";
    $result = $mysqli->query($sql);
    // [2068] => D-Euro [2069] => Ultra [2076] => Premium [2078] => Super )
    while($fila = $result->fetch_assoc()){
        
    }
} elseif(isset($_POST['fecha'])){
    // procesa camión
     if(is_numeric($_POST['remito1'])&&is_numeric($_POST['remito2'])&&(is_numeric($_POST['inputTq1'])||is_numeric($_POST['inputTq2'])||is_numeric($_POST['inputTq3'])||is_numeric($_POST['inputTq4'])||is_numeric($_POST['inputTq5'])||is_numeric($_POST['inputTq6']))){
         $fecha=explode('/', $_POST['fecha']);
         $fecha=$fecha[2].$fecha[1].$fecha[0];
         //substr($_POST['fecha'],-4).substr($_POST['fecha'],3,2).substr($_POST['fecha'],0,2);
        $sql1 = "INSERT INTO recepcioncombustibles (fecha, remito1, remito2, tq1, tq2, tq3, tq4, tq5, tq6, idOrden) VALUES ('$fecha', '$_POST[remito1]', '$_POST[remito2]', '$_POST[inputTq1]', '$_POST[inputTq2]', '$_POST[inputTq3]', '$_POST[inputTq4]', '$_POST[inputTq5]', '$_POST[inputTq6]', '$_POST[idOrden]')";
        $res1 = $mysqli->query($sql1);
        if($res1){
            $sql2 = "UPDATE ordenes SET ultimoEstado='Pedido Entregado', entregado=1, fechaEntregada='$fecha' WHERE idOrden='$_POST[idOrden]'";
            $res2 = $mysqli->query($sql2);
        }
        // si en la RV se incluyen litros reintegrados por YER se carga
        if(is_numeric($_POST['yerNS'])||is_numeric($_POST['yerUD'])||is_numeric($_POST['yerNP'])||is_numeric($_POST['yerED'])){
             $sql2 = "INSERT INTO yer (fecha, ed, ud, ns, np, idOrden) VALUES ('$fecha', '$_POST[yerED]', '$_POST[yerUD]', '$_POST[yerNS]', '$_POST[yerNP]', '$_POST[idOrden]')";
             $res2 = $mysqli->query($sql2);
        }
        // graba
        echo json_encode(array('status' => 'success','message'=> 'Camión registrado'));
    } else {
        // error
        echo json_encode(array('status' => 'error','message'=> 'Por favor complete los datos solicitados'));
    }
}
//echo $sql1;
//echo $sql2;

//print_r($articulo);
?>
