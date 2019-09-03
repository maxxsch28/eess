<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;

if(!isset($_REQUEST['rangoInicio']))$_REQUEST['rangoInicio']='2019-01-01';

$andFecha=(isset($_REQUEST['rangoFin']))?" fecha>='$_REQUEST[rangoInicio]' AND fecha<='$_REQUEST[rangoFin]'":" fecha>='$_REQUEST[rangoInicio]'";
// if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
//   $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
//   $andFecha=" AND femision>='$_REQUEST[mes]' AND femision<='$mesFin'";
// }

$andConciliado = (isset($_POST['soloNoConciliado']))?" AND idConciliado=0":"";

$sqlReintegros = "SELECT idYer, fecha, despachos, ns, ud, np, ed, rv, idMatch FROM yer WHERE $andFecha $andConciliado ORDER BY Fecha ASC";


ChromePhp::log($sqlReintegros);

$result = $mysqli->query($sqlReintegros);

$tabla = "";$a=0;$q=0;
while($rowYPF = $result->fetch_assoc()){
  $negativo = ($rowYPF['rv']==0)?"":'neg';
  if($rowYPF['idMatch']==0){
    // aun no conciliado
    $classConciliado='noConciliado';
    
    
    $tdConciliado = "<td class=''><input type='checkbox' name='idypf[]' value='$rowYPF[idYer]' class='ypf $negativo'/> $rowYPF[id]</td>";
  } else {
    $classConciliado="conciliado_$rowYPF[idMatch]";
    $tdConciliado = "<td class='m$rowYPF[idMatch]'><span class='label label-info mConciliado'>$rowYPF[idMatch]</label></td>";
  }
  // reviso si el turno pertenece a caja cerrada o no
  // levanto los precios historicos del dia
  //$anio_mes = ($rowYPF['femision'], "Ym");
  //echo $sqlPreciosHistoricos;
  
  //$caja = ($rowYPF['IdCaja']==1)?'<span class="badge badge-warning">PLAYA</span>':'<span class="badge badge-info">SHOP</span>';
  //15
  $clase = ($rowYPF['rv']==0)?"success":'danger';
  
  $femision3 = explode('-', $rowYPF['fecha']);
  if($rowYPF['rv']>0){
    $rv = substr($rowYPF['rv'],0,4).'-'.substr($rowYPF['rv'],4);
  } else {
  $rv = "...";
  }
  $multiplicador = ($rowYPF['despachos']==1)?-1:1;
  echo "<tr class='alert alert-$clase $classConciliado' id='ypf_$rowYPF[id]'>
    <td>$femision3[2]/$femision3[1]/$femision3[0]</td>
    <td>$rv</td>
    <td>$rowYPF[ns]</td>
    <td>$rowYPF[ni]</td>
    <td>$rowYPF[ud]</td>
    <td>$rowYPF[ed]</td>
    $tdConciliado</tr>";
    unset($rowYPF);
}
if(!isset($clase)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
