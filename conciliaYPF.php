<?php
$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
$titulo = "Conciliar Cuenta Corriente YPF - CaldenOil (conciliatutti)";
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
      html {
    
      }
      @page {
        size: A4;
        margin: 10px 0 10px;
      }
      @media print {
      html, body {
        width: 210mm;
        height: 297mm;
      }
      .mitad{
        
      }
      }
      .flash {
        -moz-animation: flash 1s ease-out;
        -moz-animation-iteration-count: 10;

        -webkit-animation: flash 1s ease-out;
        -webkit-animation-iteration-count: 10;

        -ms-animation: flash 1s ease-out;
        -ms-animation-iteration-count: 10;
      }

      @-webkit-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: none; }
      }

      @-moz-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #ff0000; }
        100% { background-color: #dd0000; }
      }

      @-ms-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: none; }
      }
    </style>
   
  </head>

  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
    <div class="container">
      <div class='row'>
        <form name='nuevaOP' id='nuevaOP' class=''>
          <input type='hidden' name='anio' value='2016'/>
          <div class="col-md-5 mitad">
            <h2>YPF <span class='pull-right' id='sumaYPF'>0</span></h2>
            <div style='height:400px;overflow-y: scroll;'>
            <table id='cuentaYPF' class='table table-condensed'>
            </table>
            </div>
          </div>
          <div class="col-md-1"><div class="form-group" id='botonEnvio'>
            <label for='enviar' class="control-label"></label>
            <div class="controls"> 
              <button class="btn btn-primary btn-lg" id='enviar'>Graba</button>
            </div></div>
          </div>
          <div class="col-md-5 mitad">
            <h2><span id='sumaCalden'>0</span><span class='pull-right'>CaldenOil</span></h2>
            <div style='height:400px;overflow-y: scroll;'>
            <table id='cuentaCalden' class='table table-condensed'>
            </table>
            </div>
          </div>
          </form>
      </div>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
  </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {
      $('#botonEnvio').fadeIn();
      $('#cuentaCalden').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      $('#cuentaYPF').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      var opciones= {
        success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
        url:       'func/ajaxBuscaConciliarMovimientoYPF.php',         // override for form's 'action' attribute 
        dataType: 'json',
        type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
      };
        //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
      $('#nuevaOP').ajaxForm(opciones) ;
      
      function mostrarRespuesta(responseText, statusText, xhr, $form){
        if(responseText.status=='yes'){
          $("input[type=checkbox]:checked").closest('td').html("<td class='mConciliado n"+responseText.idConciliado+"'><span class='label label-info'>"+responseText.idConciliado+"</label></td>");
        }
      }
      
      function recalculate(){
          var sum = 0;
          $("input[type=checkbox]:checked.calden").each(function(){
          if($(this).attr('class')=='calden neg'){
              var resta=-1;
            } else {
              var resta=1;
            }
            sum += resta * parseFloat($(this).attr("rel"));
          });
          var sum = sum.toFixed(2);
          $('#sumaCalden').html(sum);
          if($('#sumaYPF').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled1');
          } else {
            $('#enviar').removeClass('disabled');
          }
      }
      function recalculate2(){
          var sum = 0;
          $("input[type=checkbox]:checked.ypf").each(function(){
            if($(this).attr('class')=='ypf neg'){
              var resta=-1;
            } else {
              var resta=1;
            }
            sum += resta * parseFloat($(this).attr("rel"));
          });
          var sum = sum.toFixed(2);
          $('#sumaYPF').html(sum);
          if($('#sumaYPF').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled1');
          } else {
            $('#enviar').removeClass('disabled');
          }
      }
      
      $.post( "func/ajaxConciliaYPF_YPF.php", <?php if(isset($_GET['nc'])){echo "{soloNoConciliado:  1},";}?> function( data ) {
        $("#cuentaYPF").html( data );

        $("input[type=checkbox].ypf").change(function(){
          recalculate2();
        });
        $.post( "func/ajaxConciliaYPF_Calden.php", <?php if(isset($_GET['nc'])){echo "{soloNoConciliado:  1},";}?> function( data ) {
          $("#cuentaCalden").html( data );
          $('.mConciliado').click(function() {
            $('.mConciliado').removeClass('flash');
            var currentli = $(this).parent().prop('className');
            $('.'+currentli + ' .mConciliado').addClass('flash');
          });
          $("input[type=checkbox].calden").change(function(){
            recalculate();
          });
          var a=0;
         
          $(".noConciliado").each(function() { 
          if(a<1000){
            <?php if(!isset($_GET['nc'])){?>
              $(this).removeClass("alert-danger");
              $(this).addClass("alert-info");
            <?php }?>
            var id = $(this).attr('id');
            $.post("func/ajaxBuscaConciliarMovimientoYPF.php", {id: id}, function (data){
            });
          a++;
          }
          });
        });
      });
      
      $('.mConciliado').click(function() {
        alert('h');
        $('.mConciliado').removeClass('alert-warning');
        var currentli = $(this).parent().prop('className');
        $('.'+currentli + ' .mConciliado').addClass('alert-warning');
      });
    });
  </script>
</body>
</html>
