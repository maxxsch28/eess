<?php 
// inicia.php
setlocale(LC_NUMERIC, 'en_US');
$dbg=1;
$controlTiempo=microtime(true);
//Database Information
$dbtype = "mysqli"; 
$db_host = "localhost";
$db_user = "coopetrans";
$db_pass = "vGCP6eZ6dqUFZ2pB";
$db_name = "pedidosypf";
$db_name2 = "transporte";
$db_name3 = 'cuentaypf';
$db_name4 = 'movistar';
$db_port = "3306";
$db_table_prefix = "users_";
$clienteYER = 1283;

$tercerEmpleado = 24; // federico
$CFG = new stdClass(); 
$CFG->tomaLitrosDesdeTabla = false;
$CFG->tanquesATomarMilimetrosDesdeTablas = array(7); // 7 es para que ningún tanque de true.
$CFG->fechaDesdeDondeTomoPromedioHistoricos = "2017-01-01";
$CFG->tipoFechaSQL = "Y-d-m";
 
//Dbal Support - Thanks phpBB ; )
require_once($_SERVER['DOCUMENT_ROOT']."/classes/mysqli.php");

//Construct a db instance
$db = new $sql_db();
if(is_array($db->sql_connect($db_host, $db_user,$db_pass,$db_name, $db_port, false, false))){
  die("MYSQL No se puede conectar a la base de datos");
}

require_once($_SERVER['DOCUMENT_ROOT']."/include/es.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/class.user.php");
require_once($_SERVER['DOCUMENT_ROOT']."/func/funcs.user.php");
require_once($_SERVER['DOCUMENT_ROOT']."/func/funcs.general.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/class.newuser.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/ChromePhp.php'); //firebug

session_start();
//ob_start(); //firebug
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

/* cambio de odbc por nuevo driver MS */
$serverName = "192.168.1.13";
$connectionOptions = array(
    "Database" => "CoopDeTrabajo.Net",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions2 = array(
    "Database" => "sqlcoop_dbimplemen",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions3 = array(
    "Database" => "sqlcoop_dbshared",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
$connectionOptions4 = array(
    "Database" => "coop",
    "Uid" => "sa",
    "PWD" => "B8000ftq"
);
//Establishes the connection


/* Connect using Windows Authentication. */  

try {  
  $conn = new PDO( "sqlsrv:server=$serverName ; Database=CoopDeTrabajo.Net", "sa", "B8000ftq");  
  $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
}  
catch(Exception $e) {   
  die( print_r(__FILE__.' '.__LINE__.' '. $e->getMessage() ) );   
} 



if (!function_exists('sqlsrv_connect')) {
  debug2(__LINE__);
    echo "sqlsrv_connect functions are not available.<br />\n";
}
//echo phpinfo();

$mssql = sqlsrv_connect($serverName, $connectionOptions);
//print_r(sqlsrv_server_info($mssql));
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
$mssql4 = sqlsrv_connect($serverName, $connectionOptions4);
if( $mssql4 === false ){
  echo "MSSQL4 - No pudo conectarse:</br>";
  die( print_r( sqlsrv_error().' - '.sqlsrv_errormsg(), true));
}

$date = array("Sunday"=>"Domingo", "Monday"=>"Lunes", "Tuesday"=>"Martes", "Wednesday"=>"Miércoles","Thursday"=>"Jueves", "Friday"=>"Viernes", "Saturday"=>"Sábado");
$weekday = array("Sunday"=>"Dom", "Monday"=>"Lun", "Tuesday"=>"Mar", "Wednesday"=>"Mie","Thursday"=>"Jue", "Friday"=>"Vie", "Saturday"=>"Sab");
$date2 = array(7=>"Domingo",1=>"Lunes",2=>"Martes",3=>"Miércoles",4=>"Jueves",5=>"Viernes",6=>"Sábado");
$mes = array(1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre");
// codigo Calden para los combustibles


$articulo = array();
if(!isset($_SESSION['articulo'])){
  $sql = "SELECT a.IdArticulo, a.Descripcion, ColorARGB, b.IdFamiliaArticulo, c.Descripcion as familia, CodigoControladorSurtidores, PrecioPublico FROM dbo.articulos a, dbo.gruposarticulos b, dbo.FamiliasArticulos c, dbo.CodigosArticulosPorControladorSurtidor d WHERE a.IdGrupoArticulo=b.IdGrupoArticulo AND b.Combustible=1 AND b.IdFamiliaArticulo=c.IdFamiliaArticulo AND d.IdArticulo=a.IdArticulo ORDER BY CodigoControladorSurtidores;";
  $stmt = odbc_exec2($mssql, $sql);
  while($fila = sqlsrv_fetch_array($stmt)){
    $articulo[$fila['IdArticulo']]['idArticulo']=$fila['IdArticulo'];
    $articulo[$fila['IdArticulo']]['descripcion']=ucwords(strtolower($fila['Descripcion']));
    $abr = explode(' ', $fila['Descripcion']);
    $abreviatura = '';
    foreach($abr as $inicial){
      $abreviatura = $abreviatura.$inicial[0];
    }
    $articulo[$fila['IdArticulo']]['abr']=strtoupper($abreviatura);
    $articulo[$fila['IdArticulo']]['Color']=$fila['ColorARGB'];
    $articulo[$fila['IdArticulo']]['familia']=$fila['familia'];
    $articulo[$fila['IdArticulo']]['CodigoControladorSurtidores']=$fila['CodigoControladorSurtidores'];
    $articulo[$fila['IdArticulo']]['precio']=$fila['PrecioPublico'];
    if(!isset($premium[$fila['familia']])){
      $premium[$fila['familia']] = $fila['familia'];
      $premiumArticulo[$fila['familia']] = $fila['IdArticulo'];
      $precioPremium[$fila['familia']] = $fila['PrecioPublico'];
      $articulo[$fila['IdArticulo']]['premium']=true;
      
    } else if($premium[$fila['familia']]==$fila['familia']&&$precioPremium[$fila['familia']]<$fila['PrecioPublico']){
      $articulo[$premiumArticulo[$fila['familia']]]['premium']=false;
      $familia[$fila['familia']][] = $fila['IdArticulo'];
      $precioPremium[$fila['familia']] = $fila['PrecioPublico'];
      $articulo[$fila['IdArticulo']]['premium']=true;
    }
  }
  $_SESSION['articulo']=$articulo;
} else {
  $articulo = $_SESSION['articulo'];
}

/*
$sqlCombustibles = "select IdArticulo, a.Descripcion, ColorARGB, b.IdFamiliaArticulo, c.Descripcion as familia from dbo.articulos a, dbo.gruposarticulos b, dbo.FamiliasArticulos c where a.IdGrupoArticulo=b.IdGrupoArticulo and b.Combustible=1 and b.IdFamiliaArticulo=c.IdFamiliaArticulo;";
$stmt = odbc_exec2($mssql, $sqlCombustibles);
while($fila = sqlsrv_fetch_array($stmt)){
  $articulo[$fila['IdArticulo']]=$fila['Descripcion'];
  
  //$this->articulo[$articulo['IdArticulo']]['descripcion']=$articulo['Descripcion'];
  //$this->articulo[$articulo['IdArticulo']]['Color']=$articulo['ColorARGB'];
  //$this->articulo[$articulo['IdArticulo']]['familia']=$articulo['familia'];
}*/

// No se como pasar esto a algo mas automático. Ahora queda Hardcodeado
$classArticulo = array(2068=>"success",2069=>"warning",2076=>"info",2078=>"info2");

$toleranciaTanques = 50; // cuantos litros mas menos están tolerados para el control de stock.

// unificar nombres picos TODO
// para cierreCem
$arrayPicos = array('ed1'=>'1a - Infinia D.', 'ns1'=>'1b - Super', 'ni1'=>'1c -Infinia', 'ed2'=>'2a - Infinia D.', 'ns2'=>'2b - Super', 'ni2'=>'2c -Infinia','ud3'=>'3 - Ultra', 'ed4'=>'4 - Infinia D.', 'ud5'=>'5 - Ultra', 'ud6'=>'6 - Ultra', 'ed7'=>'7 - Infinia D.');
$arrayPicosNumeros = array(1 => 'ed1', 2=>'ns1', 3=> 'ni1', 4=>'ed2', 5=>'ns2', 6=>'ni2',7=>'ud3', 8=>'ed4', 9=>'ud5', 10=>'ud6', 11=>'ed7');
$arrayPicosTanques = array('ed1'=>4, 'ns1'=>3, 'ni1'=>5, 'ed2'=>4, 'ns2'=>3, 'ni2'=>5,'ud3'=>2, 'ed4'=>1, 'ud5'=>6, 'ud6'=>6, 'ed7'=>1);

// TODO eliminar esto
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
debug2(__LINE__);
if(!isset($_SESSION['empleado'])){
  $sqlCajeros = "select idEmpleado, empleado, idgrupovendedores from dbo.empleados where esVendedor=1;";
  $stmt = odbc_exec2($mssql, $sqlCajeros);
  while($rowCajero = sqlsrv_fetch_array($stmt)){
    //ChromePhp::log($rowCajero);
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

$fechaCambioTurno = new DateTime('2017-07-01');
$hoy = new DateTime();
$cuantosDiasDesdeElCambio = $hoy->diff($fechaCambioTurno);
$cuantosMesesDesdeElCambio = ($cuantosDiasDesdeElCambio->format('%y') * 12) + $cuantosDiasDesdeElCambio->format('%m');
$cuantosMeses=($cuantosMesesDesdeElCambio<12)?$cuantosMesesDesdeElCambio-1:12;

$comisionPorTantoPorciento = 10;
$ponderaNoche = 1.5;           // multiplicador para los turnos noches de un solo empleado
$historicoNoAfectadoNoche=1; // 1 histórico es afectado por noches

if(!isset($_SESSION['empleadosZZ'])){
    $_SESSION['empleadosZZ'] = "(0";
    $stmt = odbc_exec2($mssql, "select idEmpleado from dbo.empleados where empleado like ('%ZZ%') and activo=0", __FILE__, __LINE__);
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['empleadosZZ'].=", $rowVentas[0]";
    }
    $_SESSION['empleadosZZ'].=')';
}

// TRANSPORTE
if(!isset($_SESSION['transporte_tipos_comisiones'])){
    $stmt = odbc_exec2($mssql3, "select codigo, nombre from dbo.tipopedi", __LINE__); // saca precio F10 de litro
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['transporte_tipos_comisiones'][$rowVentas['codigo']]=$rowVentas['nombre'];
        if (preg_match("/(([0-9]+(.)+[0-9]+%)|([0-9]+%))/", $rowVentas['nombre'], $matches)) {
            $percentage = explode('%',$matches[0]);
            $_SESSION['transporte_alicuotas_comisiones'][$rowVentas['codigo']] = trim($percentage[0]);
        }
    }
}

if(!isset($_SESSION['transporte_libros_contables'])){
    $stmt = odbc_exec2($mssql2, "select codigo, detalle from [sqlcoop_dbimplemen].[dbo].[LIBRASIE]", __LINE__);
    while($rowVentas = sqlsrv_fetch_array($stmt)){
        $_SESSION['transporte_libros_contables'][$rowVentas['codigo']]=trim($rowVentas['detalle']);
    }
}
debug2(__LINE__);

// LOGIN
$langauge = "es";

//Generic website variables
$websiteName = "Cooperativa de Transporte";
$websiteUrl = "http://cooptransporte.ddns.net/"; //including trailing slash

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

debug2(__LINE__);

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
    $errorSQL = sqlsrv_errors();
    // en formato viejo se usaba la funcion odbc_error()
    if(sqlsrv_errors()[0][0]==23000){
      ChromePhp::log("Error SQL, en $script, linea $linea: $sql. INDICE REPETIDO");
    } elseif(sqlsrv_errors()[0][0]==37000){
      ChromePhp::log("Error SQL, en $script, linea $linea: $sql || ".$errorSQL['code'].' - '.$errorSQL['message']);
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
    //ChromePhp::log('Fecha es objeto');
    // fecha es objeto
    switch($res){
      case "Ym":
        $tmp = $fecha->format($res);
        break;
      case "dmyH":
        if($fecha->format("H:i:s")<>'00:00:00')
          $tmp = $fecha->format("d/m/y H:i:s");
        else {
          $tmp = $fecha->format("d/m/y");  
        }
        
        break;
      case "dmy":
      case "dmY":
      default:
        $tmp = $fecha->format("d/m/Y");
        break;
    }
  } else {
    ChromePhp::log('Fecha NO es objeto');
    if(strlen($fecha)==23){
      $fecha=substr($fecha, 0, -4);
    } 
    ChromePhp::log('Tipo de fecha esperada: '.$res);
    switch($res){
      case "Ym":
      $tmp = substr($fecha, 0,4).substr($fecha, 5,2);
      break;
      case "dmyH":
      $tmp = substr($fecha, 8,2).'/'.substr($fecha, 5,2).'/'.substr($fecha, 0,4).' '.substr($fecha,-8);
      break;
      case "sql":
      $tmp2 = explode('/', $fecha);
      $tmp = ((strlen($tmp2[2]<3)?'20':'')).$tmp2[2].'/'.$tmp2[1].'/'.$tmp2[0];
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

function is_decimal( $val ){
    return is_numeric( $val ) && floor( $val ) != $val;
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

function peso($valor){
  return number_format($valor, 2, ',', '.');
}

function apellidoEmpleado($empleado){
  // recibe nombre completo empleado y devuelve solo apellido
  $partes = explode(" ", $empleado);
  $apellido = $partes[0];
  if(strlen($partes[0])<3){
    $apellido = $apellido.' '.$partes[1];
  }
  return $apellido;
}

/* FUNCIONES AUTOMATICAS DE TANQUES Y PRODUCTOS */
function tanques($desde='', $hasta=''){
  global $mssql;
  $tanque = array();
  // obtengo datos de tanques de CaldenOil
  $sql = "SELECT IdTanque, IdArticulo, Capacidad, AdvertirSiNivelCombustibleMenorALitros, SolicitarCombustibleCuandoNivelMenorALitros, 0 as LastUpdated, 0 as Litros, 0 as Agua FROM dbo.tanques  UNION SELECT tt.IdTanque, 0, 0, 0, 0, tt.LastUpdated, tt.Litros, tt.NivelAgua FROM dbo.tanquesmediciones tt INNER JOIN (SELECT IdTanque, MAX(lastupdated) AS MaxDateTime FROM dbo.TanquesMediciones GROUP BY IdTanque) groupedtt ON tt.IdTanque = groupedtt.IdTanque AND tt.LastUpdated = groupedtt.MaxDateTime ORDER BY IdTanque;";

  $stmt = odbc_exec2($mssql, $sql, __FILE__, __LINE__);
  while($fila = sqlsrv_fetch_array($stmt)){
    if($fila['IdArticulo']>0){
      $tanque[$fila['IdTanque']]['idArticulo']=$fila['IdArticulo'];
      $tanque[$fila['IdTanque']]['capacidad']=$fila['Capacidad'];
      $tanque[$fila['IdTanque']]['nivelSuspender']=$fila['AdvertirSiNivelCombustibleMenorALitros'];
      $tanque[$fila['IdTanque']]['nivelPedir']=$fila['SolicitarCombustibleCuandoNivelMenorALitros'];
    } else {
      $tanque[$fila['IdTanque']]['litros']=$fila['Litros'];
      $tanque[$fila['IdTanque']]['agua']=$fila['Agua'];
      $tanque[$fila['IdTanque']]['ultimamedicion']=$fila['LastUpdated'];
    }
    if(isset($tanque[$fila['IdTanque']]['litros'])&&isset($tanque[$fila['IdTanque']]['capacidad'])){
      $tanque[$fila['IdTanque']]['disponible'] = $tanque[$fila['IdTanque']]['capacidad']-$tanque[$fila['IdTanque']]['litros'];
      $tanque[$fila['IdTanque']]['ocupacion'] = $tanque[$fila['IdTanque']]['litros']/$tanque[$fila['IdTanque']]['capacidad'];
    }
  }
  return $tanque;
}

function picos($desde='', $hasta=''){
  global $mssql, $articulo;
  $pico = array();
  // obtengo datos de tanques de CaldenOil
  // si desde y hacia estan vacíos no da información de litros despachados
  $sqlTanques = "SELECT IdManguera, IdArticulo, a.IdSurtidor, IdTanque, 0 as litros, 0 as importe, 0 as q FROM dbo.mangueras a, dbo.surtidores b WHERE a.IdSurtidor=b.IdSurtidor AND b.IdControladorSurtidores IS NOT NULL UNION select IdManguera, IdArticulo,0,0, SUM(Cantidad) as cantidad, SUM(importe) as importe, COUNT(Cantidad) as q FROM dbo.Despachos where fecha>='$desde' AND fecha<'$hasta' group by IdManguera, IdArticulo ORDER BY IdManguera ASC;";
  $stmt = odbc_exec2($mssql, $sqlTanques);
  while($fila = sqlsrv_fetch_array($stmt)){
    if($fila['IdTanque']>0){
      $pico[$fila['IdManguera']]['idArticulo']=$fila['IdArticulo'];
      $pico[$fila['IdManguera']]['surtidor']=$fila['IdSurtidor'];
      $pico[$fila['IdManguera']]['tanque']=$fila['IdTanque'];
    } else {
      $pico[$fila['IdManguera']]['litrosDiario']=$fila['litros'];
      $pico[$fila['IdManguera']]['importeDiario']=$fila['importe'];
      $pico[$fila['IdManguera']]['qDespachos']=$fila['q'];
      // en el mismo momento que saco información por picos ya agrupo por tanques
      $articulo[$fila['IdArticulo']]['litrosDiario'] += $fila['litros'];
      $articulo[$fila['IdArticulo']]['qDespachos'] += $fila['q'];
    }
  }
  return $pico;
}






function debug2($linea) {
  //echo "ping, linea $linea<br>";

}

debug2(__LINE__);
?>
