<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$limit=11;
$offset=0;



$andFecha=(isset($_REQUEST['rangoInicio']))?" AND femision>='$_REQUEST[rangoInicio]' AND femision<='$_REQUEST[rangoFin]'":'';
// if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
//   $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
//   $andFecha=" AND femision>='$_REQUEST[mes]' AND femision<='$mesFin'";
// }

$andConciliado = (isset($_POST['soloNoConciliado']))?" AND idConciliado=0":"";


$sqlYPF = "SELECT * FROM `ctacte` WHERE idconciliado>0 ORDER BY fecha asc, idconciliado asc";

//ChromePhp::log($sqlYPF);

$result = $mysqli3->query($sqlYPF);
/*
  Revisa la tabla ctacte, ordenada y agrupada por idConciliado
  Para cada conciliado obtiene los documentos y/o movimientos correspondientes de CaldenOil y los resta a los otros, poniendo por último el número resultante.
*/


$tabla = "";$a=0;$q=0;
echo "<tbody>";
while($rowYPF = $result->fetch_assoc()){
  if(!isset($idConciliado)||$idConciliado<>$rowYPF['idConciliado']){
    if(isset($idConciliado)){
      /* Obtengo los datos de los movimientos de CaldenOil que están imputados a este renglón */
      $sqlConciliacion = "SELECT * FROM conciliacion WHERE idConciliado=$idConciliado";
      
      ChromePhp::log($sqlConciliacion);
      $result3 = $mysqli3->query($sqlConciliacion);
      if($result3){
        while($rowConciliado = $result3->fetch_assoc()){
          $sqlCalden = "";
          switch($rowConciliado['tipoCalden']){
            // visa, cheque, rv, op, faa, nca, epago, ndi, av, nda, banco
            case 'rv':
              $sqlCalden = "SELECT Fecha, IdTipoMovimientoProveedor, CONCAT(PuntoVenta, '-',Numero) as numero, Total FROM dbo.movimientospro WHERE IdTipoMovimientoProveedor IN ('RV', 'VP') AND IdMovimientoPro=$rowConciliado[idCalden]";
              break;
            case 'visa':
              $sqlCalden = "SELECT FechaContable as Fecha, IdTipoMovimientoBancario, Detalle as numero, Importe as Total FROM dbo.MovimientosBancarios WHERE IdTransferenciaBancaria=$rowConciliado[idCalden]";
              break;
            case 'cheque':
              $sqlCalden = "SELECT Fecha, 'Cheque de tercero' as IdTipoMovimientoProveedor, Numero, Importe as Total FROM dbo.ChequesTerceros WHERE IdChequeTercero=$rowConciliado[idCalden]";
              break;
            case 'ndi':

              break;
            case 'op':
              break;
            case 'faa':
              break;
            case 'nca':
              break;
            
            case 'epago':
              break;
            
            case 'banco':
              break;
          
            case 'nda':
              break;
          }
          ChromePhp::log($sqlCalden);
          $stmt = odbc_exec2( $mssql, $sqlCalden, __LINE__, __FILE__);
          while($rowCalden = sqlsrv_fetch_array($stmt)){
            $neto = abs($importe) - abs( $rowCalden['Total']);
            echo "<tr><td>".$rowCalden['Fecha']->format('d/m/Y')."</td><td>$rowCalden[IdTipoMovimientoProveedor]</td><td>$rowCalden[numero]</td><td>$".number_format(abs($rowCalden['Total']), 2, ",", ".")."</td><td>$".number_format(abs($rowCalden['Total']), 2, ",", ".")."</td><td></td></tr>";
          }
          if($neto<>0)echo "<tr><td colspan='6'>$ $neto</td></tr>";
          $a++;
          if($a==100)
          die;
        }
      } else {
        // no tiene movimientos Calden Asociados
        echo "<tr><td colspan='6' class='badge badge-alert'>CONCILIADO CONTRA NADA!</td></tr>";
      }
      
      // break table
      echo "<tr><td colspan='6'></td></tr>";
    }
    $importe = 0;
    $idConciliado = $rowYPF['idConciliado'];
  }
  $negativo = ($rowYPF['Importe']<0||$rowYPF['clase']=='RC')?"":'neg';
  $importe += $rowYPF['Importe'];
  
  $classConciliado="conciliado_$rowYPF[idConciliado]";
  $tdConciliado = "<td class='m$rowYPF[idConciliado]'><span class='label label-info mConciliado'>$rowYPF[idConciliado]</label></td>";
 
  $clase = ($rowYPF['Importe']<0||$rowYPF['clase']=='RC')?"success":'danger';
  
  if(strlen($rowYPF['Referencia'])==15){
    // Visa
    $rowYPF['clase']='Visa';
  }
  if($rowYPF['clase']=='RV')$rowYPF['Referencia']=substr($rowYPF['Referencia'],0,-2);
  /*
  // averiguar por que iba esto o sacarlo
  if(strlen($rowYPF['femision'])>6){
    $femision3 = explode('/', $rowYPF['femision']);
  } else {
    $femision3 = explode('-', $rowYPF['fecha']);
  }
  */
  $femision3 = explode('-', $rowYPF['fecha']);
  echo "<tr class='alert alert-$clase $classConciliado' id='ypf_$rowYPF[id]'><td>$femision3[2]/$femision3[1]/$femision3[0]</td><td>$rowYPF[clase]</td><td>$rowYPF[Referencia]</td><td>$".number_format(abs($rowYPF['Importe']), 2, ",", ".")."</td><td>$".number_format($importe, 2, ",", ".")."</td>$tdConciliado</tr>";

  
  
  
  
}
echo "</tbody>";
if(!isset($clase)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
