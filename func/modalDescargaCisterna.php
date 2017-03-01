<div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title" id="myModalLabel">Descarga de camión</h4>
  </div>
  <form class="form-horizontal" role="form" id='formDescarga' name='formDescarga' action='/func/asignaTanques2.php' method='post'>
  <?php if(isset($_GET['noOp'])){
    echo "<input type='hidden' name='noOp' value='1'/>";
  }?>
  <div class="modal-body">
          <input type="hidden" id="idOrden" name='idOrden' value='<?php echo $_GET['op']?>'>
          <div class="form-group">
            <label for="fecha" class="col-sm-3 control-label">Recepción</label>
            <div class="col-sm-5"><div class="input-group">
              <input type="text" class="form-control" id="fecha" name="fecha" required="required" data-date-format="dd/mm/yyyy" data-plus-as-tab='true'><span class="input-group-addon glyphicon glyphicon-calendar"></span>
            </div> </div>
          </div>
          <div class="form-group">
            <label for="remito" class="col-sm-3 control-label">Remito</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="remito1" required="required" data-plus-as-tab='true'>
            </div>
            <div class="col-sm-3">
              <input type="text" class="form-control" name="remito2" required="required" data-plus-as-tab='true'>
            </div>
          </div>
          <div id='descarga'>
          <div class="form-group">
            <label for="inputTq2" class="col-sm-3 control-label">Ultra Diesel</label>
            <div class="col-sm-5 input-group">
              <input type="number" class="form-control litros" name="inputTq2" placeholder="Tanque 2" data-plus-as-tab='true' min='1000' max='20000'><span class="input-group-addon"></span>
              <input type="number" class="form-control litros" name="inputTq6" placeholder="Tanque 6" data-plus-as-tab='true' min='1000' max='40000'>
            </div>
            <label for="yerUD" class="col-sm-3 control-label">YER</label>
            <div class="col-sm-5 input-group">
                <input type="number" class="form-control litros" name="yerUD" placeholder="lts YER" data-plus-as-tab='true' min='0' max='4000' step="any">
            </div>
          </div>
          <div class="form-group">
            <label for="inputNS" class="col-sm-3 control-label">Nafta Super</label>
            <div class="col-sm-5 input-group">
              <input type="number" class="form-control litros" name="inputTq3" placeholder="litros" data-plus-as-tab='true' min='1000' max='20000'><span class="input-group-addon"></span>
              <input type="number" class="form-control litros" name="yerNS" placeholder="lts YER" data-plus-as-tab='true' min='0' max='4000' step="any">
            </div>
          </div>
          <div class="form-group">
            <label for="inputNP" class="col-sm-3 control-label">Nafta Infinia</label>
            <div class="col-sm-5 input-group">
              <input type="number" class="form-control litros" name="inputTq5" placeholder="litros" data-plus-as-tab='true' min='1000' max='10000'><span class="input-group-addon"></span>
              <input type="number" class="form-control litros" name="yerNP" placeholder="lts YER" data-plus-as-tab='true' min='0' max='4000' step="any">
            </div>
          </div>
          <div class="form-group">
            <label for="inputED" class="col-sm-3 control-label">Euro Diesel</label>
            <div class="col-sm-5 input-group">
              <input type="number" class="form-control litros" name="inputTq1" placeholder="Tanque 1" data-plus-as-tab='true' min='1000' max='20000'><span class="input-group-addon"></span>
              <input type="number" class="form-control litros" name="inputTq4" placeholder="Tanque 4" data-plus-as-tab='true' min='1000' max='10000'>
            </div>
            <label for="yerED" class="col-sm-3 control-label">YER</label>
            <div class="col-sm-5 input-group">
                <input type="number" class="form-control litros" name="yerED" placeholder="lts YER" data-plus-as-tab='true' min='0' max='4000' step="any">
            </div>
          </div>
          </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancela</button>
    <button type="submit" class="btn btn-primary" id='graba'>Graba</button>
  </div>
  </form>
</div><!-- /.modal-content -->
