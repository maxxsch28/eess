<?php
// cargaUltimasFacturasCliente.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
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
  ChromePhp::log($_SESSION['muestraComprimido']);
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
  ChromePhp::log($_SESSION['ocultaMenu']);
  die;
}


$sqlSocios = "select codigo, choferes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombreChofer, celular, choferes.telefono, cuil, vtopsicofi, vtocarnet, segurovida, vtocharla, equipo, acoplado, fleteros.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombreFletero, fleteros.cuit from dbo.choferes, dbo.fleteros WHERE dbo.choferes.fletero = dbo.fleteros.fletero AND dbo.fleteros.pidecta=1 AND choferes.inhabfecha<=choferes.rehabfecha AND fleteros.inhabfecha<=fleteros.rehabfecha ORDER BY fleteros.nombre;";

$sqlSociosDedicados = "select codigo, choferes.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombreChofer, celular, choferes.telefono, cuil, vtopsicofi, vtocarnet, segurovida, vtocharla, equipo, acoplado, fleteros.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombreFletero, fleteros.cuit from dbo.choferes, dbo.fleteros WHERE dbo.choferes.fletero = dbo.fleteros.fletero AND dbo.fleteros.pidecta=1 AND numeccpro=1 AND choferes.inhabfecha<=choferes.rehabfecha AND fleteros.inhabfecha<=fleteros.rehabfecha ORDER BY fleteros.nombre;";


$sqlPosicionFlecha = "SELECT DISTINCT tipo, fecha, chofer, posicionflecha.idEstado, estado FROM `posicionflecha` , `estados` WHERE estados.idEstado = posicionflecha.idestado ORDER BY idPosicion DESC LIMIT 3";
$resPosicionFlecha = $mysqli2->query($sqlPosicionFlecha);
while($filaPosicionFlecha = $resPosicionFlecha->fetch_assoc()){
  $flecha[$filaPosicionFlecha['tipo']] = $filaPosicionFlecha;
  $flechaChofer[]=$filaPosicionFlecha['chofer'];
}

// echo $sqlSocios;


$stmt = odbc_exec2( $mssql2, $sqlSocios, __LINE__, __FILE__);

$stmtDedicado = odbc_exec2( $mssql2, $sqlSocios, __LINE__, __FILE__);

$tabla = "";$a=0;
$totalB = 0;
$totalA = 0;
$totalNeto = $totalIVA = $totalPrecio = $cantidadFacturas = $cantidadClientes = $totalCantidad = 0;
$comision=array();
$totalAComisionar = array();
//var_dump($flecha);
ChromePhp::log($flecha);
while($fila = sqlsrv_fetch_array($stmt)){
  $problemaVencimiento=false;
  $choferConFlecha = false;
  $desactivado = false;
  $datosViaje = "";
  // verifico que el chofer no esté con viaje sin cumplir
  $sqlChoferDisponible = "SELECT sucursal_e, parte, loc_origen, loc_desti, salida  FROM [sqlcoop_dbimplemen].[dbo].[partes] WHERE chofer=$fila[codigo] AND cumplido=0 AND anulado=0 ORDER BY salida DESC";
  $stmt2 = odbc_exec2($mssql2, $sqlChoferDisponible, __LINE__, __FILE__);
  $filaViaje = sqlsrv_fetch_array($stmt2);
  if(is_array($filaViaje)){
    // El chofer tiene viaje sin cumplir
    $desactivado = true;
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
    $vtopsicofi=date(substr($fila['vtopsicofi'],0,10));
    $vtocarnet=date(substr($fila['vtocarnet'],0,10));
    $segurovida=date(substr($fila['segurovida'],0,10));
    $vtocharla=date(substr($fila['vtocharla'],0,10));
    if($vtopsicofi<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Psfísico $vtopsicofi<br/>";$problemaVencimiento=true;
    }
    if($vtocarnet<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Carnet $vtocarnet<br/>";$problemaVencimiento=true;}
    if($segurovida<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Seguro $segurovida<br/>";$problemaVencimiento=true;}
    if($vtocharla<=date('Y-m-d', strtotime("+10 days"))){$vencimiento.="Charla $vtocharla<br/>";$problemaVencimiento=true;}
    $vencimiento .= "</div>";
  $tabla .= (($problemaVencimiento)?$vencimiento:'</div>')."</div>";
  $primero = 1;
}

echo $tabla;
?>
