<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411'; 
if(isset($_POST['muestraComprimido'])){
  if($_POST['muestraComprimido']==1){
    $_SESSION['muestraComprimido']=1;
    setcookie( "muestraComprimido", 1, strtotime( '+30 days' ) );
  } else {
    $_SESSION['muestraComprimido']=0;
    setcookie( "muestraComprimido", 0, strtotime( '+30 days' ) );
   } 
  fb($_SESSION['muestraComprimido']);
  die;
}
if(isset($_POST['ocultaMenu'])){
  if($_POST['ocultaMenu']==1){
    $_SESSION['ocultaMenu']=1;
    setcookie( "ocultaMenu", 1, strtotime( '+30 days' ) );
  } else {
    $_SESSION['ocultaMenu']=0;
    setcookie( "ocultaMenu", 0, strtotime( '+30 days' ) );
   } 
  fb($_SESSION['ocultaMenu']);
  die;
}


$sqlSocios = "select codigo, choferes.nombre as nombreChofer, celular, choferes.telefono, cuil, vtopsicofi, vtocarnet, segurovida, vtocharla, equipo, acoplado, fleteros.nombre as nombreFletero, fleteros.cuit from dbo.choferes, dbo.fleteros WHERE dbo.choferes.fletero = dbo.fleteros.fletero AND dbo.fleteros.pidecta=1 AND choferes.inhabfecha<=choferes.rehabfecha AND fleteros.inhabfecha<=fleteros.rehabfecha ORDER BY fleteros.nombre;";

$sqlSociosDedicados = "select codigo, choferes.nombre as nombreChofer, celular, choferes.telefono, cuil, vtopsicofi, vtocarnet, segurovida, vtocharla, equipo, acoplado, fleteros.nombre as nombreFletero, fleteros.cuit from dbo.choferes, dbo.fleteros WHERE dbo.choferes.fletero = dbo.fleteros.fletero AND dbo.fleteros.pidecta=1 AND numeccpro=1 AND choferes.inhabfecha<=choferes.rehabfecha AND fleteros.inhabfecha<=fleteros.rehabfecha ORDER BY fleteros.nombre;";


$sqlPosicionFlecha = "SELECT DISTINCT tipo, fecha, chofer, posicionflecha.idEstado, estado FROM `posicionflecha` , `estados` WHERE estados.idEstado = posicionflecha.idestado ORDER BY idPosicion DESC LIMIT 3";
$resPosicionFlecha = $mysqli2->query($sqlPosicionFlecha);
while($filaPosicionFlecha = $resPosicionFlecha->fetch_assoc()){
  $flecha[$filaPosicionFlecha['tipo']] = $filaPosicionFlecha;
  $flechaChofer[]=$filaPosicionFlecha['chofer'];
}

// echo $sqlSocios;


$stmt = sqlsrv_query( $mssql2, $sqlSocios);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlSocios<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$stmtDedicado = sqlsrv_query( $mssql2, $sqlSocios);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlSocios<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas = $cantidadClientes = $totalCantidad = 0;
$comision=array();
$totalAComisionar = array();
//var_dump($flecha);
fb($flecha);
while($fila = sqlsrv_fetch_array($stmt)){
  $problemaVencimiento=false;
  $choferConFlecha = false;
  $desactivado = false;
  $datosViaje = "";
  // verifico que el chofer no esté con viaje sin cumplir
  $sqlChoferDisponible = "SELECT sucursal_e, parte, loc_origen, loc_desti, salida  FROM [sqlcoop_dbimplemen].[dbo].[partes] WHERE chofer=$fila[codigo] AND cumplido=0 AND anulado=0 ORDER BY salida DESC";
  $stmt2 = sqlsrv_query( $mssql2, $sqlChoferDisponible);
  if( $stmt2 === false ){
    echo "1. Error in executing query.</br>$sqlChoferDisponible<br/>";
    die( print_r( sqlsrv_errors(), true));
  }
  if(sqlsrv_has_rows($stmt2)){
    // El chofer tiene viaje sin cumplir
    $desactivado = true;
    $filaViaje = sqlsrv_fetch_array($stmt2);
    $datosViaje = "<span class='glyphicon glyphicon-warning-sign'></span>&nbsp;<span class='text-danger'>De $filaViaje[loc_origen] a $filaViaje[loc_desti]. Salida ".$filaViaje['salida']->format('d/m/y').'</span>';
  }
  
  // detecto si este chofer tiene la flecha actualmente
  if(in_array($fila['codigo'], $flechaChofer)){
    $choferConFlecha = true;
  }
  
  
  
  $tabla2 = "";
  if($desactivado){
    $tabla2 .= "<span class='glyphicon glyphicon-remove-sign pull-left'></span>&nbsp;";
  }//<small>$fila[codigo]</small>
  $name= "";
  $tarjeta = "tarjeta";
  if($flecha['corta']['chofer']==$fila['codigo']){
    // La corta está en este chofer
    $tabla2 .= "<span class='pull-right label label-danger flecha'>C</span>";
    $name = "<a name='flechaC'></a>";
    $tarjeta = "";
  }
  if($flecha['media']['chofer']==$fila['codigo']){
    // La media está en este chofer
    $tabla2 .= "<span class='pull-right label label-danger flecha'>M</span>";
    $name = "<a name='flechaM'></a>";
    $tarjeta = "";
  }
  if($flecha['larga']['chofer']==$fila['codigo']){
    // La larga está en este chofer
    $tabla2 .= "<span class='pull-right label label-danger flecha' alt='Larga'>L</span>";
    $name = "<a name='flechaL'></a>";
    $tarjeta = "";
  }

  //((strtolower(trim($fila['nombreChofer']))<>strtolower(trim($fila['nombreFletero'])))
  $tabla .= "$name<div class='panel $tarjeta".(($desactivado)?' panel-default text-danger':(($choferConFlecha)?' panel-success':' panel-info')).((!isset($primero))?' primero':'')."' id='ch_$fila[codigo]'><div class='panel-heading'>".$tabla2.utf8_encode(ucwords(strtolower($fila['nombreFletero'])))."</div>
  <div class='panel-body small'>".(($fila['cuil']<>$fila['cuit'])?"<span class='pull-right'><b>".utf8_encode(ucwords(strtolower(trim($fila['nombreChofer']))))."</b></span>":'').(($fila['celular']>0)?"<span class='glyphicon glyphicon-phone'></span><a href='tel:$fila[celular]'> $fila[celular]</a><br/>":'').(($fila['telefono']>0)?"<span class='glyphicon glyphicon-phone-alt'></span><a href='tel:$fila[telefono]'> $fila[telefono]</a><br/>":'');
  $tabla .= $datosViaje;
  $vencimiento = "</div><div class='vencimientos alert-danger panel-footer small'>";
    if($fila['vtopsicofi']->format('d/m/Y')<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Psfísico ".$fila['vtopsicofi']->format('d/m/y').'<br/>';$problemaVencimiento=true;
    }
    if($fila['vtocarnet']->format('d/m/Y')<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Carnet ".$fila['vtocarnet']->format('d/m/y').'<br/>';$problemaVencimiento=true;}
    if($fila['segurovida']->format('d/m/Y')<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Seguro ".$fila['segurovida']->format('d/m/y').'<br/>';$problemaVencimiento=true;}
    if($fila['vtocharla']->format('d/m/Y')<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Charla ".$fila['vtocharla']->format('d/m/y').'<br/>';$problemaVencimiento=true;}
    $vencimiento .= "</div>";
  $tabla .= (($problemaVencimiento)?$vencimiento:'</div>')."</div>";
  $primero = 1;
}

echo $tabla;
?>
