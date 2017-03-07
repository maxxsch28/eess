<?php
// calculaPromedios.php
include_once($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$limit=11;
$offset=0;

if(isset($_GET['alterno'])&&$_GET['alterno']==1){
  if(!isset($_SESSION['soloMios'])||$_SESSION['soloMios']==0){
    $_SESSION['soloMios']=1;
  } else 
    $_SESSION['soloMios']=0;
}
$filtroUsuarios = (!isset($_SESSION['soloMios'])||$_SESSION['soloMios']==0)?"":" AND tmpBuscaAsientos.user_id=".$loggedInUser->user_id;

$sql = "SELECT id, importe, leyenda, fuzzyness, ambito, rangoinicio, rangofin, cuentaEESS, cuentaTransporte, cantidadusos, username, color FROM tmpBuscaAsientos, users_users WHERE tmpBuscaAsientos.user_id=users_users.user_id $filtroUsuarios ORDER BY id DESC LIMIT 10;";
//fb($sql);
$result = $mysqli->query($sql);
while($row = $result->fetch_assoc()){
  if($row['rangoinicio']=='2011-01-01'&&$row['rangofin']=='2069-12-31'){
    $rango = "<b>histórico";
  } elseif($row['rangoinicio']==date("Y").'-01-01'&&$row['rangofin']==date("Y").'-12-31'){
    $rango = "año <b>".date("Y");
  } elseif($row['rangoinicio']==(date("Y")-1).'-01-01'&&$row['rangofin']==(date("Y")-1).'-12-31'){
    $rango = "año <b>".(date("Y")-1);
  } else {
    $rango = "<b>$row[rangoinicio]</b> a <b>$row[rangofin]";
  }
  $contiene = ($row['leyenda']<>'')?"contiene <b>\"$row[leyenda]\"</b>":'';
  $cuenta = ($row['cuentaEESS']<>0)?" en cuenta Calden \"$row[cuentaEESS]\"":'';
  $cuenta .= ($row['cuentaTransporte']<>0)?" en cuenta Setup \"$row[cuentaTransporte]\"":'';
  $ambito = ($row['ambito']=='integral')?"primary":(($row['ambito']=='eess')?"success":"warning");
  
  echo "<tr class='histAsiento' id='$row[id]'><td><span class='label label-$row[color]'>".strtoupper($row['username'][0])."</span></td><td>\$$row[importe]</td><td>$rango</b></td><td>{$contiene}{$cuenta}</td><td><span class='label label-$ambito'>".strtoupper($row['ambito'][0])."</span></td></tr>";
}
/*
  } else{
    // inserto una nueva búsqueda // 28-09-1977
    $fuzyness = (isset($_REQUEST['fuzzy']))?$_REQUEST['fuzziness']:0;
    $rangoInicio = substr($_REQUEST['rangoInicio'], 6).'-'.substr($_REQUEST['rangoInicio'], 0,2).'-'.substr($_REQUEST['rangoInicio'], 3,2);
    $rangoFin = substr($_REQUEST['rangoFin'], 6).'-'.substr($_REQUEST['rangoFin'], 0,2).'-'.substr($_REQUEST['rangoFin'], 3,2);
    $sql = "INSERT INTO tmpbuscaasientos (ambito, importe, rangoInicio, rangoFin, fuzzyness, leyenda, cuenta, user_id) VALUES ('$_REQUEST[ambito]', '$_REQUEST[importe]', '$rangoInicio', '$rangoFin', $fuzyness, '".((isset($_REQUEST['leyenda'])&&$_REQUEST['leyenda']>'')?mysqli_real_escape_string($mysqli, $_REQUEST['leyenda']):'')."', '".((isset($_REQUEST['cuenta'])&&$_REQUEST['cuenta']>0)?$_REQUEST['cuenta']:0)."', $loggedInUser->user_id)";
    fb($sql);
  }
  if(!isset($_SESSION['ultimoSQL'])||$_SESSION['ultimoSQL']<>$sql){
    $result = $mysqli->query($sql);
    $_SESSION['ultimoSQL']=$sql;
  } else {
    $sql = "SELECT 1;";
  }
  //$result = $mysqli->query($sql);
}*/
?>
