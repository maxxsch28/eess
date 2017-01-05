<?php
$nivelRequerido = 2;
include('include/inicia.php');
$titulo = "Conciliar Cuenta Corriente YPF - CaldenOil";
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
    </style>
   
  </head>

  <body>
    <?php include('include/menuSuperior.php');?>
    <div class="container">
      <div class='row'>
        <form name='nuevaOP' id='nuevaOP' class='' style='display:'>
          <input type='hidden' name='anio' value='2016'/>
          <div class="col-md-5 mitad">
            <h2>YPF <span class='pull-right' id='sumaYPF'>0</span></h2>
            <div style='550px;overflow-y: scroll;'>
            <table id='cuentaYPF' class='table table-condensed'>
            </table>
            </div>
          </div>
          <div class="col-md-1"><div class="form-group" id='botonEnvio'>
            <label for='enviar' class="control-label"></label>
            <div class="controls"> 
              <button class="btn btn-primary btn-lg disabled" id='enviar'>Graba</button>
            </div></div>
          </div>
          <div class="col-md-5 mitad">
            <h2><span id='sumaCalden'>0</span><span class='pull-right'>CaldenOil</span></h2>
            <div style='550px;overflow-y: scroll;'>
            <table id='cuentaCalden' class='table table-condensed'>
            </table>
            </div>
          </div>
          </form>
      </div>
    <?php include ('include/footer.php')?>
  </div> <!-- /container -->
  <?php include('include/termina.php');?>
  <script>
    $(document).ready(function() {
      $('#botonEnvio').fadeIn();
      $('#cuentaCalden').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      $('#cuentaYPF').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      
      $("input[type=checkbox]").change(function(){
        recalculate();
      });


      function recalculate(){
          var sum = 0;
          $("input[type=checkbox]:checked.calden").each(function(){
            sum += parseInt($(this).attr("rel"));
          });
          $('#sumaCalden').html(sum);
          if($('#sumaYPF').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled');
          } else {
            $('#enviar').removeClass('disabled');
          }
      }
      function recalculate2(){
          var sum = 0;
          $("input[type=checkbox]:checked.ypf").each(function(){
            sum += parseInt($(this).attr("rel"));
          });
          $('#sumaYPF').html(sum);
          if($('#sumaYPF').html()!=$('#sumaCalden').html()){
            $('#enviar').addClass('disabled');
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
          $("input[type=checkbox].calden").change(function(){
            recalculate();
          });
          var a=0;
         
          $(".noConciliado").each(function() { 
          if(a<10){
            $(this).removeClass("alert-danger");
            $(this).addClass("alert-info");
            var id = $(this).attr('id');
            $.post("func/ajaxBuscaConciliarMovimientoYPF.php", {id: id}, function (data){
              
            });
          a++;
          }
          });
        });
      });
      
    });
  </script>
</body>
</html>
