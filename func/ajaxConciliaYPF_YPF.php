<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;



$andFecha=(isset($_REQUEST['rangoInicio']))?" AND femision>='$_REQUEST[rangoInicio]' AND femision<='$_REQUEST[rangoFin]'":" AND femision>='$_REQUEST[rangoInicio]'";
// if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
//   $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
//   $andFecha=" AND femision>='$_REQUEST[mes]' AND femision<='$mesFin'";
// }

$andConciliado = (isset($_POST['soloNoConciliado']))?" AND idConciliado=0":"";


$sqlYPF = "SELECT *, str_to_date(femision, '%d/%m/%Y') as femision2, fecha as femision3  FROM ctacte WHERE 1 $andFecha $andConciliado ORDER BY fecha ASC;";

//ChromePhp::log($sqlYPF);

$result = $mysqli3->query($sqlYPF);

$tabla = "";$a=0;$q=0;
echo "<tbody>";
while($rowYPF = $result->fetch_assoc()){
  $negativo = ($rowYPF['Importe']<0||$rowYPF['clase']=='RC')?"":'neg';
  if($rowYPF['idConciliado']==0){
    // aun no conciliado
    $classConciliado='noConciliado';
    
    
    $tdConciliado = "<td class=''><input type='checkbox' name='idypf[]' value='$rowYPF[id]' rel='".abs($rowYPF['Importe'])."' class='ypf $negativo'/> $rowYPF[id]</td>";
  } else {
    $classConciliado="conciliado_$rowYPF[idConciliado]";
    $tdConciliado = "<td class='m$rowYPF[idConciliado]'><span class='label label-info mConciliado'>$rowYPF[idConciliado]</label></td>";
  }
  // reviso si el turno pertenece a caja cerrada o no
  // levanto los precios historicos del dia
  //$anio_mes = ($rowYPF['femision'], "Ym");
  //echo $sqlPreciosHistoricos;
  
  //$caja = ($rowYPF['IdCaja']==1)?'<span class="badge badge-warning">PLAYA</span>':'<span class="badge badge-info">SHOP</span>';
  //15
  $clase = ($rowYPF['Importe']<0||$rowYPF['clase']=='RC')?"success":'danger';
  
  if(strlen($rowYPF['Referencia'])==15){
    // Visa
    $rowYPF['clase']='Visa';
  }
  if($rowYPF['clase']=='RV')$rowYPF['Referencia']=substr($rowYPF['Referencia'],0,-2);
  if(strlen($rowYPF['femision2'])>6){
    $femision3 = explode('-', $rowYPF['femision2']);
  } else {
    $femision3 = explode('-', $rowYPF['fecha']);
  }
  echo "<tr class='alert alert-$clase $classConciliado' id='ypf_$rowYPF[id]'><td>$femision3[2]/$femision3[1]/$femision3[0]</td><td>$rowYPF[clase]</td><td>$rowYPF[Referencia]</td><td>$".number_format(abs($rowYPF['Importe']), 2, ",", ".")."</td><td>$rowYPF[fvto]</td>$tdConciliado</tr>";
}
echo "</tbody>";
if(!isset($clase)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
