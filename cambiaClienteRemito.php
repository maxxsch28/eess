<?php
$nivelRequerido = 5;
include('include/inicia.php');
$sqlClientes = "SELECT IdCliente, Codigo, RazonSocial, Identificador, EmiteRemito, IdPeriodicidadFacturacionRemito, IdClienteAsociado FROM dbo.clientes WHERE IdPeriodicidadFacturacionRemito IS NOT NULL AND EmiteRemito<>0 AND Activo=1 ORDER BY RazonSocial, Identificador";
$stmt = sqlsrv_query( $mssql, $sqlClientes);

if(!isset($_SESSION['clientesRemitos'])){
  $_SESSION['clientesRemitos']='';
  $_SESSION['clientesRemitosInternos']='';
  while($rowCuentas = sqlsrv_fetch_array($stmt)){
    if($rowCuentas['IdPeriodicidadFacturacionRemito']==3||$rowCuentas['IdPeriodicidadFacturacionRemito']==12||$rowCuentas['IdClienteAsociado']!=NULL){
      $_SESSION['clientesRemitosInternos'].="<option value='$rowCuentas[IdCliente]'>$rowCuentas[Codigo] - ".(($rowCuentas['Identificador']<>"")?"<b>$rowCuentas[Identificador]</b> / ".substr($rowCuentas['RazonSocial'],0,15):"<b>$rowCuentas[RazonSocial]</b>")."</option>";
      
    } else {
      $_SESSION['clientesRemitos'].="<option value='$rowCuentas[IdCliente]'>$rowCuentas[Codigo] - ".(($rowCuentas['Identificador']<>"")?"<b>$rowCuentas[Identificador]</b> / $rowCuentas[RazonSocial]":"<b>$rowCuentas[RazonSocial]</b>")."</option>";
    }
  }
}



?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Remitos Municipalidad</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  </head>
  <body>
	<?php include('include/menuSuperior.php');?>
	<div class="container">
          <div class='row'>
            <div class="col-md-6">
              <h2>Acomoda remitos Municipalidad</h2>
              <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
              <fieldset>
              <div class="form-group">  
                <label class="control-label" for="rangoFechas">Rango de fechas</label>
                <div class="controls">
                <div class="input-group" id='rop'>
                  <input type='text' name='rangoInicio' id='rangoInicio' class="input-sm form-control" value="01/01/<?php echo date("Y")?>" data-date-format="mm/dd/yy"  data-plus-as-tab='true'/>
                  <span class="input-group-addon">a</span>
                  <input type='text' name='rangoFin' id='rangoFin' class="input-sm form-control"  value="12/31/<?php echo date("Y")?>" data-date-format="mm/dd/yy" data-plus-as-tab='true'/>
                  <span class="input-group-addon presetAnio btn" id="1000">TODOS</span>
                  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y', strtotime("-1 year"))?>"><?php echo date('Y', strtotime("-1 year"))?></span>
                  <span class="input-group-addon presetAnio btn" id="<?php echo date('Y')?>"><?php echo date('Y')?></span>
                </div></div></div>
                
                <div class="form-group">  
                <label class="control-label" for="activaCuentas">Cliente</label>
                <div class="controls">
                <div class="input-group" id=''>
                  <select name='idCliente' id='clientes' class='input-sm form-control' placeholder='Filtrar por Cliente'>
                    <option value='' disabled selected>Filtrar por Cliente</option>
                    <?php echo $_SESSION['clientesRemitos'] ?>
                  </select><span class="input-group-addon"><input type="radio" value="clientes" name='clientes' id='filtraCuenta' class='radioClientes' checked></span>	
                </div></div></div>


                <div class="form-group">  
                <label class="control-label" for="activaCuentas">Uso interno</label>
                <div class="controls">
                <div class="input-group" id=''>
                  <select name='idCliente' id='internos' class='input-sm form-control' disabled>
                        <option value='' selected='selected'>Filtrar por Cliente</option>
                              <?php echo $_SESSION['clientesRemitosInternos'] ?>
                        </select><span class="input-group-addon"><input type="radio" value="internos" name='clientes' class='radioClientes' id='filtraCuenta2'></span>	
                      </div></div></div>
    
                      <div class="form-group col-md-5 col-md-offset-1">
                              <div class="controls">
                                      <label class="radio">
                                              <input type="radio" name="idTipoMovimiento" id="idCaja1" value="">Todos
                                      </label>
                                      <label class="radio">
                                              <input type="radio" name="idTipoMovimiento" id="idCaja2" value="REM" checked><span class="label label-warning">REMITOS</span>
                                      </label>
                                      <label class="radio">
                                              <input type="radio" name="idTipoMovimiento" id="idCaja3" value="FAC"><span class="label label-info">FACTURAS</span>
                                      </label>
                      </div></div>

                      <div class="form-group col-md-5 col-md-offset-1">
                              <div class="controls">
                                      <label class="checkbox">
                                              <input type="checkbox" name="seleccionaTodos" id="seleccionaTodos" value="1">Permite seleccionar entre todos los clientes
                                      </label>
                      </div></div>
              </fieldset>
                    
				<div class="form-group" id='botonEnvio'>
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'>Buscar &raquo;</button>
				</div>
                </div>   
                    <div class="form-group" id='botonEnviando' style="display:none">
				<label for='enviandor' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" >Buscando....</button>
				</div>
                </div>
				</form>
			</div>
			<div class="col-md-6">
				<h2>Remitos</h2>
				<div id='respuesta' style='display:none' class='bg-success'>Cambios grabados</div>
				<div style='height:80%;overflow-y: scroll;max-height:600px'>
				<table id='libroDiario' class='table'>
				</table>
				</div>
			</div>
			
		</div>
        <?php include ('include/footer.php')?>

    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
          $(document).ready(function() {
            $('.radioClientes').click(function(){
              if($(this).val()=='clientes'){
                $('#internos').attr('disabled', true);
                $('#clientes').removeAttr('disabled').focus();
              } else {
                $('#clientes').attr('disabled', true);
                $('#internos').removeAttr('disabled').focus();
              }
            });
          $('#botonEnvio').fadeIn();
            
          $('#enviar').click(function() {
              var opciones= {
                  beforeSubmit: mostrarLoader, //funcion que se ejecuta antes de enviar el form
                  success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
                  url:       'func/ajaxListaRemitosCliente.php',         // override for form's 'action' attribute 
                  type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
              };
              $('#nuevaOP').ajaxForm(opciones);    
          });

            //lugar donde defino las funciones que utilizo dentro de "opciones"
          function mostrarLoader(){
              $('#botonEnvio').hide();
              $('#botonEnviando').show();
              $('#libroDiario').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
              
          };//lugar donde defino las funciones que utilizo dentro de "opciones"
          
          $( "#clientes" ).change(function() {
            $('#respuesta').hide('slow');
          });
            
          function mostrarRespuesta(responseText){
              $('#botonEnviando').hide();
              $('#botonEnvio').fadeIn();
              $('#libroDiario').html(responseText);
              $('.trSelect').click(function(){
                var id=$(this).attr('id').split('_');
                $('#id_'+id[1]).prop("checked", !$('#id_'+id[1]).prop("checked"));
              });
              $('#reasigna').click(function() {
                $('#respuesta').hide();
                var chkBoxArray = [];
                $('.idMovimientoFac:checked').each(function() {
                  chkBoxArray.push($(this).val());
                });
                //dump(chkBoxArray);
                //dump($('#nuevoCliente').val());
                if(!isNaN(chkBoxArray[0])){
                  $.get('func/ajaxReasignaRemitosCliente.php?idRemitos='+chkBoxArray+'&nuevoCliente='+($('#nuevoCliente').val()), function(data) {
                    if($.trim(data)==="yes"){
                      $('#respuesta').show('slow');
                      $('#enviar').click();
                    } else {
                      $('#respuesta').hide('slow');
                      alert(data);
                    }
                  });
                } else { alert ('No hay remitos seleccionados.');}
              });
           }
        });

      </script>
  </body>
</html>
