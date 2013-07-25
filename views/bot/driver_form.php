<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if (!is_object($devices)): ?>
		<div class="alert alert-error">
			<strong>Warning</strong> The client has not reported the results of the device scan yet, wait a moment and reload to see the device scan results for easier configuration of serial ports, webcams, etc.
		</div>
  <? endif ?>
  <? if ($driver == 'dummy'): ?>
    <div class="control-group ">
      <label class="control-label" for="idelay"><strong>Delay</strong></label>
      <div class="controls">
        <input type="text" class="input-mini" id="idelay" name="delay" value="<?=$delay?>">
        <span class="muted">(in seconds between gcode commands)</span>
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

  <input type="hidden" id="webcam_id" name="webcam_id" value="<?=$webcam_id?>">

  <? if (is_object($devices)): ?>
    <? if (!empty($devices->camera_files)): ?>
      <div class="control-group ">
        <label class="control-label" for="iwebcam">
          <strong>Webcam Setup</strong><br/>
          <span class="muted">Click on an image to select your webcam or enter it manually below.</span>
        </label>
        <div class="controls">
          <div class="span3 webcam_preview <?= (!$webcam_device) ? 'active' : ''?>" id="webcam_preview_foo" onclick="set_webcam('foo')">
            <input type="hidden" id="webcam_id_foo" value="">
            <input type="hidden" id="webcam_name_foo" value="">
            <input type="hidden" id="webcam_device_foo" value="">
            <span class="webcam_name">No Camera</span>
            <img src="/img/colorbars.gif">
          </div>
          <? foreach ($devices->camera_files AS $idx => $file_id): ?>
            <? $s3 = new S3File($file_id); ?>
            <div class="span3 webcam_preview <?= ($devices->cameras[$idx]->device == $webcam_device) ? 'active' : ''?>" id="webcam_preview_<?=$idx?>" onclick="set_webcam(<?=$idx?>)">
              <input type="hidden" id="webcam_id_<?=$idx?>" value="<?=$devices->cameras[$idx]->id?>">
              <input type="hidden" id="webcam_name_<?=$idx?>" value="<?=$devices->cameras[$idx]->name?>">
              <input type="hidden" id="webcam_device_<?=$idx?>" value="<?=$devices->cameras[$idx]->device?>">
              <span class="webcam_name"><?=$devices->cameras[$idx]->name?></span>
              <img src="<?=$s3->getRealUrl()?>">
            </div>
          <? endforeach ?>
        </div>
      </div>
    <? endif ?>
  <? endif ?>

  <div class="control-group ">
    <label class="control-label" for="webcam_name"><strong>Webcam Name</strong></label>
    <div class="controls">
      <input type="text" class="input-xlarge" id="webcam_name" name="webcam_name" value="<?=$webcam_name?>">
    </div>
  </div>

  <div class="control-group ">
    <label class="control-label" for="webcam_device"><strong>Webcam Device</strong></label>
    <div class="controls">
      <input type="text" class="input-xlarge" id="webcam_device" name="webcam_device" value="<?=$webcam_device?>">
    </div>
  </div>

  <div class="control-group ">
    <label class="control-label" for="webcam_brightness"><strong>Webcam Brightness</strong></label>
    <div class="controls">
      <input type="text" class="input-mini" id="webcam_brightness" name="webcam_brightness" value="<?=$webcam_brightness?>">
      <span class="muted">%</span>
    </div>
  </div>
  
  <div class="control-group ">
    <label class="control-label" for="webcam_contrast"><strong>Webcam Contrast</strong></label>
    <div class="controls">
      <input type="text" class="input-mini" id="webcam_contrast" name="webcam_contrast" value="<?=$webcam_contrast?>">
      <span class="muted">%</span>
    </div>
  </div>
  
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
    
    function set_webcam(id)
    {
      $('#webcam_id').val($('#webcam_id_' + id).val());
      $('#webcam_device').val($('#webcam_device_' + id).val());
      $('#webcam_name').val($('#webcam_name_' + id).val());

      $('div.webcam_preview').removeClass('active');
      $('#webcam_preview_' + id).addClass('active');
    }
  </script>
<? endif ?>