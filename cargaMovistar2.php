<?php
$nivelRequerido = 3;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$modifica = false;
$fechaFactura = false;
setlocale(LC_ALL, 'es_ES');
if(isset($_GET['id'])){
	// carga los datos para modificar
	$modifica=true;
	// levanto los datos de la factura
	$sqlFactura = "SELECT numeroFactura, periodo, idCliente FROM `movistar.facturasrecibidas` WHERE idFacturaRecibida=$_GET[id]";
	$resultFactura = $mysqli->query($sqlFactura);
	$factura = $resultFactura->fetch_assoc();
	
	// levanto datos del cliente
	$sqlCliente = "SELECT nombre, idCliente, variosClientes, codigo FROM `movistar.clientes` WHERE idCliente='$factura[idCliente]'";
	$resultCliente =  $mysqli->query($sqlCliente);
	$cliente = $resultCliente->fetch_assoc();
	
	
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Carga Factura | Movistar</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  </head>

  <body>
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php') ?>
	<div class="container">
	<!-- Main hero unit for a primary marketing message or call to action -->
		<div class='row'>
			<div class="col-md-12">
				<h2><?php if($modifica&&!$fechaFactura)echo"Modificar"; elseif(isset($fechaFactura))echo "Revisa";else echo "Nueva";?> factura</h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well'<?php if(isset($fechaFactura))echo" disabled='true'"?>>
				<?php if($modifica)echo"<input type='hidden' name='idFactura' id='idFactura' value='$_GET[id]'/>";?>
				<div class='row'>
					<div class='col-md-5'>
							<div class="form-group">  
							<label class="control-label" for="idCliente">Buscar por teléfono</label>
							<div class="controls">
							<div class="input-group"> 
								<input type='text' name='idCliente' id='idCliente' required="required" pattern="[0-9]{2,}" maxlength="11" class='input-sm ui-widget' <?php if($modifica)echo "value='$cliente[idCliente]'"?>/>&nbsp; &nbsp;<input type='text' name='celular' id='celular' maxlength="5" class='input-sm ui-widget prepend' disabled='disabled' <?php if($modifica)echo "value='$cliente[codigo]'"?>/><span class="input-group-addon" id='addCliente'><a href='cargaCliente.php'>+</a></span>
							</div></div></div>
							<div class="form-group">  
							<label class="control-label" for="cliente">Cliente</label>
							<div class="controls">
								<input type='text' name='cliente' id='cliente' maxlength="255" class='input ui-widget' disabled='disabled' <?php if($modifica)echo "value='$cliente[nombre]'"?>/>
							</div></div>
							<div class="form-group">  
							<label for='periodo' class="control-label">Período</label>
							<div class="controls">  
								<select name='periodo' id='periodo' data-plus-as-tab='true'>
									<?php
									for ($i = 2; $i >= 0; $i--) {
										$mes = date("F Y", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
										$valorMes = date("Ym", mktime(0, 0, 0, date("m")-$i, date("d"),   date("Y")));
										echo "<option value='$valorMes' ".(((!$modifica&&$i==1)||$modifica&&$valorMes==$factura['periodo'])?' selected="selected"':'').">$mes</option>";
									}?>
								</select>
							</div></div>
							<div class="form-group" id='numeroFactura'>
							<label for='numeroFactura' class="control-label">Factura</label>
							<div class="controls">
								<input type='text' name='numeroFactura' id='numeroFactura' class="input" placeholder="Numero..." required="required" pattern="[0-9]{1,}" maxlength="12"<?php if(isset($factura['numeroFactura']))echo" value='$factura[numeroFactura]'";?> data-plus-as-tab='true'/>
							</div></div>
							
							<div class="form-group" id='numeroFactura'>
							<label for='totalFactura' class="control-label">Total</label>
							<div class="controls">
							<div class="input-group"> 
								<input type='text' name='totalFactura' id='totalFactura' class="input-sm" placeholder="$" pattern="[0-9\.]{1,}" maxlength="12" data-plus-as-tab='true'/><input type='text' name='totalCalculado' id='totalCalculado' class="input-sm prepend" disabled='disabled'/><span class="input-group-addon" id='errorFactura'>!</span>
							</div></div></div>
					</div>
					<div class='col-md-6'>
						<table id='ultimasFacturasCliente' class='table'>
						<thead><th>Línea</th><th>Al 27</th><th>IVA 27%</th><th>BB / Datos</th><th>IVA 21%</th><th>Imp. Int.</th><th>Otros</th><th>Total</th></tr></thead>
						<tbody></tbody></table>
						
					</div>
				</div>
				<div class='row col-md-11'>
						<fieldset>
							<table id='facturas' class='table'>
								<thead>
									<tr><th>Celular</th><th colspan='2'>Fijos / Prop.</th><th colspan='2'>Bberry / internet</th><th colspan='2'>Variable</th><th colspan='2'>Uso red</th><th>Imp.Int.</th><th colspan='2'>Mora</th><!--<th>IIBB</th>--><th>Otros</th></tr>
								</thead>
								<tfoot>
									<tr><td colspan='13'></td></tr>
								</tfoot>
								<tbody>
								<tr><td colspan='13'>Buscar cliente por cualquiera de sus números de celular</td></tr>
								</tbody>
							</table>

						</fieldset>
				</div>
				<div class="form-group">
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar' disabled='disabled' style='display:none'><?php if($modifica)echo"Actualizar"; else echo "Cargar";?> &raquo;</button><div id='loader_gif'><img src=''/> Cargando...</div>
				</div></div>
				<div id='msgbox' class='alert alert-success col-md-10' style='display:none'>Factura cargada correctamente</div>
				</form>
			</div>
		</div>
		<div class='row'>
			<div class="col-md-12">
				<h2>Ultimas facturas cargadas</h2>
				<table class='table' id='ultimasFacturas'>
					<thead><tr><th colspan='2'>Cliente</th><th>Factura</th><th>Mes</th><th>al 27%</th><th>IVA</th><th>BB / datos</th><th colspan='2'>Comisión</th><th>IVA 21%</th><th>Imp. Int.</th><th>Otros</th><th>Total</th></tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
        <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script>
		$(document).ready(function() {
			<?php if($modifica){?>
				$.post('func/listaUltimasFacturasCliente.php', { idCliente: <?php echo $cliente['idCliente']?> }, function(data) {
					$('#ultimasFacturasCliente tbody').html(data);
				});
				$.post('func/cargaCelularesCliente.php', { idCliente: <?php echo $cliente['idCliente']?>, idFactura: <?php echo $_GET['id']?> }, function(data) {
					$('#facturas tbody').html(data);
					$("input").each(function() {
						var that = this; // fix a reference to the <input> element selected
						$(this).keyup(function(){
							newSum.call(that); // pass in a context for newsum():
						});
					});
					$('.iva').each(function() {
						var that = this; // fix a reference to the <input> element selected
						$(this).focusout(function(){
							calculaIVA.call(that); // pass in a context for newsum():
						});
					});
					$('.enable').click(function(){
						var myArray = $(this).attr('id').split('_');
						if(myArray[0]=='en'){
							$('#mora_'+myArray[1]).removeAttr('disabled').show();
							$('#iva_4_'+myArray[1]).focus();
						}else if(myArray[0]=='es'){
							$('#otro_'+myArray[1]).removeAttr('disabled').show();
							$('#iva_5_'+myArray[1]).focus();
						}else {
							$('#red_'+myArray[1]).removeAttr('disabled').show();
							$('#iva_3_'+myArray[1]).focus();
						}
						$(this).hide();
					});
					$('.remueve').click(function(){
						var myArray = $(this).attr('id').split('_');
						$('#f'+myArray[1]).hide('slow').remove();							
					});
					$('#totalFactura').val($('#total').val());
				});
			<?php }?>
			$("#idCliente").autocomplete({
				source: "func/buscaClientePorCelular.php",
				minLength: 3,
				select: function( event, ui ) {
					$('msgbox').hide();
					$(this).value=ui.item.label;
					$('#celular').val(ui.item.label);
					$.post('func/buscaClientePorCelular.php', { idCliente: ui.item.value }, function(data) {
						$('#cliente').val(data);
					});
					$.post('func/cargaCelularesCliente.php', { idCliente: ui.item.value }, function(data) {
						$('#facturas tbody').html(data);
						$("input").each(function() {
							var that = this; // fix a reference to the <input> element selected
							$(this).keyup(function(){
								newSum.call(that); // pass in a context for newsum():
							});
						});
						$('.iva').each(function() {
							var that = this; // fix a reference to the <input> element selected
							$(this).focusout(function(){
								calculaIVA.call(that); // pass in a context for newsum():
							});
						});
						$('.enable').click(function(){
							var myArray = $(this).attr('id').split('_');
							if(myArray[0]=='en'){
								$('#mora_'+myArray[1]).removeAttr('disabled').show();
								$('#iva_4_'+myArray[1]).focus();
							}else if(myArray[0]=='es'){
								$('#otro_'+myArray[1]).removeAttr('disabled').show();
								$('#iva_5_'+myArray[1]).focus();
							}else {
								$('#red_'+myArray[1]).removeAttr('disabled').show();
								$('#iva_3_'+myArray[1]).focus();
							}
							$(this).hide();
						});
						$('.remueve').click(function(){
							var myArray = $(this).attr('id').split('_');
							$('#f'+myArray[1]).hide('slow').remove();							
						});
					});
					$.post('func/listaUltimasFacturasCliente.php', { idCliente: ui.item.value }, function(data) {
						$('#ultimasFacturasCliente tbody').html(data);
					});
				}
			});
			$.post('func/listaUltimasFacturas.php', function(data) {
				$('#ultimasFacturas tbody').html(data);
			});
            var opciones= {
                beforeSubmit: mostrarLoader,
                success: mostrarRespuesta,
				url: 'func/cargaNuevaFactura.php',
				type: 'post',
				//clearForm: true,
				resetForm: true
            };
            function mostrarLoader(){
                $('#loader_gif').fadeIn("slow");
            };
            function mostrarRespuesta (responseText){
				if(responseText.search('yes')>0){
					$('msgbox').show('slow');
					$('#facturas tbody').html("<tr><td colspan='13'>Buscar cliente por cualquiera de sus números de celular</td></tr>");
					$('#enviar').hide('slow');
					$.post('func/listaUltimasFacturas.php', function(data) {
						$('#ultimasFacturas tbody').html(data);
					});
					$('#idCliente').focus();
				}
                $("#loader_gif").fadeOut("slow");
				$('.span').html('0.00');
			}
			$('#nuevaOP').ajaxForm(opciones); 
			$('#idCliente').focus();
			$("#facturas input").keypress(function(event) {
				if ( event.which == 13 ) {
					event.preventDefault();
				}
				alert(event.which);
			});
		});
		function newSum() {
			var sum = 0;
			//var thisRow = $(this).closest('tbody');

			$('#facturas tbody').find('td:not(.x) input:text').each( function(){
				if(!isNaN(this.value)){
					sum += parseFloat(this.value);}
			});
			$('#facturas tbody').find('.int').each( function(){
				if(!isNaN(this.value)){
					sum += parseFloat(this.value);}
			});
			var myArray = $(this).attr('id').split('_');
			var impInt1 = $('#iva_1_'+myArray[2]).val() * 0.051667;
			var impInt0 = $('#iva_0_'+myArray[2]).val() * 0.01;
			var impInt2 = $('#iva_2_'+myArray[2]).val() * 0.051667;
			var impInt3 = $('#iva_3_'+myArray[2]).val() * 0.051667;
			$('#impInt_'+myArray[2]).val(impInt0 + impInt1 + impInt2 + impInt3);
			$('#spanImpInt_'+myArray[2]).html((impInt0 + impInt1 + impInt2 + impInt3).toFixed(2));
			
			$('#totalCalculado').val(sum);
			var totalFactura = $('#totalFactura').val();
			if(Math.abs(totalFactura - sum) > .5){
				$('#errorFactura').addClass('label alert-danger').html("!");
				$('#enviar').attr('disabled','disabled').hide();
			}else if(sum > 1) {
				$('#errorFactura').removeClass('label alert-success').html("OK");
				$('#enviar').removeAttr('disabled').show();
			}
		}
		function calculaIVA(){
			var myArray = $(this).attr('id').split('_');
			var thisIva = '#IVA'+myArray[1]+'_'+myArray[2];
			if(myArray[1]==0||myArray[1]==4)
				var alicuota= .21;
			else
				var alicuota= .27;
			var IVA = $(this).val() * alicuota;
			$(thisIva).html(IVA.toFixed(2));
		}
		</script>
	</body>
</html>