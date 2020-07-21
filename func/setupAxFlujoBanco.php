<?php
// setupFlujoBanco.php
// Lista los viajes que no se han liquidados a una fecha determinada
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_POST);
 // $array=array();
//$_POST['mes']='201411';

if(isset($_POST['saldoBanco'])&&is_numeric($_POST['saldoBanco'])){
  if((isset($_SESSION['saldoBanco'])&&$_SESSION['saldoBanco']<>$_POST['saldoBanco'])||!isset($_SESSION['saldoBanco'])){
    $_SESSION['saldoBanco']=$_POST['saldoBanco'];
  } 
}
/* tipomovi 1 cheques
 tipomovi 2 depositos 
 chequera 5 transferencias
		 1 al dia
		 2 difereridos	
		 0 extracciones, cambio de eess
 */

 
 
$sqlFlujoBanco = "select fechamovi AS fecha, -1*(importe) AS importe, 'obligaciones' AS que, CAST(numero as varchar(20)) as numero, fecha AS fechaCarga FROM dbo.moctacte WHERE fechamovi>= DATEADD(month,-1,GETDATE()) AND fechamovi<DATEADD(month,+1,GETDATE()) AND conciliado=0 AND tipomovi=1 AND chequera NOT IN (0,5,7) AND numero NOT IN (SELECT cheque FROM [coop].[dbo].[flujoBanco] WHERE cheque IS NOT NULL) UNION 

select vencimien AS fecha, importe, 'cheques' AS que, numero_val AS numero, ingreso AS fechaCarga FROM dbo.cheques WHERE disponible=1  UNION

select fechamovi AS fecha, importe, 'depositos' AS que, CAST(numero as varchar(20)) as numero, fechamovi AS fechaCarga FROM dbo.moctacte WHERE fechamovi>=DATEADD(day,-5,GETDATE()) AND fechamovi<DATEADD(month,+1,GETDATE())  AND conciliado=0 AND comprobant IS NULL AND tipomovi=2 AND numero NOT IN (SELECT deposito FROM [coop].[dbo].[flujoBanco] WHERE deposito IS NOT NULL)  order by fecha asc, numero asc;";
// 26/6/19 Agregado "AND comprobant IS NULL" para que no incluya los ingresos a banco generados por recibos por transferencias recibidas. (esas transferencias ya se incluyen cuando el operador carga el saldo final real)

ChromePhp::log($sqlFlujoBanco);
$stmt = odbc_exec2($mssql2, $sqlFlujoBanco, __LINE__, __FILE__);
$tabla = "";$a=0;
$obligaciones = array();
$depositos = array();
$neto = $neto = $_SESSION['saldoBanco'];

while($fila = sqlsrv_fetch_array($stmt)){
  // tiene que armar un array para obligaciones y depositos por fecha, si la fecha cae sábado o domingo tienen que pasarse al lunes siguiente
  // el sql tiene que buscar mas de un mes y luego el php tiene que ignorar lo que quede por encima de 30 valores del array
  // en la segunda parte tiene que hacer un foreach en el array y  armar la tabla en base a lo que puso.
  // las obligaciones y los depósitos de ldía tienen que tener el check para marcarlos como realizados, en ese caso tienen que desaparecer y salir del cálculo que arrastra al ída siguiente que lo tiene que hacer este script, el principal debe, cada vez que se marca una casilla, pedir grabar ese cheque o depósito como realizado y pedir este de nuevo.
  
  
  // si es depósito y tiene fecha hoy directamente tengo que cambiarle le fecha a mañana y que luego vea si es sábado o no
  if($fila['que']=='depositos'&&$fila['fecha']->format('d/m/Y')==date('d/m/Y')){
    //ChromePhp::log('hola'. $fila['fecha']->format('d/m/Y'));
    //var_dump(($fila['fecha']));
    $fila['fecha'] = date_create_from_format('d/m/Y:H:i:s', date("d/m/Y:H:i:s", strtotime(date('Y-m-d').' + 1 days')));
    
  }
  
  
  if(!isset($fecha)||$fecha<>$fila['fecha']->format('d/m/Y')){
    // no está definida fecha, veo si es sábado y le sumo 2 días y si es domingo 1 días
    // antes que esto veo si la fecha es anterior a hoy la cargo en el "hoy"
    if($fila['fecha']->format('Ymd')<date('Ymd')){
      if(date("N")==6){
        $fecha = date("d/m", strtotime(date('Y-m-d').' + 2 days'));
      } elseif (date("N")==7){
        $fecha = date("d/m", strtotime(date('Y-m-d').' + 1 days'));
      } else {
        $fecha = date('d/m');
      }
    } elseif(date("N", strtotime($fila['fecha']->format('Y-m-d')))==6){
      // sabado
      $fecha = date("d/m", strtotime($fila['fecha']->format('Y-m-d').' + 2 days'));
    } elseif(date("N", strtotime($fila['fecha']->format('Y-m-d')))==7){
      // domingo
      $fecha = date("d/m", strtotime($fila['fecha']->format('Y-m-d').' + 1 days'));
      
      //ChromePhp::log(date("N", strtotime($fila['fecha']->format('Y-m-d'))), $fecha, $fila['numero'], $fila['importe']);
      
    } else {
      $fecha = $fila['fecha']->format('d/m');
    }
  }
  if($fila['que']=='obligaciones'){
    // prueba para ver si es muy lento revisar si el cheque está en Calden en cartera
    $sqlCalden = "SELECT Ubicacion, FechaSalida, TipoSalida FROM dbo.chequesterceros WHERE numero='$fila[numero]' AND idbanco=4;";
    $stmt2 = odbc_exec2($mssql, $sqlCalden, __LINE__, __FILE__);
    $filaCalden = sqlsrv_fetch_array($stmt2);
    // Ubicacion: 1 es en cartera
    // TipoSalida: 2 es en orden de pago
    $estaEnCalden=0;
    if($filaCalden){
      // está en Calden
      $estaEnCalden=1;
    } 
    if($filaCalden[1]>0){
      $estaEnCalden=2;
    }
    
    $obligaciones[$fecha][] = array($fila['numero'], $fila['importe'], $fila['fecha'], $estaEnCalden, $fila['fechaCarga']);
  } elseif($fila['que']=='depositos') {
    $depositos[$fecha][] = array($fila['numero'], $fila['importe'], $fila['fecha'], 0);
  } else {
    //ChromePhp::log($fecha, date("N")$fila['numero'], $fila['importe']);
    $cheques[$fecha][] = array($fila['numero'], $fila['importe'], $fila['fecha'], 0);
  }
}
//ChromePhp::log($cheques);
$j=30;
$trObligaciones = "<tr><th rowspan='2' class='rowspan'>A pagar</th>";
$trNeto = "<tr class='bg-info'><th>Proyeccion</th>";
$trObligaciones2 = "<tr class='detalle'>";
$trDepositos = "<tr><th rowspan='2' class='rowspan'>Depositos y cartera</th>";
$trDepositos2 = "<tr class='detalle'>";
$a=0;
for($i=0;$i<=$j;$i++){
  if(date('N', time()+$i*86400)>5){
    // sabado y domingo
    $j++;$a--;
  } else {
    $d = time()+$i*86400;
    $trObligaciones2.="<td>";
    $trDepositos2.="<td>";
    foreach($obligaciones[date('d/m', $d)] AS $id => $key) {
      if($key[3]==1){
        $class = ' alert-warning';
      } elseif($key[3]==2){
        $class = ' alert-danger';
      } else {
        $class = '';
      }
      $class2 = '';
      if($key[4]->format('Y-m-d')==date('Y-m-d')){
          // emitido hoy
          $class2 = ' alert-success';
        }
      $trObligaciones2 .= "<span class='cheque$class' id='ch_$key[0]'> Nº $key[0], $".number_format(-1*$key[1],2,',','.');
      if($a==0||$a==1){
        // hoy, agrego botón para marcar como pagado
        $trObligaciones2 .= "<span class='glyphicon glyphicon-remove pagado' id='$key[0]' aria-hidden='true' ></span> - <span class='$class2'>".$key[2]->format('d/m')."</span>";
      }
      $trObligaciones2 .= "<br/></span>";
      $obligacionesDia[$i] += -1*$key[1];
    }
    foreach($depositos[date('d/m', $d)] AS $id => $key) {
      $trDepositos2 .= "<span class='deposito' id='ch_$key[0]'>".number_format($key[1],2,',','.'); 
      if($a==0||$a==1){
        // hoy, agrego botón para marcar como pagado
        $trDepositos2 .= "<span class='glyphicon glyphicon-remove pagado2' id='$key[0]' aria-hidden='true' ></span> - ".$key[2]->format('d/m');
      }
      $trDepositos2 .= "<br/></span>";
      $depositosDia[$i] += $key[1];
    }
    foreach($cheques[date('d/m', $d)] AS $id => $key) {
      $trDepositos2.= "Nº $key[0], $".number_format($key[1],2,',','.').'<br/>';
      $depositosDia[$i] += $key[1];
    }
    $trObligaciones2.="</td>";
    $trDepositos2.="</td>";
  }
  $a++;
}
$j=30;
for($i=0;$i<=$j;$i++){
  if(date('N', time()+$i*86400)>5){
    // sabado y domingo
    $j++;$a--;
  } else {
    $trObligaciones .= "<td class='text-danger'><b>".number_format(-1*$obligacionesDia[$i],2,',','.')."</b></td>";
    $trDepositos .= "<td><b>".number_format($depositosDia[$i],2,',','.')."</b></td>";
    $neto = $neto+$depositosDia[$i]-$obligacionesDia[$i];
    $trNeto .= "<td class='neto".(($neto<0)?' text-danger':'')."'><b>".number_format($neto,2,',','.')."</b></td>";
  }
}

// print_r($obligaciones);
$trObligaciones2 .= '</tr>';
$trObligaciones .= '</tr>';
$trDepositos .= '</tr>';
$trDepositos2 .= '</tr>';
$trNeto .= '</tr>';
echo $trNeto.$trObligaciones.$trObligaciones2.$trDepositos.$trDepositos2;
?>
