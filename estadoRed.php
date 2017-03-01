<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
/*
  estado de todo lo que se pueda detectar desde el servidor
  - Computadoras arriba (SHOP, PLAYA, Maxi)
  - Estado base de datos SQL en server
  - Mysql en server
  - 
*/
  



?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Tablero</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
	  html {
    
          }
          .jqplot-title{
           font-size:2em;   
          }
    </style>
   <link rel="stylesheet" type="text/css" hrf="css/jquery.jqplot.min.css" />
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
	<div class="container">
		<div class='row'>
			<div class="col-md-3">
				<h2>Tablero</h2>
                                <ul>
                                    <li><a class='selector' id='tarjetas'>Tarjetas | mes entero</a> | <a  class='selector' id='tarjetas_d'> | al día</a></li>
                                    <li><a class='selector' id='lubricantes'>Lubricantes (Facturado) | mes entero</a> | <a class='selector' id='lubricantes_d'>al día</a></a></li>
                                    <li><a class='selector' id='lts_lubricantes'>Lubricantes (Litros) | mes entero</a> | <a class='selector' id='lts_lubricantes_d'>al día</a></a></li>
                                    <li><a class='selector' id='combustibles'>Combustibles | mes entero</a> | <a  class='selector' id='combustibles_d'>al día</a></li>
                                </ul>
			</div>
			<div class="col-md-9">
                <div class='row'>
				<div id='resultados'>
				
				</div></div>
                <div class='row'>
				<div id='resultados2'>
				
				</div></div>
                <div class='row' id='explicacion'>
                    <h2>Tarjetas</h2>
                    <p>El gráfico de arriba muestra en celeste las cobranzas con DÉBITO, en naranja las cobranzas con CRÉDITO. Las magnitudes están en el eje izquierdo.<br>
                    La línea es el porcentaje de CRÉDITO sobre el total de ventas con tarjetas.</p>
                    <p>El gráfico de abajo muestra en celeste el total de la facturación sin incluir las comisiones de YPF. En naranja el total de lo cobrado con tarjetas.<br/>La línea es el porcentaje que representan las cobranzas con tarjetas sobre el total de lo facturado.</p>
                </div>
			</div>
			
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>

    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
        <script type="text/javascript" src="js/jquery.jqplot.min.js"></script>
        <script type="text/javascript" src="js/plugins/jqplot.json2.min.js"></script>
       <script type="text/javascript" src="js/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script type="text/javascript" src="js/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
     <script type="text/javascript" src="js/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
          <script type="text/javascript" src="js/plugins/jqplot.dateAxisRenderer.min.js"></script>
     <!--    <script type="text/javascript" src="js/plugins/jqplot.categoryAxisRenderer.min.js"></script>-->
<!--         <script type="text/javascript" src="js/plugins/jqplot.barRenderer.min.js"></script>-->
        <script type="text/javascript" src="js/plugins/jqplot.enhancedLegendRenderer.js"></script>
        <script type="text/javascript" src="js/plugins/jqplot.highlighter.min.js"></script>
        <script type="text/javascript" src="js/plugins/jqplot.cursor.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
                    function ucFirst(string){
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                    $('.selector').click(function(){
                        $('#resultados').html("<center><br><br><br><img src='img/ajax-loader.gif'/></center>").fadeIn();
                        var que = ucFirst($(this).attr('id'));
                        var que2 = $(this).attr('id');
                        var debito = [];
                        var credito = [];
                        var total = [];
                        var elaion = [];
                        var resto = [];
                        var porcentaje = [];
                        var porcentajeSobreFacturacion = [];
                        var porcentajeCreditoSobreFacturacion = [];
                        var totalVentasMes = [];
                        var totalTarjetas = [];
                        var ticks = [];
                        switch(que2){
                            case 'tarjetas':
                                title="Ventas cobradas con tarjetas";
                                var parametros2 = [totalVentasMes, totalTarjetas, porcentajeSobreFacturacion, porcentajeCreditoSobreFacturacion];
                                var parametros = [debito, credito, porcentaje];
                                var labels2 = ['Total', 'Tarjetas', 'Porcentaje', '% Credito'];
                                var labels = ['Débito', 'Crédito', 'Porcentaje'];
                                var eje = '$';
                                $('#resultados2').html("<center><br><br><br><img src='img/ajax-loader.gif'/></center>").fadeIn();
                                break;
                            case 'lubricantes':
                                title="Facturación de lubricantes";
                                var parametros = [elaion, resto, porcentaje ];
                                var labels = ['Elaion', 'Resto', 'Porcentaje'];
                                var eje = '$';
                                break;
                            case 'lts_lubricantes':
                                title="m3 de lubricantes vendidos";
                                var parametros = [elaion, resto, porcentaje ];
                                var labels = ['Elaion', 'Resto', 'Porcentaje'];
                                var eje = 'Metros cúbicos';
                                que = 'Lubricantes';
                                break;
                        };
                        $.ajax({
                            type: "POST",
                            url: "func/muestraVentas"+que+".php",
                            dataType: "json",
                            success: function (data) {
                                $('#resultados').empty();
                                $('#resultados2').empty();
                                //do chart stuff here.
                                
                                for (var i in data) {
                                    if(que2 === 'lts_lubricantes'){
                                        porcentaje.push([i, data[i].lts_porcentaje]);
                                        resto.push([i, (data[i].lts_resto/1000)]);
                                        elaion.push([i, (data[i].lts_elaion/1000)]);
                                    } else if (que2 === 'lubricantes'){
                                        porcentaje.push([i, data[i].porcentaje]);
                                        resto.push([i, data[i].lts_resto]);
                                        elaion.push([i, data[i].lts_elaion]);
                                    } else {
                                        //totalVentasMes.push([i, data[i].totalVentasMes]);
                                        debito.push([i, data[i].debito]);
                                        credito.push([i, data[i].credito]);
                                        porcentaje.push([i, data[i].porcentaje]);
                                        //porcentajeSobreFacturacion.push([i, data[i].porcentajeSobreFacturacion]);
                                        totalTarjetas.push([i, data[i].totalTarjetas]);
                                        totalVentasMes.push([i, data[i].totalVentasMes]);
                                        porcentajeSobreFacturacion.push([i, data[i].porcentajeSobreFacturacion]);
                                        porcentajeCreditoSobreFacturacion.push([i, data[i].porcentajeSobreFacturacion]);
                                    }
                                    ticks.push(i);
                                }
                                

                                var tarjetas = $.jqplot('resultados', parametros, { 
                                    title: title, 
                                    // Series options are specified as an array of objects, one object
                                    // for each series.
                                    legend: {
                                        show: true,
                                        location: 's',
                                        placement: 'outsideGrid',
                                        showSwatches: true,
                                        fontSize: '10pt',
                                        renderer: $.jqplot.EnhancedLegendRenderer,
                                        rendererOptions: {
                                            numberRows: 1
                                        },
                                        labels: labels,
                                        border: '1px solid #abc'
                                    },

                                    series:[{
                                        showHighlight: false,
                                    },{
                                        showHighlight: false,
                                    },{
                                        yaxis: 'y2axis',
                                        showLabel: true,
                                        showHighlight: true,
                                        lineJoin: 'round',
                                        disableStack: true,
                                        fill:false,
                                        renderer: $.jqplot.PieRenderer,
                                        rendererOptions: {
                                            showDataLabels: true
                                        }
                                    }],
                                    highlighter: {
                                        show: true,
                                        sizeAdjust: 7.5
                                    },
                                    cursor: {
                                        show: false
                                    },
                                seriesDefaults: {
                                    enderer: $.jqplot.LineRenderer,
                                    fill: true,
                                    showHighLight:false,
                                    showMarker: true
                                },
                                stackSeries: true,
                                     axes: {
                                        yaxis: {
                                            autoscale:false,
                                            label: eje,
                                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                            min:0, 
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                                        },
                                        y2axis: {
                                            autoscale:true,
                                            label: '%',
                                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                                        },
                                        xaxis: {
                                            renderer: $.jqplot.DateAxisRenderer,
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                            autoscale:true,
                                            label: 'Meses',
                                            ticks: ticks
                                        }
                                    }
                                  });

                                var tarjetas2 = $.jqplot('resultados2', parametros2, { 
                                    //title: title, 
                                    // Series options are specified as an array of objects, one object
                                    // for each series.
                                    legend: {
                                        show: true,
                                        location: 's',
                                        placement: 'outsideGrid',
                                        showSwatches: true,
                                        fontSize: '10pt',
                                        renderer: $.jqplot.EnhancedLegendRenderer,
                                        rendererOptions: {
                                            numberRows: 1
                                        },
                                        labels: labels2,
                                        border: '1px solid #abc'
                                    },

                                    series:[{
                                        showHighlight: false,
                                    },{
                                        showHighlight: false,
                                    },{
                                        yaxis: 'y2axis',
                                        showLabel: true,
                                        showHighlight: true,
                                        lineJoin: 'round',
                                        disableStack: true,
                                        fill:false,
                                        renderer: $.jqplot.PieRenderer,
                                        rendererOptions: {
                                            showDataLabels: true
                                        }
                                    }],
                                    highlighter: {
                                        show: true,
                                        sizeAdjust: 7.5
                                    },
                                    cursor: {
                                        show: false
                                    },
                                seriesDefaults: {
                                    enderer: $.jqplot.LineRenderer,
                                    fill: true,
                                    showHighLight:false,
                                    showMarker: true
                                },
                                stackSeries: true,
                                     axes: {
                                        yaxis: {
                                            autoscale:false,
                                            label: eje,
                                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                            min:0, 
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                                        },
                                        y2axis: {
                                            autoscale:true,
                                            label: '%',
                                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                                        },
                                        xaxis: {
                                            renderer: $.jqplot.DateAxisRenderer,
                                            tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                            autoscale:true,
                                            label: 'Meses',
                                            ticks: ticks
                                        }
                                    }
                                  });
                            }
                        });   //end of ajax call
                    });
		});
		</script>
	</body>
</html>
