<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if ($driver == 'dummy'): ?>
    <div class="control-group ">
      <label class="control-label" for="idelay"><strong>Delay</strong></label>
      <div class="controls">
        <input type="text" class="input-mini" id="idelay" name="delay" value="<?=$delay?>">
        <span class="muted">(in seconds)</span>
      </div>
    </div>
  <? elseif ($driver == 'printcore'): ?>
    <div class="control-group ">
      <label class="control-label" for="iserial_port"><strong>Serial Port</strong></label>
      <div class="controls">
        <div class="input-append">
          <input type="text" class="input-xlarge" id="iserial_port" name="serial_port" value="<?=$serial_port?>">
          <input type="hidden" id="port_id" name="port_id" value="<?=$serial_port_id?>">
          <div class="btn-group">
            <button class="btn dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <? if (!empty($devices->bots->printcoredriver)): ?>
                <? foreach ($devices->bots->printcoredriver AS $idx => $port): ?>
                  <li><a tabindex="-1" href="#" onclick="return set_serialport(this, <?=$idx?>)"><?=$port[0]?></a></li>
                  <input type="hidden" id="port_id_<?=$idx?>" value="<?=$port[2]?>">
                <? endforeach ?>
              <? endif ?>
            </ul>
          </div>
        </div>
        <p class="help-block">Name of the serial port to connect to.</p>
      </div>
    </div>
    <div class="control-group ">
      <label class="control-label" for="ibaudrate"><strong>Baudrate</strong></label>
      <div class="controls">
        <div class="input-append">
          <input type="text" class="input-small" id="ibaudrate" name="baudrate" value="<?=$baudrate?>">
          <div class="btn-group">
            <button class="btn dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <? foreach ($baudrates AS $rate): ?>
                <li><a tabindex="-1" href="#" onclick="return set_baudrate(this)"><?=$rate?></a></li>
              <? endforeach ?>
            </ul>
          </div>
        </div>
        <span class="muted">(likely 115200 or 250000)</span>
      </div>
    </div>
  <? endif ?>

  <div class="control-group ">
    <label class="control-label" for="webcam_device"><strong>Webcam Device</strong></label>
    <div class="controls">
      <input type="text" class="input-xlarge" id="webcam_device" name="webcam_device" value="<?=$webcam_device?>">
      <p class="help-block">Click on an image below to select your webcam or enter it manually.</p>
    </div>
  </div>

  <? if (is_object($devices)): ?>
    <? if (!empty($devices->camera_files)): ?>
      <div class="row">
        <? foreach ($devices->camera_files AS $idx => $file_id): ?>
          <? $s3 = new S3File($file_id); ?>
          <div class="span3 webcam_preview" onclick="set_webcam(this)">
            <span class="webcam_name"><?=$devices->cameras[$idx]?></span>
            <img src="<?=$s3->getRealUrl()?>">
          </div>
        <? endforeach ?>
      </div>
    <? endif ?>
  <? endif ?>
  
  <script>
    function set_serialport(ele, idx)
    {
      $('#iserial_port').val($(ele).html())
      $('#port_id').val($('#port_id_' + idx).val());
      
      return false;
    }

    function set_baudrate(ele)
    {
      $('#ibaudrate').val($(ele).html());
      
      return false;
    }
    
    function set_webcam(ele)
    {
      $('#webcam_device').val($(ele).find('span.webcam_name').html());
    }
  </script>
<? endif ?>