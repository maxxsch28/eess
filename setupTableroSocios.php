<?php
$nivelRequerido = 3;
include('include/inicia.php');
setlocale(LC_ALL, 'es_ES.utf-8');
$titulo="Tablero flechas Socios";
require_once 'classes/Mobile_Detect.php';
if(!isset($_SESSION['esMovil'])){
    $detect = new Mobile_Detect;
    $_SESSION['esMovil'] = ($detect->isMobile()||$detect->isTablet())?true:false; 
}
// $_SESSION['esMovil']=true;
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

    #socios  {
      border-top: 2px solid #000;
      <?php if(isset($_SESSION['esMovil'])&&$_SESSION['esMovil']){?>
        -webkit-column-count: 1; /* Chrome, Safari, Opera */
        -moz-column-count: 1; /* Firefox */
        column-count: 1;
      <?php } else { ?>
        -webkit-column-count: 4; /* Chrome, Safari, Opera */
        -moz-column-count: 4; /* Firefox */
        column-count: 4;
      <?php } ?>
       clear: right; 
       line-height: 1.2;
       margin-top: 12px;
       padding-top: 4px;
       font-size: 10pt;
    }
    .vencimientos {
      column-count: 2;
      -webkit-column-count: 2; /* Chrome, Safari, Opera */
      -moz-column-count: 2; /* Firefox */
      }
    #socios .tarjeta{
      break-inside: avoid-column;
      page-break-inside: avoid; /* For Firefox. */
      -webkit-column-break-inside: avoid; /* For Chrome & friends. */
      break-inside: avoid; /* For standard browsers like IE. :-) */
    }
    #socios .primero{
      margin-top: -17px;
    }
    .ocultarMenu{
      margin-top:-60px;
    }
    .flecha a{
      color: #fff;
    }
    .affix{
      background-color: #fff;
      z-index: 999;
      margin:-3em 0 2px;
      border-radius:0;
      border-bottom: 1px solid #000;
      padding:3em 0 2px;
      width:71%;
    }
  </style>
  </head>
  <body data-target=".navbar" data-offset="50">
    <?php if(!isset($_SESSION['esMovil'])||!$_SESSION['esMovil']){include('include/menuSuperior.php');} ?>
    <div class="container<?php if((isset($_SESSION['ocultaMenu'])&&$_SESSION['ocultaMenu']==1)||(isset($_COOKIE['ocultaMenu'])&&$_COOKIE['ocultaMenu']==1)){echo " ocultarMenu";}?>"> 
      <div class='row'>
      <div class="<?php if(!isset($_SESSION['esMovil'])||!$_SESSION['esMovil']){echo "col-md-12";}?>">
        <nav class="navbar" data-spy="affix" data-offset-top="100">
        <!--<div class='row' data-spy="affix" data-offset-top="60">-->
          <div>
            <form class='form-horizontal'>
              <input type="hidden" name='muestraSoloExternos' value='0' id='muestraSoloExternos'/>
              <input type="hidden" name='muestraVencimientos' value='0' id='muestraVencimientos'/>
              <label>Tablero posicion viajes</label>
              <div style='float:right'>
                <a href='#flechaC'><div class='btn btn-danger flecha'>C</div></a>&nbsp;<a href='#flechaM'><div class='btn btn-danger flecha'>M</div></a>&nbsp;<a href='#flechaL'><div class='btn btn-danger flecha'>L</div></a>
                <div id='comprimir' class='btn btn-success no2'><?php if((isset($_SESSION['muestraComprimido'])&&$_SESSION['muestraComprimido']==1)||(isset($_COOKIE['muestraComprimido'])&&$_COOKIE['muestraComprimido']==1)){echo "Mostrar vencimientos";} else {echo "Ocultar vencimientos";}?></div>&nbsp;<span class='glyphicon glyphicon-<?php if((isset($_SESSION['ocultaMenu'])&&$_SESSION['ocultaMenu']==1)||(isset($_COOKIE['ocultaMenu'])&&$_COOKIE['ocultaMenu']==1)){echo "save";} else {echo "open";}?>' id='ocultaMenu'></span>
              </div>
            </form>
          </div>
        <!--</div>-->
        </nav>
        <div id='socios' class='container-fluid'>
        </div>
      </div>
      </div>
    </div>
    <div class="modal fade bs-no-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Rechazo viaje</h4>
          </div>
          <div class="modal-body">
            <p>Motivo:<br>
            <ul>
              <li>Cargado</li>
              <li>Taller</li>
              <li>Papeles</li>
              <li>Viaje malo</li>
              <li>Enfermedad</li>
              <li>No contesta</li>
              <li>Otro</li>
              
            </ul>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade bs-si-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Acepta viaje</h4>
          </div>
          <div class="modal-body">
            <p>
            Tipo de viaje:<br/>
            <div class='btn btn-danger flechaCorta'>Corta</div><br>
            <div class='btn btn-danger flechaMedia'>Media</div><br>
            <div class='btn btn-danger flechaLarga'>Larga</div><br>
            </p>
          </div>
        </div>
      </div>
    </div>
    <?php if(!isset($_SESSION['esMovil'])||!$_SESSION['esMovil']||false){//include ('include/footer.php');
    }?>
  </div> <!-- /container -->
  <?php include('include/termina.php');?>
  <script>
  $(document).ready(function() {
    $('#socios').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
    $.post('func/setupChoferesDisponibles.php', { mes: $('#periodo').val()}, function(data) {
      $('#socios').html(data);
       $('.tarjeta').click(function(){
        var tempo = $(this).html();
        var id = $(this).attr('id');
        $('.botoneraViajes').remove();
        $(this).append("<div class='panel-footer botoneraViajes'><span class='btn btn-success contestacion' data-toggle='modal' data-target='.bs-si-modal-sm' id='si_"+id+"'>SI</span>&nbsp;<span class='btn btn-danger contestacion pull-right' data-toggle='modal' data-target='.bs-no-modal-sm' id='no_"+id+"'>NO</span></div>");
        $('.contestacion').click(function(){
          // saco ID
          var tmp = $(this).attr('id').split("_");
          var id = tmp[2];
          // mando indicación de cambio de flecha
        });
      });
      <?php if(isset($_SESSION['muestraComprimido'])&&$_SESSION['muestraComprimido']==1){?>
      $('#comprimir').click();
      <?php }?>
    });
    $('.flecha').click(function(){
      $('.botoneraViajes').remove();
    });
    $('#comprimir').click(function(){
      if($('.vencimientos').is(":visible") === true ) {
        $(".vencimientos" ).hide();
        $('.comisionEncabezado').removeClass('info');
        $('muestraVencimientos').val(1);
        $.post('func/setupChoferesDisponibles.php', { muestraComprimido: 1});
        $('#comprimir').html('Mostrar vencimientos');
      } else {
        $(".vencimientos").show();
        $('.comisionEncabezado').addClass('info');
        $('muestraVencimientos').val(0);
        $.post('func/setupChoferesDisponibles.php', { muestraComprimido: 0});
        $('#comprimir').html('Ocultar vencimientos');
      }
    });
    <?php if((isset($_SESSION['ocultaMenu'])&&$_SESSION['ocultaMenu']==1)||(isset($_COOKIE['ocultaMenu'])&&$_COOKIE['ocultaMenu']==1)){echo '$("#menu").hide();';}?>
    $('#ocultaMenu').click(function(){
      if($('#menu').is(":visible") === true ) {
        $("#menu").hide();
        $(".container").addClass("ocultarMenu");
        $.post('func/setupChoferesDisponibles.php', { ocultaMenu: 1});
        $('#ocultaMenu').removeClass('glyphicon-open');
        $('#ocultaMenu').addClass('glyphicon-save');
      } else {
        $("#menu").show();
        $(".container").removeClass("ocultarMenu");
        $.post('func/setupChoferesDisponibles.php', { ocultaMenu: 0});
        $('#ocultaMenu').removeClass('glyphicon-save');
        $('#ocultaMenu').addClass('glyphicon-open');
      }
    });
   
  });
  </script>
</body>
</html>
