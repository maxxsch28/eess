<?php
// ajaxCargaDetalleMovistar.php
// recibe archivo csv generado por Tabula desde Movistar y lo pasa a una tabla SQL
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
$mysqli4 = new mysqli($db_host, $db_user, $db_pass, $db_name4);


$h = fopen($_FILES['subeArchivo']['tmp_name'], 'r');
$a = 0;
while(($data = fgetcsv($h, 1000, ';')) !== FALSE) {
       //Array ( [0] => 2926410623 [1] => 660,00 [2] => 34,74 [3] => 202104 ) 


    $neto = str_replace('.', '', $data[1]);
    $imp = str_replace('.', '', $data[2]);
    $neto = str_replace(',', '.', $neto);
    $imp = str_replace(',', '.', $imp);

    $sqlFactura = "INSERT INTO `movistar`.`detalle_mensual_csv` (celular ,neto, imp_int, mes) values ('$data[0]', '".($neto+$imp)."', '".($neto*.0526)."', '$data[3]');";
    echo $sqlFactura.'<br>';
    $result = $mysqli4->query($sqlFactura);
    $idFactura = $mysqli4->insert_id;

    $a++;

}
fclose($h);
if($a>0)
echo "Se insertaron $a documentos de compra, la última $idFactura";


//echo $tabla;
?>