<?php
$nivelRequerido = 2;
include('include/inicia.php');
$titulo='Ventas en Servicompras por empleado';
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      th, caption{
          text-align:right;
          border-bottom: 1px solid #000;
      }
      .neg, .nc{
          color: #f00;
      }
      #listadoVentas{
          display:none;
          font-size: .8em;
      }
      #soloImpresora{
          display:none;
      }
      
      .noche{
          color:#000099;
      }
      .cebra{
          background-color:#fcf8e3;
      }
      #aclaracion { display: block; }
      .oculto{
          color:#fcf8e3;
          font-size:.6em;
      }
      sup {
          color: #f00;
      }
      .ampliar, .ampliar2 {
          cursor: pointer;
      }
      .ampliar{
          width: 110px;
      }
      .columna{
          width: 94px;
          word-wrap: normal;
      }
      .sel {
          font-weight: bold;
          background-color: #66cccc;
      }
      #recibo{
          width:50%; 
          text-align: right; 
          line-height: 3em;
      }
      #recibo tbody tr{
          border-bottom: 1px dotted #000;
      }
      @media print {
            #soloImpresora{
                page-break-before: always;
                display: block;
            }
            #recibo{
                padding-top:3em;
            }
          footer {
              display:none;
          }
          .ni{
              display:none;
          }
          body{
              padding-top:0;
          }
          @page {size: landscape A4; margin: .5cm }
          .container{width: 100%}
          #ventasMensuales{
              font-size: 1em;
          }
          #listadoVentas{
            font-size: .8em;
        }
        #ranking tbody {
            font-size: .8em;
        }
        #aclaracion {
            font-size:.9em;
            display:block;
        }
        h2 {
            font-size: 1.5em;
        }
        input {
            border: none;
        }
      }
    </style>
  </head>

  <body>
	<?php include('include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
			<div class="col-md-12">
                <h2>Ventas Servicompras mensual y promedio  <select name='periodo' id='periodo' class=''>
                            <?php
                            for ($i = 12; $i >= 0; $i--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
                                $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
                                echo "<option value='$valorMes' ".((($i==1&&!isset($_GET['m']))||(isset($_GET['m'])&&$_GET['m']==$valorMes))?' selected="selected"':'').">$mes</option>";
                            }?>
                        </select></h2>
				<div id='ranking'>
                     <table id='ventasMensuales'></table>
				</div>
			</div>
			
		</div><br/>&nbsp;
            <div id='listadoVentas' class='panel panel-default'>
                <div class="panel-heading">
                    <h3 class="panel-title">Detalle ventas mensuales<?php if(!isset($_GET['sinNoche']))echo " con TURNO NOCHE PONDERADO x $ponderaNoche"?></h3><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </div>
                <div class="col-md-12">
                <table style='width:100%;text-align:right'><thead><tr><th></th><th>Precio</th><th>Comision unitaria</th><th>Cant.</th><th>Comisiona</th><th>Vendedores</th><th>Turno</th></tr></thead><tbody></tbody></table>
                </div>
            </div>
            <div id='aclaracion' class='row'>
                <div class='col-md-12'><h2>Forma de cálculo</h2>
                    <p>El sistema calcula la facturación por turnos desde 12 meses atrás, ignorando cigarrillos y tarjetas de celulares, también lo facturado o remitido como vencidos, calculando productos vendidos <b>a precio actual</b>.</p>
                    <p>En cada turno asigna a cada uno de los vendedores involucrados el 50% de lo facturado y con eso se obtiene lo facturado mensualmente por cada vendedor.</p>
                    <p>Para cada vendedor calcula el promedio de lo facturado en los últimos 11 meses, mientras que para la dotación completa calcula el promedio en esos mismos 11 meses.</p>
                    <p>El premio es <?php echo $multiplica?> precio actual del F10 de litro <b>($<?php echo sprintf("%.2f",$_SESSION['comision'])." * $multiplica = \$$comision"?>)</b> por cada <b><?php echo $comisionPorTantoPorciento?>%</b> que se supere el promedio individual o general.</p>
                    <p><sup>1</sup> La Variación personal (<b>&Delta; personal</b>) es el porcentaje que varió cada uno respecto a su promedio de ventas de los últimos <?php echo $cuantosMeses?> meses.<br/>
                    <sup>2</sup> La Variación sobre el promedio grupal (<b>&Delta; grupal</b>) es el porcentaje que varió cada uno respecto al promedio total de los últimos <?php echo $cuantosMeses?> meses. Ese promedio se calculó dividiendo el total vendido en cada mes sobre la cantidad de empleados de cada mes.<br/>
                    <sup>3</sup> Si el vendedor no tiene <?php echo $cuantosMeses?> de antigüedad el premio por la variación sobre su promedio personal se ponderará gradualmente hasta alcanzar la misma antigüedad que el resto del plantel.</p>
                    <!--<p>Las ventas hechas por "cubre vacaciones" no se suman en el total de las ventas, pero tampoco achican el promedio al dividir por un empleado mas. Si se toman en cuenta para comisionar solo la mitad de los artículos vendidos al empleado, de lo contrario favorecería a los que trabajen con "cubre vacaciones".</p>-->
                    <p>No se incluyen los artículos facturados a la cuenta Estación de Servicio C0059.</p>
                </div>
            </div>
        <div class='row' id='soloImpresora' >
            <div class="col-md-12">
                <table id='recibo'><thead><tr><th>Recibo <?php echo date("d/m/Y")?></th><th>Premio</th><th style="width:44%">Firma</th></tr></thead><tbody></tbody></table>
            </div>
        </div>
                     

        <?php include ('include/footer.php')?>

    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
            $(document).ready(function() {
                $('#ventasMensuales').html("<tr><td align=center colspan=10><br><img src='img/ajax-loader.gif'/></td></tr>").fadeIn();
                $.post('func/ajaxBuscaVentasServicompras.php',{ mes: <?php echo date((isset($_GET['m'])?$_GET['m']:"Ym"), strtotime("-1 month")); if(!isset($_GET['sinNoche']))echo ", noche:1 "?>}, function( data ) {
                    $( "#ranking tbody" ).html( data );
                    $('.ampliar2').click(function(){
                        $('.ampliar, .ampliar2').removeClass('sel');
                        $(this).addClass('sel');
                        $.post('func/listaVentasShop.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde2:$(this).attr('id') },function(data) {
                            $("#listadoVentas").show();
                            $("#listadoVentas tbody").html(data).fadeIn();
                        });
                    });
                    $('.ampliar').click(function(){
                        $('.ampliar, .ampliar2').removeClass('sel');
                        $(this).addClass('sel');
                        $.post('func/listaVentasShop.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde:$(this).attr('id') },function(data) {
                            $("#listadoVentas").show();
                            $( "#listadoVentas tbody" ).html( data );
                        });
                    });
                });
                 $.post('func/reciboVentasLubricantes.php', function( data ) {
                    $( "#recibo tbody" ).html( data );
                 });
                
                $('#periodo').change(function(){
                   $('#ventasMensuales').html("<tr><td align=center colspan=10><br><img src='img/ajax-loader.gif'/></td></tr>").fadeIn();
                   $.post('func/ajaxBuscaVentasServicompras.php', { mes: $(this).val()<?php if(!isset($_GET['sinNoche']))echo ", noche:1  "?>}, function( data ) {
                        $( "#ventasMensuales" ).html( data );
                        $('.ampliar2').click(function(){
                            $('.ampliar, .ampliar2').removeClass('sel');
                            $(this).addClass('sel');
                            $.post('func/listaVentasShop.php', { mes: $(this).val()<?php if(!isset($_GET['sinNoche']))echo ", noche:1 "?>, desde2:$(this).attr('id') },function(data) {
                                $("#listadoVentas").show();
                                $( "#listadoVentas tbody" ).html( data ).fadeIn();
                            });
                        });
                        $('.ampliar').click(function(){
                            $('.ampliar, .ampliar2').removeClass('sel');
                            $(this).addClass('sel');
                            $.post('func/listaVentasShop.php', { <?php if(!isset($_GET['sinNoche']))echo " noche:1 , "?>desde:$(this).attr('id') },function(data) {
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
