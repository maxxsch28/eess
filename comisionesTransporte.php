<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Lista comisiones mensuales";
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      #comisionesSocios td, #comisionesSocios th{
          text-align: left;
      }
    @page {
        size: A4;
        margin: 0;
    }
    @media print {
        width: 21cm;
        min-height: 29.7cm;
        .page {
            margin: 0;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }

    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
		<div class='row'>
			<div class="col-md-12">
                <form class='form-horizontal'>
                    <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
                    <input type="hidden" name='muestraComprimido' value='0' id='muestraComprimido'/>
                    <div class="form-group">
                        <label for='periodo' class="control-label">Comisiones sobre viajes de: <select name='periodo' id='periodo'>
                            <?php 
                            
                            echo "<option value='".date('Y')."' >".date('Y')."</option>";
                            echo "<option value='".(date('Y')-1)."' >".(date('Y')-1)."</option>";

                            for ($abc = 12; $abc >= 0; $abc--) {
                                $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, 1, date("Y")));
                                $valorMes = date("Y-m", mktime(0, 0, 0, date("m")-$abc, 1, date("Y")));
                                if(mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y"))>=mktime(0,0,0,7,1,2015))
                                    echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                            }?>
                            </select></label><div style='float:right'><div id='comprimir' class='btn btn-success no2'>Ver comprimido</div>
                            <select name='filtroTipoViaje' id='filtroTipoViaje' class='btn btn-danger'>
                                <option value='0' selected="selected">Todos los viajes</option>
                                <?php foreach($_SESSION['transporte_tipos_comisiones'] as $key => $nombre){
                                    echo "<option value='$key'>$nombre</option>";
                                }?>
                                </select>
                                
                        </div>
                        </div>
                </form>
				<table class='table table-striped table-condensed' id='comisionesSocios'>
					<thead><tr><th class='nombre no2'>Socio</th>
					<th >Origen -> Destino</th>
					<th >Importe</th>
					<th>Tipo Comisión</th>
                    <th >Cliente</th>
					</tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
      
        <?php include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {
      $('#comisionesSocios tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupComisionesTransporte.php', { mes: $('#periodo').val()}, function(data) {
          $('#comisionesSocios tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
        });
        $('#periodo').change(function(){
          $('#comisionesSocios tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
            $.post('func/setupComisionesTransporte.php', { mes: $(this).val(), soloExternos: $('#filtroTipoViaje').val() }, function(data) {
              $('#comisionesSocios tbody').html(data);
              if($('#muestraComprimido').val() == 1){
                $('#comprimir').click();
              }
            });
          });
      $('#comprimir').click(function(){
        if ( $('.viaje').is(":visible") === true ) {
          $( ".viaje" ).hide();
          $('.comisionEncabezado').removeClass('info');
          $('#comisionesSocios').removeClass('table-striped');
          $('#muestraComprimido').val(1);
        } else {
          $( ".viaje" ).show();
          $('.comisionEncabezado').addClass('info');
          $('#comisionesSocios').addClass('table-striped');
          $('#muestraComprimido').val(0);
        }
        //$('.viaje').toggle();
      });
      $('#filtroTipoViaje').change(function(){
        $('#comisionesSocios tbody').html("<tr><td colspan='5'><center><img src='img/ajax-loader.gif'/></center></td></tr>").fadeIn();
        $.post('func/setupComisionesTransporte.php', { mes: $('#periodo').val(), soloExternos: $(this).val() }, function(data) {
          $('#comisionesSocios tbody').html(data);
          if($('#muestraComprimido').val() == 1){
            $('#comprimir').click();
          }
        });
        //$('.viaje').toggle();
      });
    });
  </script>
</body>
</html>
