<?php 
// inicia.php

$dbg=1;
$controlTiempo=microtime();
//Database Information
$dbtype = "mysqli"; 
$db_host = "localhost";
$db_user = "coopetrans";
$db_pass = "vGCP6eZ6dqUFZ2pB";
$db_pass2= "vGCP6eZ6dqUFZ2pB";
$db_pass3= "vGCP6eZ6dqUFZ2pB";
//$db_user = "root";
//$db_pass = "e757g4a";
//$db_pass2= "e757g4a";
//$db_pass3= "e757g4a";
$db_name = "pedidosypf";
$db_name2 = "transporte";
$db_name3 = 'cuentaypf';
$db_port = "3306";
$db_table_prefix = "users_";

$tercerEmpleado = 24; // federico
$CFG = new stdClass(); 
$CFG->tomaLitrosDesdeTabla = false;
$CFG->tanquesATomarMilimetrosDesdeTablas = array(7); // 7 es para que ningún tanque de true.
$CFG->fechaDesdeDondeTomoPromedioHistoricos = "2015-01-01";
$CFG->tipoFechaSQL = "Y-d-m";

//Dbal Support - Thanks phpBB ; )
require_once($_SERVER['DOCUMENT_ROOT']."/classes/mysqli.php");

//Construct a db instance
$db = new $sql_db();
if(is_array($db->sql_connect($db_host, $db_user,$db_pass,$db_name, $db_port, false, false))){
  die("Unable to connect to the database");
}
	
require_once($_SERVER['DOCUMENT_ROOT']."/include/es.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/class.user.php");
require_once($_SERVER['DOCUMENT_ROOT']."/func/funcs.user.php");
require_once($_SERVER['DOCUMENT_ROOT']."/func/funcs.general.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/class.newuser.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/func/FirePHPCore/fb.php'); //firebug

session_start();
ob_start(); //firebug
$remember_me_length = "1wk";
//Global User Object Var
//loggedInUser can be used globally if constructed
if(isset($_SESSION["userPieUser"]) && is_object($_SESSION["userPieUser"]))
    $loggedInUser = $_SESSION["userPieUser"];
else if(isset($_COOKIE["userPieUser"])) {
    $db->sql_query("SELECT session_data FROM users_sessions WHERE session_id = '".$_COOKIE['userPieUser']."'");
    $dbRes = $db->sql_fetchrowset();
    if(empty($dbRes)) {
        $loggedInUser = NULL;
        setcookie("userPieUser", "", -parseLength($remember_me_length));
    } else {
        $obj = $dbRes[0];
        $loggedInUser = unserialize($obj["session_data"]);
    }
} else {
    if(isset($remember_me_length))
    $db->sql_query("DELETE FROM users_sessions WHERE ".time()." >= (session_start+".parseLength($remember_me_length).")");
    $loggedInUser = NULL;
}



/* Base Mysql */

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
$mysqli2 = new mysqli($db_host, $db_user, $db_pass, $db_name2);
$mysqli3 = new mysqli($db_host, $db_user, $db_pass, $db_name3);

if ($mysqli->connect_error) {
    die('MySQL - Error de Conexión ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
}

if ($mysqli2->connect_error) {
    die('MySQL - Error de Conexión ('.$mysqli2->connect_errno.') '.$mysqli2->connect_error);
}
if ($mysqli3->connect_error) {
    die('MySQL - Error de Conexión ('.$mysqli3->connect_errno.') '.$mysqli3->connect_error);
}


ini_set('mssql.charset', 'UTF-8');
/* Specify the server and connection string attributes. */
$serverName = "sqlserver";
$uid = 'sa';
$pwd = 'B8000ftq';
$database1 = "calden";
$database2 = "sqlcoop_dbimplemen";
$database3 = "sqlcoop_dbshared";

/* Connect using SQL Server Authentication. */
//$mssql = sqlsrv_connect($serverName, $connectionInfo);
/*
$mssql = odbc_connect("calden",$uid, $pwd);
if( $mssql === false ){
     echo "MSSQL - No pudo conectarse:</br>";
     die( print_r( odbc_error().' - '.odbc_errormsg(), true));
}
$mssql2 = odbc_pconnect("dbimplemen",$uid, $pwd);
if( $mssql2 === false ){
     echo "MSSQL - No pudo conectarse:</br></br></br>";
     die( print_r( odbc_error().' - '.odbc_errormsg(), true));
}
$mssql3 = odbc_pconnect("dbshared",$uid, $pwd);
if( $mssql3 === false ){
     echo "MSSQL - No pudo conectarse:</br></br></br>";
     die( print_r( odbc_error().' - '.odbc_errormsg(), true));
}
$mssql4 = odbc_pconnect("dbimplemen2",$uid, $pwd);
if( $mssql3 === false ){
     echo "MSSQL - No pudo conectarse:</br></br></br>";
     die( print_r( odbc_error().' - '.odbc_errormsg(), true));
}*/

/* cambio de odbc por nuevo driver MS */
$serverName = "192.168.1.35";
$connectionOptions = array(
    "Database" => "CoopDeTrabajo.Net",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions2 = array(
    "Database" => "sqlcoop_dbimplemen",
    "Uid" => "coop",
    "PWD" => "MarcosPaz3876"
);
$connectionOptions3 = array(
    "Database" => "sqlcoop_dbshared",
    "Uid" => "coop",
    "PWD" => "MarcosPaz3876"
);
//Establishes the connection
$mssql = sqlsrv_connect($serverName, $connectionOptions);
if( $mssql === false ){
  echo "MSSQL - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$mssql2 = sqlsrv_connect($serverName, $connectionOptions2);
if( $mssql2 === false ){
  echo "MSSQL2 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$mssql3 = sqlsrv_connect($serverName, $connectionOptions3);
if( $mssql3 === false ){
  echo "MSSQL3 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$date = array("Sunday"=>"Domingo", "Monday"=>"Lunes", "Tuesday"=>"Martes", "Wednesday"=>"Miércoles","Thursday"=>"Jueves", "Friday"=>"Viernes", "Saturday"=>"Sábado");
$date2 = array(1=>"Domingo",2=>"Lunes",3=>"Martes",4=>"Miércoles",5=>"Jueves",6=>"Viernes",7=>"Sábado");
$mes = array(1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre");
// codigo Calden para los combustibles
$articulo = array(2068=>"Infinia D.",2069=>"Ultra",2076=>"Infinia",2078=>"Super");
$classArticulo = array(2068=>"success",2069=>"warning",2076=>"info",2078=>"info2");

$tanques = array(1=>2068, 2=>2069, 3=>2078, 4=>2068, 5=>2076, 6=>2069);

$toleranciaTanques = 50; // cuantos litros mas menos están tolerados para el control de stock.

// unificar nombres picos TODO
// para cierreCem
$arrayPicos = array('ed1'=>'1a - Infinia D.', 'ns1'=>'1b - Super', 'ni1'=>'1c -Infinia', 'ed2'=>'2a - Infinia D.', 'ns2'=>'2b - Super', 'ni2'=>'2c -Infinia','ud3'=>'3 - Ultra', 'ed4'=>'4 - Infinia D.', 'ud5'=>'5 - Ultra', 'ud6'=>'6 - Ultra', 'ed7'=>'7 - Infinia D.');
$arrayPicosNumeros = array(1 => 'ed1', 2=>'ns1', 3=> 'ni1', 4=>'ed2', 5=>'ns2', 6=>'ni2',7=>'ud3', 8=>'ed4', 9=>'ud5', 10=>'ud6', 11=>'ed7');
$arrayPicosTanques = array('ed1'=>4, 'ns1'=>3, 'ni1'=>5, 'ed2'=>4, 'ns2'=>3, 'ni2'=>5,'ud3'=>2, 'ed4'=>1, 'ud5'=>6, 'ud6'=>6, 'ed7'=>1);
$arrayClasses = array('ed1'=>'success', 'ns1'=>'info', 'ni1'=>'info', 'ed2'=>'success', 'ns2'=>'info', 'ni2'=>'info','ud3'=>'warning', 'ed4'=>'success', 'ud5'=>'warning', 'ud6'=>'warning', 'ed7'=>'success');

// para el resto
$surtidores = array('ns1' => 1, 'ns2' => 1, 'ed1' => 1, 'ed2' => 1, 'np1' => 1, 'np2' => 1, 'ud1' => 2, 'ed3' => 3, 'ed4' => 4, 'ud5' => 4, 'ud6' => 4);
$productoPorSurtidor = array('ns1' => 'Nafta Super', 'ns2' => 'Nafta Super', 'ed1' => 'Infinia D.', 'ed2' => 'Infinia D.', 'np1' => 'Infinia', 'np2' => 'Infinia', 'ud1' => 'Ultra Diesel', 'ed3' => 'Infinia D.', 'ed4' => 'Infinia D.', 'ud5' => 'Ultra Diesel', 'ud6' => 'Ultra Diesel');

$tanquePorSurtidor = array('ns1' => 3, 'ns2' => 3, 'ed1' => 4, 'ed2' => 4, 'np1' => 5, 'np2' => 5, 'ud1' => 2, 'ed3' => 1, 'ed4' => 4, 'ud5' => 6, 'ud6' => 6);

$colorPorProducto = array('super'=>'info', 'infinia'=>'info', 'euro'=>'success', 'ultra'=>'warning');
$config['minimoDescarga']=1000;
// TODO: cargarlo con un query, usar el mismo para definir empleadosZZ
//$vendedor = array(2=>"BAIER", 3=>"ENGRAFF", 4=>"BARTOLOME", 5=>"ZZ FIGUEROA", 6=>"WALTER", 7=>"BOHN",8=>"BONFIGLI", 9=>"ZZ PERK", 10=>"ZZ CARLOS", 11=>"ZZ HERLEIN", 12=>"STADELMANN", 13=>"SAUER", 14=>"DIETRICH", 15=>"DETZEL", 16=>"PALACIN", 17=>"SCHIMMEL", 18=>"ZZ ZARATE", 19=>"ZZ SIMON", 20=>"DUCA", 21=>"CUBRE VACIONES", 22=>"SUAREZ");
$vendedor=array();
// query para sacar empleados desde la base:
if(!isset($_SESSION['vendedor'])){
  $sqlVendedores = "select idEmpleado, empleado from dbo.empleados where esVendedor=1 and activo=1 and idgrupovendedores=1;";
  $stmt = odbc_exec2($mssql, $sqlVendedores);
  while($rowVendedor = sqlsrv_fetch_array($stmt)){
    $apellido = explode(" ", $rowVendedor['empleado']);
    if(strlen($apellido[0])>3){
      $vendedor[$rowVendedor['idEmpleado']]=$apellido[0];
    } else {
      $vendedor[$rowVendedor['idEmpleado']]=$apellido[0].' '.$apellido[1];
    }
  }
  $_SESSION['vendedor']=$vendedor;
} else {
  $vendedor = $_SESSION['vendedor'];
  asort($vendedor);
}
if(!isset($_SESSION['empleado'])||1){
  $sqlCajeros = "select idEmpleado, empleado, idgrupovendedores from dbo.empleados where esVendedor=1;";
  //$stmt = odbc_exec($mssql, $sqlCajeros);
  $stmt = odbc_exec2($mssql, $sqlCajeros);
  while($rowCajero = sqlsrv_fetch_array($stmt)){
    //fb($rowCajero);
    $apellido = explode(" ", $rowCajero['empleado']);
    if(strlen($apellido[0])>3){
      $empleado[$rowCajero['idgrupovendedores']][$rowCajero['idEmpleado']]=$apellido[0];
    } else {
      $empleado[$rowCajero['idgrupovendedores']][$rowCajero['idEmpleado']]=$apellido[0].' '.$apellido[1];
    }
  }
  $_SESSION['empleado']=$empleado;
} else {
  $empleado = $_SESSION['empleado'];
  asort($empleado);
}


if(!isset($_SESSION['comision'])){
    $stmt = odbc_exec2($mssql, "select preciopublico from Articulos where Codigo=2472"); // saca precio F10 de litro
    $rowVentas = sqlsrv_fetch_array($stmt);
    $_SESSION['comision']=round($rowVentas[0],2);
}
$multiplica = 1;
$comision = $multiplica*$_SESSION['comision'];
$cuantosMeses=12;
$comisionPorTantoPorciento = 10;
$ponderaNoche = 1.5;           // multiplicador para los turnos noches de un solo empleado
$historicoNoAfectadoNoche=1; // 1 histórico es afectado por noches

if(!isset($_SESSION['empleadosZZ'])||1){
    $_SESSION['empleadosZZ'] = "(0";
    $stmt = odbc_exec2($mssql, "select idEmpleado from dbo.empleados where empleado like ('%ZZs%') and activo=0");
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['empleadosZZ'].=", $rowVentas[0]";
    }
    
    $_SESSION['empleadosZZ'].=')';
}
if(!isset($_SESSION['precios'])){
    
}


// TRANSPORTE
if(!isset($_SESSION['transporte_tipos_comisiones'])){
    $stmt = odbc_exec2($mssql3, "select codigo, nombre from dbo.tipopedi", __LINE__); // saca precio F10 de litro
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['transporte_tipos_comisiones'][$rowVentas['codigo']]=$rowVentas['nombre'];
        if (preg_match("/(([0-9]+(.)+[0-9]+%)|([0-9]+%))/", $rowVentas['nombre'], $matches)) {
            $percentage = explode('%',$matches[0]);
            $_SESSION['transporte_alicuotas_comisiones'][$rowVentas['codigo']] = $percentage[0];
        }
    }
}

if(!isset($_SESSION['transporte_libros_contables'])){
    $stmt = odbc_exec2($mssql2, "select codigo, detalle from [sqlcoop_dbimplemen].[dbo].[LIBRASIE]", __LINE__);
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['transporte_libros_contables'][$rowVentas['codigo']]=trim($rowVentas['detalle']);
    }
}


// LOGIN
$langauge = "es";

//Generic website variables
$websiteName = "Cooperativa de Transporte";
$websiteUrl = "http://cooptransporte.caldenoil.com:3128/ypf/"; //including trailing slash

//Do you wish UserPie to send out emails for confirmation of registration?
//We recommend this be set to true to prevent spam bots.
//False = instant activation
//If this variable is falses the resend-activation file not work.
$emailActivation = false;

//In hours, how long before UserPie will allow a user to request another account activation email
//Set to 0 to remove threshold
$resend_activation_threshold = 1;

//Tagged onto our outgoing emails
$emailAddress = "estcotrans@gmail.com";

//Date format used on email's
$emailDate = date("l \\t\h\e jS");

//Directory where txt files are stored for the email templates.
$mail_templates_dir = "models/mail-templates/";

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,$websiteUrl,$emailDate);

//Display explicit error messages?
$debug_mode = false;

//Remember me - amount of time to remain logged in.
$remember_me_length = "1wk";

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Mobile_Detect.php';
if(!isset($_SESSION['esMovil'])){
  $detect = new Mobile_Detect;
  $_SESSION['esMovil'] = ($detect->isMobile()||$detect->isTablet())?true:false; 
}

// FUNCIONES COMUNES
function loguea($idObjetivo, $idAccion, $idRegistro = NULL){
  // Loguea cada acción realizada en el sistema
  
}

function ms_escape_string($data) {
  if ( !isset($data) or empty($data) ) return '';
  if ( is_numeric($data) ) return $data;

  $non_displayables = array(
      '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
      '/%1[0-9a-f]/',             // url encoded 16-31
      '/[\x00-\x08]/',            // 00-08
      '/\x0b/',                   // 11
      '/\x0c/',                   // 12
      '/[\x0e-\x1f]/'             // 14-31
  );
  foreach ( $non_displayables as $regex )
      $data = preg_replace( $regex, '', $data );
  $data = str_replace("'", "''", $data );
  return $data;
}


if(isset($nivelRequerido)){
    if(!isset($loggedInUser)){
        // no logueado y tiene nivel requerido
        $_SESSION['volverLuegoDelLogin']=$_SERVER['REQUEST_URI'];
        header("Location: /login.php");
        die;
    }
    if($loggedInUser->group_id==($nivelRequerido||2||5)){
        // deja pasar
    } else {
        header("Location: 401.php");
        die;
    }
}

function odbc_exec2($db, $sql, $linea=__LINE__, $script=__FILE__){
  // realiza el query y muestra error unificado en caso de falla
  $params = array();
  $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
  $stmt = sqlsrv_query($db, $sql, $params, $options);
  if( $stmt === false ){
    if(odbc_error()==23000||sqlsrv_errors()[0][0]==23000){
      fb("Error SQL, en $script, linea $linea: $sql. INDICE REPETIDO");
    } elseif(odbc_error()==37000||sqlsrv_errors()[0][0]==37000){
      fb("Error SQL, en $script, linea $linea: $sql || ".odbc_errormsg().' - '.odbc_error());
      echo "<span class='alert alert-danger'>Error SQL - 37000</span><br/>$sql";
      die();
    } else {
      echo "Error SQL, en $script, linea $linea:<br/><br/>$sql<br/><br/>";print_r(sqlsrv_errors());
      echo "<span class='alert alert-danger'>Error SQL</span>";
      die();
    }
  }
  return $stmt;
}

function fecha($fecha, $res='dmy', $tipo='ymd'){
  if(is_object($fecha)){
    // fecha es objeto
    switch($res){
      case "Ym":
        $tmp = $fecha->format($res);
        break;
      case "dmyH":
        $tmp = $fecha->format("d/m/Y H:i:s");
        break;
      case "dmy":
      case "dmY":
      default:
        $tmp = $fecha->format("d/m/Y");
        break;
    }
  } else {
    if(strlen($fecha)==23){
      $fecha=substr($fecha, 0, -4);
    }
    switch($res){
      case "Ym":
      $tmp = substr($fecha, 0,4).substr($fecha, 5,2);
      break;
      case "dmyH":
      $tmp = substr($fecha, 8,2).'/'.substr($fecha, 5,2).'/'.substr($fecha, 0,4).' '.substr($fecha,-8);
      break;
      case "sql":
      $tmp2 = explode('/', $fecha);
      $tmp = $tmp2[2].'/'.$tmp2[1].'/'.$tmp2[0];
      break;
      case "dmy":
      case "dmY":
      default:
      $tmp = substr($fecha, 8,2).'/'.substr($fecha, 5,2).'/'.substr($fecha, 0,4);
      break;
    }
  }
  return $tmp;
}


if (!function_exists('stats_standard_deviation')) {
  /**
    * This user-land implementation follows the implementation quite strictly;
    * it does not attempt to improve the code or algorithm in any way. It will
    * raise a warning if you have fewer than 2 values in your array, just like
    * the extension does (although as an E_USER_WARNING, not E_WARNING).
    *
    * @param array $a
    * @param bool $sample [optional] Defaults to false
    * @return float|bool The standard deviation or false on error.
    */
  function stats_standard_deviation(array $a, $sample = false) {
    $n = count($a);
    if ($n === 0) {
      trigger_error("The array has zero elements", E_USER_WARNING);
      return false;
    }
    if ($sample && $n === 1) {
      trigger_error("The array has only 1 element", E_USER_WARNING);
      return false;
    }
    $mean = array_sum($a) / $n;
    $carry = 0.0;
    foreach ($a as $val) {
      $d = ((double) $val) - $mean;
      $carry += $d * $d;
    };
    if ($sample) {
      --$n;
    }
    return sqrt($carry / $n);
  }
}

?>
