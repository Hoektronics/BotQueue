<html>
  <head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <style>
      body {
        margin: 0px;
        padding: 0px;
      }
    </style>
  </head>
  <body>
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