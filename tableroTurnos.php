<?php
if(substr($_SERVER['REMOTE_ADDR'], 0, 10)=='192.168.1.'){
    $nivelRequerido=5;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$titulo="Estado tanques | YPF";

// hacer ventas mensuales calculadas por diferencia aforadores ultimo cierre menos ultimo cierre mes anterior y sumar las ventas diarias.


$sqlUltimoUpdate = $mysqli->query("SELECT fecha, tipo FROM ultimaactualizacion order by id desc limit 1");
ChromePhp::log($sqlUltimoUpdate);
$ultimoUpdate = $sqlUltimoUpdate->fetch_array();
$datetime1 = date_create($ultimoUpdate[0]);
$datetime2 = new DateTime("now");
$interval = date_diff($datetime1, $datetime2);
       

$hito1 = "2011-10-02"; // Comienzo
$hito2 = "2017-07-01"; // cambio de turnos
$hito3 = "2017-08-31"; // CREA



// verifico que el historico no esté en sesion
if(!isset($_SESSION['despachosHorariosHistoricos'])||1){
  // saca promedio general desde el día 0 hasta hoy
  // select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$desdeHistorico',getdate()) from dbo.Despachos group by datepart(HOUR, Fecha) order by hora; 
  $sqlDespachosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$hito1',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito1' AND Fecha<'$hito2' group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosHistoricos, __FILE__, __LINE__);

  $despachosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $despachosHorariosHistoricos[$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosHistoricos']=$despachosHorariosHistoricos;
  $sqlLitrosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'$hito1',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito1' AND Fecha<'$hito2'  group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosHistoricos, __FILE__, __LINE__);

  $litrosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $litrosHorariosHistoricos[$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosHistoricos']=$litrosHorariosHistoricos;
}

if(!isset($_SESSION['despachosHorariosNuevoTurno'])||1){
  $sqlDespachosHorariosNuevoTurno = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$hito2',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito2' AND Fecha<'$hito3' group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosNuevoTurno, __FILE__, __LINE__);

  $despachosHorariosNuevoTurno = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $despachosHorariosNuevoTurno[$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosNuevoTurno']=$despachosHorariosNuevoTurno;
  $sqlLitrosHorariosNuevoTurno = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'$hito2',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito2' AND Fecha<'$hito3'  group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosNuevoTurno, __FILE__, __LINE__);

  $litrosHorariosNuevoTurno = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $litrosHorariosNuevoTurno[$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosNuevoTurno']=$litrosHorariosNuevoTurno;
}

if(!isset($_SESSION['despachosHorariosPostCrea'])||1){
  $sqlDespachosHorariosPostCrea = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$hito3',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito3' group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosPostCrea, __FILE__, __LINE__);

  $despachosHorariosPostCrea = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $despachosHorariosPostCrea[$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosPostCrea']=$despachosHorariosPostCrea;
  $sqlLitrosHorariosPostCrea = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'$hito3',getdate()) as q from dbo.Despachos WHERE Fecha>='$hito3' group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosPostCrea, __FILE__, __LINE__);

  $litrosHorariosPostCrea = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $litrosHorariosPostCrea[$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosPostCrea']=$litrosHorariosPostCrea;
}

//ChromePhp::log($_SESSION['despachosHorariosHistoricos']);

//ChromePhp::log(date('G'));
$max1 = max($_SESSION['despachosHorariosHistoricos']);
$max2 = max($_SESSION['despachosHorariosNuevoTurno']);
$max3 = max($_SESSION['despachosHorariosPostCrea']);

$maximo = max($max1, $max2, $max3)+10;


// litros por hora
$sqlLitrosHorariosActuales = "select datepart(HOUR, Fecha) as hora, sum(Cantidad) as q from dbo.Despachos where CONVERT(date, Fecha)=CONVERT(date, Getdate()) group by datepart(HOUR, Fecha) order by hora;";

$stmt = odbc_exec2($mssql, $sqlLitrosHorariosActuales, __FILE__, __LINE__);

while($row = sqlsrv_fetch_array($stmt)){
  if($row['hora']>5)
  $litrosHorariosActuales[$row['hora']]=round($row['q'],1);
}
//ChromePhp::log($litrosHorariosActuales);
// calcula estimacion hora actual
@$litrosHorariosActuales[date('G')] = round($litrosHorariosActuales[date('G')]/date('i')*60,0);
//ChromePhp::log($despachosHorariosActuales[date('G')]);
//ChromePhp::log($litrosHorariosActuales);
//ChromePhp::log(date('G'));
$max1 = max($_SESSION['litrosHorariosHistoricos']);
$max2 = max($_SESSION['litrosHorariosNuevoTurno']);
$max3 = max($_SESSION['litrosHorariosPostCrea']);


$maximo2 = max($max1, $max2, $max3)+100;


$sql="SELECT sum( ns ) , sum( np ) , sum( ud ) , sum( ed ) FROM `ventasDiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) AND MONTH( fecha ) = MONTH( CURDATE( ) ) ";

$sqlMangueras = "SELECT idManguera, IdArticulo FROM dbo.mangueras";
$stmt = odbc_exec2($mssql, $sqlMangueras, __FILE__, __LINE__);

$mangueras = array();
while($manguera = sqlsrv_fetch_array($stmt)){
    $mangueras[$manguera['idManguera']] = $manguera['IdArticulo'];
}





//print_r($estadoComb);
function d($fecha, $incluyeHora=false){
  if($incluyeHora){
    $part = explode(' ', $fecha);
    $dia = explode('-', $part[0]);
    return $dia[2].'/'.$dia[1].' '.$part[1]; 
  } else {
    $part = explode('-', $fecha);	
    return $part[2].'/'.$part[1];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
    <style type="text/css">
        body{
          <?php if(!$_SESSION['esMovil']){?>
          margin: 50px auto;
          <?php } else {?>
          margin: 10px 0;
          <?php }?>
        }
        .OPentregada{
            background-color:#fcf8e3;
        }
        #myModal2 table{
            background-color:#fff;
        }
    </style>
    <?php if(isset($_GET['soloComb'])){?><link href="css/graficobarras.css" rel="stylesheet" type="text/css" media="screen"/><?php }; ?>
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print"/>
  </head>
  <body>
	<?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){include($_SERVER['DOCUMENT_ROOT']."/include/menuSuperior.php");} ?>
	<?php //if(!isset($_GET['soloComb'])){include("include/menuSuperior.php");} ?>
    <div class="container">
      <h1>Desayuno Infinia</h1>
      <p>Ranking mensual de canje de cupones Desayuno, los tres primeros de cada mes están en oscuro y son los ganadores. La cuarta columna de cada mes indica que porcentaje de cupones corresponden a despachos reales (no cargados litros a mano) facturados.</p>
      <div class='ranking'>
        <table id='rankingDesayuno' class='table'><tbody><tr><td>"<center><img src='img/ajax-loader.gif'/></center></td></tr></tbody></table>
      </div>
      <h1>Porcentaje AliBabá</h1>
      <p>Por cada turno se calcula cuantos envases de 1 litro de Elaion se venden respecto a cantidad de despachos.</p>
      <div class='row'>
      </div>
      
      <h1>Ventas Lubricantes</h1>
      <p>Se prorratea los envases de litro vendidos en cada turno entre los vendedores que lo componen y se suman para cada uno, eso se hace mensual y luego se obtiene un promedio de esos últimos 12 meses.</p>
      <p>Se compara la performance de cada vendedor en el último mes contra su propio promedio anual y contra el promedio de la dotación en el último mes.</p>
      <p>Por cada 5% que supere su promedio o el grupal recibirá un premio equivalente al valor actual del Elaion F10 de litro</p>
      <div class='row'>
        <div class="col-md-12">
          <h3>Muestra mes <select name='periodo' id='periodo' class=''>
          <?php
          for ($i = 12; $i >= 0; $i--) {
              $mes = date("F Y", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
              $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
              if($valorMes>'201709'){
                echo "<option value='$valorMes' ".((($i==1&&!isset($_GET['m']))||(isset($_GET['m'])&&$_GET['m']==$valorMes))?' selected="selected"':'').">$mes</option>";
              }
          }?>
          </select></h3>
          <div id='ranking'>
            <table id='ventasMensuales'></table>
          </div>
        </div>
      </div>
      <div class='row'>
      </div>
      <div class='row'>
      </div>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
        $(document).ready(function() {
          $('#fecha').datepicker({autoclose: true});
          $.post("func/ajaxMuestraDesayunosMes.php", function(data){
            $('#rankingDesayuno tbody').html(data);
          });
          $('#ventasMensuales').html("<tr><td align=center colspan=10><br><img src='img/ajax-loader.gif'/></td></tr>").fadeIn();
            $.post('func/ajaxBuscaVentasLubricantes.php',{ mes: <?php echo date((isset($_GET['m'])?$_GET['m']:"Ym"), strtotime("-1 month")); if(!isset($_GET['sinNoche']))echo ", noche:1 "?>}, function( data ) {
                $( "#ranking tbody" ).html( data );
                $('.ampliar2').click(function(){
                    $('.ampliar, .ampliar2').removeClass('sel');
                    $(this).addClass('sel');
                    $.post('func/listaVentasLubricantes.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde2:$(this).attr('id') },function(data) {
                        $("#listadoVentas").show();
                        $("#listadoVentas tbody").html(data).fadeIn();
                    });
                });
                $('.ampliar').click(function(){
                    $('.ampliar, .ampliar2').removeClass('sel');
                    $(this).addClass('sel');
                    $.post('func/listaVentasLubricantes.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde:$(this).attr('id') },function(data) {
                        $("#listadoVentas").show();
                        $( "#listadoVentas tbody" ).html( data );
                    });
                });
            });
            
            $('#periodo').change(function(){
                $('#ventasMensuales').html("<tr><td align=center colspan=10><br><img src='img/ajax-loader.gif'/></td></tr>").fadeIn();
                $.post('func/ajaxBuscaVentasLubricantes.php', { mes: $(this).val()<?php if(!isset($_GET['sinNoche']))echo ", noche:1  "?>}, function( data ) {
                    $( "#ventasMensuales" ).html( data );
                    $('.ampliar2').click(function(){
                        $('.ampliar, .ampliar2').removeClass('sel');
                        $(this).addClass('sel');
                        $.post('func/listaVentasLubricantes.php', { mes: $(this).val()<?php if(!isset($_GET['sinNoche']))echo ", noche:1 "?>, desde2:$(this).attr('id') },function(data) {
                            $("#listadoVentas").show();
                            $( "#listadoVentas tbody" ).html( data ).fadeIn();
                        });
                    });
                    $('.ampliar').click(function(){
                        $('.ampliar, .ampliar2').removeClass('sel');
                        $(this).addClass('sel');
                        $.post('func/listaVentasLubricantes.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde:$(this).attr('id') },function(data) {
                            $("#listadoVentas").show();
                            $( "#listadoVentas tbody" ).html( data );
                        });
                    });
                });
                $.post('func/reciboVentasLubricantes.php', function( data ) {
                    $( "#recibo tbody" ).html( data );
                });
              });

            //lugar donde defino las funciones que utilizo dentro de "opciones"
          function mostrarLoader(){
            $('#enviar').text('Buscando...').addClass('disabled');
            $('#ranking tbody').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
          };
          function mostrarRespuesta(responseText){
            $('#enviar').text('Buscar').removeClass('disabled');
            $('#ranking tbody').html(responseText).slideDown('slow');
            $('#botonEnvio').fadeIn();
          }
        });
	</script>
  </body>
</html>
