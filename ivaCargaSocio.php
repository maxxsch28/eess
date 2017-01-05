<?php
$nivelRequerido = 6;

include('include/inicia.php');

$modifica = false;
$fechaFactura = false;
setlocale(LC_ALL, 'es_ES');
if(isset($_GET['id'])){
	// carga los datos para modificar
	$modifica=true;

	// levanto datos del socio
	$sqlSocio = "SELECT * FROM `iva_socios` WHERE idSocio='$GET[id]'";
	$resultSocio =  $mysqli->query($sqlSocio);
	$socio = $resultSocio->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>IVA  - Socios</title>
    <?php include ('/include/head.php');?>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
  
  </head>

  <body>
	<?php include('include/menuSuperior.php') ?>
	<div class="container">
	<!-- Main hero unit for a primary marketing message or call to action -->
		<div class='row'>
			<div class="col-md-12">
				<h2><?php if($modifica&&!$fechaFactura)echo"<span class='alert-danger'>Modifica socio</span>"; elseif($fechaFactura)echo "Revisa socio";else echo "Nuevo socio";?> </h2>
				<form name='nuevaOP' id='nuevaOP' class='form-horizontal well<?php if($modifica)echo "  alert-danger"?>'<?php if(isset($fechaFactura))echo" disabled='true'"?>>
                <input type='hidden' name='idSocio' id='idSocio' <?php if($modifica)echo "value='$socio[idSocio]'"?>/>
				<div class='row'>
					<div class='col-md-5'>
							<div class="form-group">  
							<label class="control-label" for="busca">Buscar socio a modificar</label>
							<div class="controls">
							<div class="input-group"> 
                               
								<input type='text' name='busca' id='busca' maxlength="11" class='input-sm form-control' <?php if($modifica)echo "value='$socio[codigo]'"?>/>
                                <span class="input-group-addon btn" id='addCliente'>-></span>
                                <input type='text' name='socio' id='socio' maxlength="255" class='input-sm form-control ui-widget' disabled='disabled' <?php if($modifica)echo "value='$socio[razonsocial]'"?>/>
							</div></div></div>
                        
							<div class="form-group" id='numeroFactura'>
							<label for='razonsocial' class="control-label">Razón Social</label>
							<div class="controls">
								<input type='text' name='razonsocial' id='razonsocial' class=" form-control" placeholder="Nombre..." required="required" maxlength="255"<?php if($modifica)echo" value='$socio[razonsocial]'";?> data-plus-as-tab='true'/>
							</div></div>
							
							<div class="form-group">
							<label for='cuit' class="control-label">CUIT</label>
							<div class="controls">
							<div class="input-group"> 
                                <input type='text' name='cuit' id='cuit' class="input-sm form-control" pattern="[0-9]{11}" maxlength="11" data-plus-as-tab='true'/><span class="input-group-addon"><label><input type='radio' name='condicion' id='condicion' value='ri' checked/> RI</label>&nbsp;<label> <input type='radio' name='condicion' id='condicion' value='rm'/>Monotr.</label>&nbsp;<label><input type='radio' name='condicion' id='condicion' value='otro'/>Otro </label></span>
							</div></div></div>
					</div>
					<div class='col-md-6'>
						<table id='ultimasFacturasSocio' class='table'>
                            <thead><th>Fecha</th><th>Razón Social</th><th>Número</th><th>Neto</th><th>IVA 21%</th><th>IVA 27%</th><th>Imp. Int.</th><th>Otros</th><th>Total</th></tr></thead>
						<tbody></tbody></table>
						
					</div>
				</div>
				<div class="form-group">
				<label for='enviar' class="control-label"></label>
				<div class="controls"> 
					<button class="btn btn-primary btn-lg" id='enviar'><?php if($modifica)echo"Actualizar"; else echo "Cargar";?> &raquo;</button><div id='loader_gif'><img src=''/> Cargando...</div>
				</div></div>
				<div id='msgbox' class='alert alert-success col-md-10' style='display:none'>Factura cargada correctamente</div>
				</form>
			</div>
		</div>
        <?php include ('include/footer.php')?>

    </div> <!-- /container -->
	<?php include('include/termina.php');?>
	<script>
		$(document).ready(function() {
			<?php if($modifica){?>
				$.post('func/listaUltimasFacturasSocio.php', { idSocio: <?php echo $socio['idSocio']?> }, function(data) {
					$('#ultimasFacturasSocio tbody').html(data);
				});
				$.post('func/cargaCelularesSocio.php', { idSocio: <?php echo $socio['idSocio']?>, idFactura: <?php echo $_GET['id']?> }, function(data) {
					$('#facturas tbody').html(data);
					$("input").each(function() {
						var that = this; // fix a reference to the <input> element selected
						$(this).keyup(function(){
							newSum.call(that); // pass in a context for newsum():
						});
					});
					$('.remueve').click(function(){
						var myArray = $(this).attr('id').split('_');
						$('#f'+myArray[1]).hide('slow').remove();							
					});
					$('#totalFactura').val($('#total').val());
				});
			<?php }?>
			$("#busca").autocomplete({
				source: "func/buscaSocio.php",
				minLength: 3,
				select: function( event, ui ) {
					$(this).value=ui.item.label;
					$(this).val(ui.item.label);
                    $("#idSocio").value=ui.item.value;
					$("#idSocio").val(ui.item.value);
					$.post('func/buscaSocioPorCelular.php', { idSocio: ui.item.value }, function(data) {
						$('#socio').val(data);
					});
					$.post('func/cargaCelularesSocio.php', { idSocio: ui.item.value }, function(data) {
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
					$.post('func/listaUltimasFacturasSocio.php', { idSocio: ui.item.value }, function(data) {
						$('#ultimasFacturasSocio tbody').html(data);
					});
				}
			});
            var opciones= {
                beforeSubmit: mostrarLoader,
                success: mostrarRespuesta,
				url: 'func/grabaNuevaFactura.php',
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
					$('#facturas tbody').html("<tr><td colspan='13'>Buscar socio por cualquiera de sus números de celular</td></tr>");
					$('#enviar').hide('slow');
					$.post('func/listaUltimasFacturas.php', function(data) {
						$('#ultimasFacturas tbody').html(data);
					});
					$('#celular').focus();
				}
                $("#loader_gif").fadeOut("slow");
				$('.span').html('0.00');
			}
			$('#nuevaOP').ajaxForm(opciones); 
			$('#celular').focus();
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