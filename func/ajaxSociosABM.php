<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);echo "<br><br>";
$args = array(
  'idSocio' => FILTER_SANITIZE_ENCODED,
  'nombre' => FILTER_SANITIZE_MAGIC_QUOTES,
  'razonsocial' => FILTER_SANITIZE_MAGIC_QUOTES,
  'idFletero' => FILTER_SANITIZE_NUMBER_INT,
  'domicilio' => FILTER_SANITIZE_MAGIC_QUOTES,
  'domicilio2' => FILTER_SANITIZE_MAGIC_QUOTES,
  'email' => FILTER_SANITIZE_EMAIL,
  'fechaIngreso' => FILTER_SANITIZE_SPECIAL_CHARS,
  'fechaEgreso' => FILTER_SANITIZE_SPECIAL_CHARS,
  'cuit' => FILTER_SANITIZE_NUMBER_INT,
  'iva' => FILTER_SANITIZE_STRING,
  'celular' => FILTER_SANITIZE_SPECIAL_CHARS,
  'libro' => FILTER_SANITIZE_NUMBER_INT,
  'foja' => FILTER_SANITIZE_NUMBER_INT,
  'acta' => FILTER_SANITIZE_NUMBER_INT
);

$post = filter_var_array($_POST, $args);

if(isset($_POST['idSocio'])){
  // Modificacion
  if(!is_numeric($_POST['idSocio'])){
    echo "me muero!";
    die;
  }
  
} else {
  // alta
  // verifico que con ese CUIT ni con ese número de Setup exista ya cargado un socio.
  $sql = "SELECT * FROM dbo.[socios.socios] WHERE idFletero='$post[idFletero]' OR cuit='$post[cuit]';";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);

  if(sqlsrv_num_rows($stmt)>0){
    // ya existen socios con el número de fletero o el mismo CUIT
    $msg['status']  = "error";
    $msg['msg'] = 'Ya existen socios cargados con esos datos';
    
  } else {
    // graba en sql los datos del nuevo datosSocio
    $fechaIngreso = fecha($post['fechaIngreso'], 'sql', 'dmy');
    $sql = "INSERT INTO dbo.[socios.socios] (razonsocial, nombre, idFletero, domicilio, domicilio2, fechaIngreso, activo, celular, email, iva, cuit) VALUES ('$post[razonsocial]', '$post[nombre]', '$post[idFletero]', '$post[domicilio]', '$post[domicilio2]', '$fechaIngreso', '1', '$post[celular]', '$post[email]', '$post[iva]', '$post[cuit]');";
    //echo $sql;
    
    $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
    
    $identificador = odbc_exec2( $mssql4, "SELECT @@IDENTITY AS ID", __LINE__, __FILE__);
    $idFletero = sqlsrv_fetch_array($identificador);
    
    // graba en evento la creación del nuevo socio
    if(eventoSocio($idFletero[0], 'alta', $fechaIngreso, 'Alta nuevo socio', $post['libro'], $post['foja'], $post['acta'])){
      // retorno todo OK
      $msg['status']  = "success";
      $msg['msg'] = "Se cargó correctamente al socio $post[nombre]";
      $msg['idSocio'] = $idFletero[0];
    } else {
      // retorno socio grabado pero no logueado.
      
    }
  }
}

echo json_encode($msg);
die;

function eventoSocio ($idFletero, $evento, $fecha, $detalle='', $idLibro=0, $folio=0, $acta=0){
  // TODO: sacarla a inicia.php
  global $mssql4; 
  if(!isset($_SESSION['eventosTipos'])||1){
    $sql = "SELECT idEvento, evento FROM dbo.[socios.eventosTipos] ORDER BY evento";
    $stmt = odbc_exec2($mssql4, $sql, __LINE__, __FILE__);
    while ($row = sqlsrv_fetch_array($stmt)){
      $_SESSION['eventosTipos'][$row['idEvento']] = trim($row['evento']);
    }
  }
  $idEvento = array_search ($evento, $_SESSION['eventosTipos']);
  $sql = "INSERT INTO dbo.[socios.movimientos] (idSocio, idLibro, folio, acta, evento, idEvento, fecha, detalle) values ('$idFletero', '$idLibro', '$folio', '$acta', '$evento', '$idEvento', '$fecha', '$detalle')";
  $stmt = odbc_exec2( $mssql4, $sql, __LINE__, __FILE__);
  if($stmt){
    return true;
  }
}

?>
