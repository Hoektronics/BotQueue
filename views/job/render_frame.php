<?
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var StorageInterface $file
 * @var int $width
 * @var int $height
 */
?>
<html>
  <head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <link href="/bootstrap/2.1.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        margin: 0px;
        padding: 0px;
        background: #eee;
      }
      
      button.centered {
        position: absolute;
        width: 200px;
        height: 80px;
        top: 50%;
        left: 50%;
        margin-left: -100px;
        margin-top: -40px;
      }
      
      div#GCodeStatusDiv, div#GCodeErrorDiv {
        position: absolute;
        width: 200px;
        height: 50px;
        line-height: 50px;
        top: 50%;
        left: 50%;
        margin: -25px 0px 0px -100px;
        padding: 0px;
        text-align: center;
        z-index: 1000;
      }
    </style>
  </head>
  <body>
    <div id="GCodeErrorDiv" class="alert alert-error" style="display: none"></div>
    <div id="GCodeStatusDiv" class="alert alert-success" style="display: none"></div>
    <? if (!$megaerror): ?>
      <? if ($file->isGCode()): ?>
        <?= Controller::byName('job')->renderView('render_gcode', array('file' => $file, 'width' => $width, 'height' => $height))?>
      <? elseif ($file->is3DModel()): ?>
        <?= Controller::byName('job')->renderView('render_model', array('file' => $file, 'width' => $width, 'height' => $height))?>
      <? else: ?>
        <h3>Error: I do not know how to render <?=$file->getLink()?></h3>
      <? endif ?>
    <? else: ?>
      <h3><?=$megaerror?></h3>
    <? endif ?>
  </body>
</html>