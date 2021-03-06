<?php
/**
 * @package botqueue_job
 * @var StorageInterface $file
 */
?>
<script src="/thingiview/Three.js"></script>
<script src="/thingiview/plane.js"></script>
<script src="/thingiview/model-controls.js"></script>
<script src="/thingiview/thingiview.js"></script>
<script>
  function loadRenderer()
  {
    $('#displayButton').hide();
    $('#renderArea').show();

    thingiurlbase = "/thingiview";
    thingiview = new Thingiview("renderArea");
    thingiview.setBackgroundColor('#eeeeee');
    thingiview.setObjectColor('#00CC00');
    thingiview.initScene();
    thingiview.loadSTL("/passthru:<?php echo $file->id ?>");
  }
</script>
<button id="displayButton" class="btn btn-primary centered"  onclick="loadRenderer()">Load 3D Model Viewer<br/>(<?php echo Utility::filesizeFormat($file->get('size')) ?>)</button>
<div id="renderArea" style="width: <?php echo $width ?>; height: <?php echo $height ?>;"></div>