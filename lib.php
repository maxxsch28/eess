<?php
// mayorAdelantosFleteros.php
// Muestra mayor adelantos fleteros con detalle que no da Setup-

$nivelRequerido = 2;
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');
if(isset($_GET['gasoil'])){
  $titulo = "Muestra mayores adelantos de gasoil";
  $gasoil = 1;
} else {
  $titulo = "Muestra mayores adelantos fleteros";
  $gasoil = 0;
}

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
      .asiento {
        height: 8em;
      }
      .table th {
        text-align: center;
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
      }
      .flash {
        -moz-animation: flash 1s ease-out;
        -moz-animation-iteration-count: infinite;

        -webkit-animation: flash 1s ease-out;
        -webkit-animation-iteration-count: infinite;

        -ms-animation: flash 1s ease-out;
        -ms-animation-iteration-count: infinite;
      }

      @-webkit-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: #ff0000; }
      }

      @-moz-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #ff0000; }
        100% { background-color: #ddff00; }
      }

      @-ms-keyframes flash {
        0% { background-color: none; }
        50% { background-color: #fbf8b2; }
        100% { background-color: #ff0000; }
      }
    </style>
   
  </head>

  <body>
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/menuSuperior.php');?>
    <div class="container">
    <form name='nuevaOP' id='nuevaOP' class=''>
      <input type='hidden' name='gasoil' value='<?php echo $gasoil?>'/>
      <div class='row'>
        <div class="col-md-12 mitad">
            <h2>Mayor de <b>Adelantos fleteros</b> 
            <span id='refresh' class='pull-right glyphicon glyphicon-refresh gly-spin'></span>&nbsp;
            <select name='periodo' id='periodo' class='input-sm pull-right'>
                <?php 
                for ($abc = 12; $abc >= 0; $abc--) {
                    $mes = date("F Y", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    $valorMes = date("Ym", mktime(0, 0, 0, date("m")-$abc, date("d"), date("Y")));
                    echo "<option value='$valorMes' ".(($abc==1)?' selected="selected"':'').">$mes</option>";
                }?>
                <?php
                echo "<option value='".date("Y")."'>".date("Y")." anual</option><option value='".date("Y",strtotime("-1 year"))."'>".date("Y",strtotime("-1 year"))." anual</option>";
                ?>
            </select>
            </h2>
        </div>
      </div>
      <div class='row'>
            <input type='hidden' name='status' value='<?php echo (isset($_REQUEST['status']))?$_REQUEST['status']:''?>'>
          <div class="col-md-12">
            <div>
                <table id='cuentaSetup' class='table table-condensed'>
                <thead><tr><th>Fecha</th><th>Asiento</th><th>Detalle</th><th  width='10%'>Debe</th><th width='10%' >Haber</th><th width='20%'>Concepto</th></tr></thead>
                <tbody id='cuentaSetupTbody'></tbody>
                </table>
            </div>
          </div>
      </div>
    </form>
    <?php include ($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
  <?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
  <script>
    $(document).ready(function() {
        a=0;
        actualiza();
        
        $('#periodo').change(function(){
            if($('#periodo').val()<10000&&false){
                // anual
                $('#cuentaSetup tbody').empty();
                for(i = 1; i < 13; i++) { 
                    actualiza(false, i);

                }
            } else {
                // mensual
                actualiza(true);
            }

        });
        $('#refresh').click(function(){
            actualiza(true);
        });

        function actualiza(limpia=false, periodo=false) {
            $('#refresh').addClass('gly-spin');
            $("#cuentaSetup").fadeOut(3000);
            if(periodo){
                mes = $('#periodo').val()+periodo;
            } else {
                mes = $('#periodo').val();
            }
            const formatter = new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 2
            })
            $.post('func/setupMuestraCuentaContable.php', { mes: mes, caja:'Setup' }, function(data) {
                if(limpia === true){
                    $('#cuentaSetup tbody').empty();
                }
                var totalD = 0;
                var totalH = 0;
                $.each(data, function(i, item) {
                    totalD = totalD + parseFloat(item.debe);
                    totalH = totalH + parseFloat(item.haber);
                    var debe = formatter.format(item.debe);
                    var haber = formatter.format(item.haber);
                    $('<tr>').append($('<td>').text(item.fecha),$('<td>').text(item.asiento),$('<td>').text(item.detalle),$('<td>').text(debe.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' })),$('<td>').text(haber.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' })),$('<td>').text(item.concepto)).appendTo('#cuentaSetup tbody');
                });
                $('<tr>').append($('<td>').text(""),$('<td>').text(""),$('<td>').text("TOTAL"),$('<td>').text(totalD.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' })),$('<td>').text(totalH.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' })),$('<td>').text("")).appendTo('#cuentaSetup tbody');

                $('#refresh').removeClass('gly-spin');
                $("#cuentaSetup").fadeIn(1000);
            }, "json");
        };

        
    });

      
  </script>
</body>
</html>
