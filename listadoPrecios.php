<?php
//  $nivelRequerido = 5;
include('include/inicia.php');
$titulo = "Listado de precios para góndolas";

if(!isset($_SESSION['selectGrupos'])){
  $sqlGrupos = "select dbo.GruposArticulos.IdGrupoArticulo, dbo.GruposArticulos.Descripcion, count(IdArticulo) as q from dbo.GruposArticulos, dbo.Articulos WHERE dbo.GruposArticulos.activo=1 AND dbo.Articulos.activo=1 AND dbo.GruposArticulos.IdGrupoArticulo=dbo.Articulos.IdGrupoArticulo GROUP BY dbo.GruposArticulos.IdGrupoArticulo, dbo.GruposArticulos.Descripcion ORDER BY dbo.GruposArticulos.Descripcion;";
  $stmt = sqlsrv_query( $mssql, $sqlGrupos);
  $_SESSION['selectGrupos'] = '';
  while($rowCuentas = sqlsrv_fetch_array($stmt)){
    if($rowCuentas['q']>1&&substr($rowCuentas['Descripcion'],0,2)<>"Z "){
    $_SESSION['selectGrupos'].="<option value='$rowCuentas[IdGrupoArticulo]'>$rowCuentas[Descripcion] ($rowCuentas[q])</option>";}
  }
}


if(!isset($_SESSION['selectUbicacion'])||1){
  $sqlGrupos = "select Ubicacion, count(idArticulo) as q from dbo.articulos where Ubicacion<>'' AND Ubicacion<>'0' AND Activo=1 GROUP BY Ubicacion ORDER BY Ubicacion;";
  $stmt = sqlsrv_query( $mssql, $sqlGrupos);
  $_SESSION['selectUbicacion'] = '';
  while($rowCuentas = sqlsrv_fetch_array($stmt)){
    if($rowCuentas['q']>1&&strlen(trim($rowCuentas['Ubicacion']))>3){
    $_SESSION['selectUbicacion'].="<option value='$rowCuentas[Ubicacion]'>$rowCuentas[Ubicacion] ($rowCuentas[q])</option>";}
  }
}

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include ('/include/head.php');?>
    <style type="text/css">
      @media print
      {    
        
     /* html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, tr, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
              margin: 0;
              padding: 0;
              border: 0;
              font-size: 100%;
              font: inherit;
              vertical-align: baseline;
      }*/
           
        
        .no-print, .no-print *
        {
            display: none !important;
        }
        #impresion{
          height:28cm;
          width:13cm;
          font-size: 13pt;
        }
        body {
            font-family: verdana, arial, sans-serif ;
            font-size: 13pt ;
            }
        .table table {border-collapse:collapse; table-layout:fixed; width:310px;}
        .table th,
        .table td {
            padding: 4px 4px 4px 4px ;
            font-size: 13pt;
            height: 18pt;
/*            word-wrap:break-word;*/
            }
        .table th {
            height: 30pt;
            border-bottom: 2px solid #333333 ;
            }
        .table td {
            border-bottom: 1px dotted #999999 ;
            }
        .table tfoot td {
            border-bottom-width: 0px ;
            border-top: 2px solid #333333 ;
            padding-top: 20px ;
            }
      }
    </style>
  </head>
  <body>
    <?php include('include/menuSuperior.php');?>
    <div class="container">
      <div class='row'>
        <h2></h2>
        <div class="col-md-5 no-print">
        <h2>Seleccionar artículos</h2>
        <form name='nuevaOP' id='nuevaOP' class='form-horizontal well'>
        <fieldset>
          <div class="form-group" id='rop'>  
          <label class="control-label" for="numero"><b>Por grupo</b></label>
          <div class="controls">
          <div class="input-group">
            <select name='IdGrupoArticulo' id='IdGrupoArticulo' class='input-sm form-control' placeholder='Filtrar por Grupo'>
              <?php echo $_SESSION['selectGrupos'] ?>
            </select>
            <span class="input-group-addon"><input type="checkbox" value="grupo" name='clientes[1]' id='filtraCuenta' class='radioClientes' checked></span>	
          </div></div></div>
          
          <div class="form-group" id='rop'>  
          <label class="control-label" for="numero"><b>Por góndola</b></label>
          <div class="controls">
          <div class="input-group">
            <select name='ubicacion' id='ubicacion' class='input-sm form-control' placeholder='Filtrar por Góndola'>
              <?php echo $_SESSION['selectUbicacion'] ?>
            </select>
            <span class="input-group-addon"><input type="checkbox" value="ubicacion" name='clientes[2]' id='filtraCuenta' class='radioClientes' checked></span>
          </div></div></div>
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
      <div class="col-md-7">
        <h2 class="no-print">Listado precios</h2>
        <div style='' id='impresion'>
        <table id='libroDiarioTransporte' class='table' style='text-align:left'>
        </table>
        </div>
      </div>
    </div>
    <?php include ('include/footer.php')?>
  </div> <!-- /container -->
  <?php include('include/termina.php');?>
  <script>
    $(document).ready(function() {
      $('#botonEnvio').fadeIn();
    

      //asignamos el plugin ajaxForm al formulario myForm y le pasamos las opciones
      // $('#enviarDeposito').ajaxForm(opciones2) ; 
      // $('#nuevaOP').ajaxForm(opciones) ; 
      
      $('#enviar').click(function() {
        var opciones= {
          beforeSubmit: mostrarLoaderTransporte, //funcion que se ejecuta antes de enviar el form
          success: mostrarRespuesta, //funcion que se ejecuta una vez enviado el formulario
          url:       'func/listaPreciosGondola.php', // override for form's 'action' attribute 
          type:      'post'       // 'get' or 'post', override for form's 'method' attribute 
        };
        $('#nuevaOP').ajaxForm(opciones);    
      });

        //lugar donde defino las funciones que utilizo dentro de "opciones"
      function mostrarLoaderTransporte(){
        $('#botonEnvio').hide();
        $('#botonEnviando').show();
        $('#libroDiarioTransporte').html("<center><img src='img/ajax-loader.gif'/></center>").fadeIn();
      };
      function mostrarRespuesta(responseText){
        $('#botonEnviando').hide();
        $('#botonEnvio').fadeIn();
        $('#libroDiarioTransporte').html(responseText).slideDown('slow');
      }
  });
  </script>
</body>
</html>
