<?php
// ajaxCargaMisComprobantes.php
// recibe archivo csv generado por AFIP y lo pasa a una tabla SQL
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


$h = fopen($_FILES['subeArchivo']['tmp_name'], 'r');
$a = 0;
while(($data = fgetcsv($h, 1000, ',')) !== FALSE) {
    if($a>0){
        $fecha = explode('/', $data[0]);
        $data[11] = ($data[11]>0)?$data[11]:0;
        $data[12] = ($data[12]>0)?$data[12]:0;
        $data[13] = ($data[13]>0)?$data[13]:0;
        $data[14] = ($data[14]>0)?$data[14]:0;
        $data[15] = ($data[15]>0)?$data[15]:0;
        

        $sql = "INSERT INTO [coop].[dbo].[misComprobantes] ([fecha] ,[tipo], [pv],[numDesde],[numHasta] ,[tipoDocEmisor] ,[cuit] ,[razonSocial], [netoGravado] ,[netoNoGravado] ,[opExentas] ,[iva] ,[total]) values ('$fecha[2]-$fecha[1]-$fecha[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[6]', '$data[7]', '$data[8]',  $data[11], $data[12], $data[13], $data[14], $data[15])";
        $stmt = odbc_exec2( $mssql4, $sql, __FILE__, __LINE__);
    }
    $a++;

}
fclose($h);
if($a>0)
echo "Se insertaron $a documentos de compra";


//echo $tabla;
?>