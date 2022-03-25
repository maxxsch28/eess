<?php
// ajaxSociosLibrosABM.php
// Recibe PDF de escaneo libro actas
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$args = array(
  'idLibro'  => FILTER_SANITIZE_NUMBER_INT,
  'acta'  => FILTER_SANITIZE_NUMBER_INT,
  'fojas'   => FILTER_SANITIZE_MAGIC_QUOTES,
  'fojaFirmas'  => FILTER_SANITIZE_NUMBER_INT,
  'actas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'altas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'altas_'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'bajas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'fecha'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'tipo'   => FILTER_SANITIZE_MAGIC_QUOTES,
  'detalle'=> FILTER_SANITIZE_MAGIC_QUOTES
);

$post = filter_var_array($_POST, $args);
//print_r($_POST);
//ChromePhp::log($_POST);
//print_r($post);
//die;

if(isset($_POST['idActa'])){
  // Modificacion
  if(!is_numeric($_POST['idActa'])){
    echo "me muero!";
    die;
  }
  // Modifico (?)
  
} else {

  // [idLibro] => 2 [acta] => 452 [fojas] => 2 [fojaFirmas] => 59 [actas] => [altas] => [bajas] => [fecha] => 2002-10-04 [detalle] => 1) Se decide hablar con transportes Pisano y Wunder, tema a tratar tarifa nueva. Según ellos debería ser 100% mas. 2) Se insiste conque socios presenten fotocopia de recibo por seguro de terceros, caso contrario no pueden tomar viajes. 3) Se consultará al Dador de Cargas por una orden tomada por socio Urquiza que se cree fue devuelta. 4) Por renuncia del Sr. Carlos Sánchez, siendo él tesorero, se decide que el socio Angel Migliaro ocupe ese puesto. 

  // nuevo acta
  //[idActa]  ,[idFojas]  ,[idFolio]  ,[numero]  ,[tipo]  ,[fecha]  ,[altas]  ,[bajas]  ,[detalle]  ,[tieneAltas]  ,[tieneBajas]  ,[esConvocatoria]  ,[esAsamblea]  ,[esDistribucionCargos] FROM [coop].[dbo].[socios.actas2]

  $sql = "SELECT numero, idFojas FROM dbo.[socios.actas2] WHERE numero='$post[acta]'";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);

  if(sqlsrv_num_rows($stmt)>0){
    // ya existe esa foja cargada
    $idFoja = sqlsrv_fetch_array($stmt);
    $msg['status']  = "error";
    $msg['msg'] = "Ya existe ese acta cargada. <a href='?idActa=$idFoja[0]'>¿Desea modificarla?</a>";
  } else {
    // graba en sql los datos de la foja
    if(is_array($_POST['altas_'])){
      $tieneAltas = '1';
      $altas = implode(",", $_POST['altas_']);
    } else {
      $tieneAltas = 0;
    }
    
    $tieneBajas = ($post['bajas']<>'')?'1':'';
    $esConvocatoria = ($_POST['tipo']=='convocatoria')?'1':'';
    $esAsamblea = ($post['tipo']=='asamblea')?'1':'';
    $esDistribucionCargos = ($post['tipo']=='distribucion')?'1':'';

    $sql = "INSERT INTO dbo.[socios.actas2] (idFojas, idFolio, numero, fecha, altas, bajas, detalle, tieneAltas, tieneBajas, esConvocatoria, esAsamblea, esDistribucionCargos) VALUES ('$post[fojas]', '$post[fojaFirmas]', '$post[acta]', '$post[fecha]', '$altas', '$post[bajas]', '$post[detalle]', '$tieneAltas', '$tieneBajas', '$esConvocatoria', '$esAsamblea', '$esDistribucionCargos');";
    ChromePhp::log($sql); 
    
    $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  
    $identificador = odbc_exec2( $mssql4, "SELECT @@IDENTITY AS ID", __LINE__, __FILE__);
    $idObtenido = sqlsrv_fetch_array($identificador);

    if(is_numeric($idObtenido[0])){
      $msg['status']  = "success";
      $msg['msg'] = "Se cargó correctamente el acta $post[acta] del libro $post[libro]";
      $msg['idActa'] = $idObtenido[0];
    } else {
      $msg['status']  = "error";
      $msg['msg'] = "Fallo la carga del acta. No se pudo grabar. -$idObtenido[0]-";
    }
  }
}

echo json_encode($msg);
die;
?>
