<?php
// ajaxSociosLibrosABM.php
// Recibe PDF de escaneo libro actas
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$args = array(
  'idFoja' => FILTER_SANITIZE_ENCODED,
  'idLibro'  => FILTER_SANITIZE_NUMBER_INT,
  'foja'   => FILTER_SANITIZE_NUMBER_INT,
  'actas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'altas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'bajas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'detalle'=> FILTER_SANITIZE_MAGIC_QUOTES
);

$post = filter_var_array($_POST, $args);
//print_r($_POST);
//ChromePhp::log($post);
//print_r( $post);
//die;

if(isset($_POST['idFoja'])){
  // Modificacion
  if(!is_numeric($_POST['idFoja'])){
    echo "me muero!";
    die;
  }
  
} else {
  // alta
  if($_SESSION['idLibro']<>$post['idLibro']){
    $_SESSION['idLibro']=$post['idLibro'];
  }
  // idFoja idLibro numero
  $sql = "SELECT idFoja FROM dbo.[socios.fojas] WHERE idLibro='$post[idLibro]' AND foja='$post[foja]';";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);

  if(sqlsrv_num_rows($stmt)>0){
    // ya existe esa foja cargada
    $idFoja = sqlsrv_fetch_array($stmt);
    $msg['status']  = "error";
    $msg['msg'] = "Ya existe esa foja cargada. <a href='?idFoja=$idFoja[0]'>¿Desea modificarla?</a>";
  } else {
    // graba en sql los datos de la foja
    $sql = "INSERT INTO dbo.[socios.fojas] (idLibro, foja) VALUES ('$post[idLibro]', '$post[foja]');";
    //echo $sql;
    
    $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  
    $identificador = odbc_exec2( $mssql4, "SELECT @@IDENTITY AS ID", __LINE__, __FILE__);
    $idObtenido = sqlsrv_fetch_array($identificador);
    if(intval($idObtenido[0])>0){
      if (isset($_FILES['archivo'])) {
        $nombreArchivo = str_pad($idObtenido[0], 4, "0", STR_PAD_LEFT);
        if(move_uploaded_file($_FILES['archivo']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/pdf/$nombreArchivo.pdf")){

        //if(move_uploaded_file($_FILES['archivo']['tmp_name'], "../pdf/$idObtenido[0].pdf")){

          $msg['status']  = "success";
          $msg['msg'] = "Se cargó correctamente la foja $post[foja] del libro ".$_SESSION['libros'][$post['idLibro']]." grabada como $nombreArchivo.pdf, $idObtenido[0]";
          $msg['idFoja'] = $idObtenido[0];
          $msg['proximaFoja'] = $post['foja']+1;
        } else {

          $msg['status']  = "error";
          $msg['msg'] = "Fallo la carga del archivo PDF. No se pudo grabar.";
          if(!isset($_POST['idFoja'])){
            // fue un alta
            $sql = "DELETE FROM dbo.[socios.libros] WHERE idFoja=$idObtenido[0]";
            $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
          } else {
            // era una modificación. Dejo el registro en SQL pero no toco nada en el PDF que puede o no existir
          }
        }

      } else {

        $msg['status']  = "error";
        $msg['msg'] = "No se recibió ningún archivo. Por favor compruebeló.";
        $sql = "DELETE FROM dbo.[socios.libros] WHERE idFoja=$idObtenido[0]";
        $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
      }
      // Graba 
    } else {
      $msg['status']  = "error";
      $msg['msg'] = "Falló la registración en la base de datos. Intente enviar nuevamente apretando el botón \"Grabar\" o regrese mas tarde.";
    }
  }
}

echo json_encode($msg);
die;
?>
