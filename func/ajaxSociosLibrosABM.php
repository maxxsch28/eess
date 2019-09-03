<?php
// ajaxSociosLibrosABM.php
// Recibe PDF de escaneo libro actas
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$args = array(
  'idFoja' => FILTER_SANITIZE_ENCODED,
  'libro'  => FILTER_SANITIZE_NUMBER_INT,
  'foja'   => FILTER_SANITIZE_NUMBER_INT,
  'actas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'altas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'bajas'  => FILTER_SANITIZE_MAGIC_QUOTES,
  'detalle'=> FILTER_SANITIZE_MAGIC_QUOTES
);

$post = filter_var_array($_POST, $args);

//ChromePhp::log($_FILES);

if(isset($_POST['idFoja'])){
  // Modificacion
  if(!is_numeric($_POST['idFoja'])){
    echo "me muero!";
    die;
  }
  
} else {
  // alta
  // verifico que con ese CUIT ni con ese número de Setup exista ya cargado un socio.
  $sql = "SELECT idFoja FROM dbo.[socios.libros] WHERE libro='$post[libro]' AND foja='$post[foja]';";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);

  if(sqlsrv_num_rows($stmt)>0){
    // ya existe esa foja cargada
    $idFoja = sqlsrv_fetch_array($stmt);
    $msg['status']  = "error";
    $msg['msg'] = "Ya existe esa foja cargada. <a href='?idFoja=$idFoja[0]'>¿Desea modificarla?</a>";
  } else {
    // graba en sql los datos de la foja
    $sql = "INSERT INTO dbo.[socios.libros] (libro, foja, actas, detalle, altas, bajas) VALUES ('$post[libro]', '$post[foja]', '$post[actas]', '$post[detalle]', '$post[altas]', '$post[bajas]');";
    //echo $sql;
    
    $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  
    $identificador = odbc_exec2( $mssql4, "SELECT @@IDENTITY AS ID", __LINE__, __FILE__);
    $idObtenido = sqlsrv_fetch_array($identificador);
    if(intval($idObtenido[0])>0){
      if (isset($_FILES['archivo'])) {
        if(move_uploaded_file($_FILES['archivo']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/pdf/$idObtenido[0].pdf")){
        //if(move_uploaded_file($_FILES['archivo']['tmp_name'], "../pdf/$idObtenido[0].pdf")){
          $msg['status']  = "success";
          $msg['msg'] = "Se cargó correctamente la foja $post[foja] del libro $post[libro]";
          $msg['idFoja'] = $idObtenido[0];
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
