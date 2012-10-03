<style>
  div#GCodeStatusDiv, div#GCodeErrorDiv {
    position: absolute;
    width: 200px;
    height: 50px;
    line-height: 50px;
    top: 50%;
    left: 50%;
    margin-left: -100px;
    margin-top: -25px;
    margin-bottom: 0px;
    margin-right: 0px;
    padding: 0px;
    text-align: center;
  }
</style>

<button id="displayButton" class="btn btn-primary centered"  onclick="loadRenderer()">Load GCode Viewer<br/>(<?= Utility::filesizeFormat($file->get('size'))?>)</button>
<div id="renderArea" style="width: <?=$width?>; height: <?=$height?>; display: none;"></div>
<div id="GCodeErrorDiv" class="alert alert-error" style="display: none"></div>
<div id="GCodeStatusDiv" class="alert alert-success" style="display: none"></div>

<script src="/gcode-viewer/lib/Three.js"></script>
<script src="/gcode-viewer/lib/bootstrap-modal.js"></script>
<script src="/gcode-viewer/lib/modernizr.custom.93389.js"></script>
<script src="/gcode-viewer/lib/sugar-1.2.4.min.js"></script>
<script src="/gcode-viewer/gcode-parser.js"></script>
<script src="/gcode-viewer/gcode-model.js"></script>
<script src="/gcode-viewer/renderer.js"></script>
<script src="/gcode-viewer/ui.js"></script>

<script>
  function loadRenderer()
  {
    $('#displayButton').hide();
    $('#renderArea').show();
    
    initializeGCodeViewer("/passthru:<?=$file->id?>");
  }
</script>

<!-- 'About' dialog'-->
<div class="modal fade" id="aboutModal" style="display: none">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>About GCode Viewer</h3>
  </div>
  <div class="modal-body">
    <p>This is a viewer for <a href="http://en.wikipedia.org/wiki/G-code" target="_new">GCode</a>
    files, which contain commands sent to a CNC machine such as a
    <a href="http://reprap.org/" target="_blank">RepRap</a> or
    <a href="http://www.makerbot.com/" target="_blank">MakerBot</a> 3D printer.</p>

    <p>This viewer shows the operations the machine will take.</p>

    <p>Drag the mouse to rotate the model. Hold down 'S' to zoom.</p>

    <p>To view your own model, drag a gcode file from your desktop and drop it in this window.</p>

    <p>To learn more, read the <a href="http://joewalnes.com/2012/04/01/a-3d-webgl-gcode-viewer-for-understanding-3d-printers/" target="_new">blog post</a>.</p>
  </div>
  <div class="modal-footer">
    <a class="btn btn-primary" data-dismiss="modal">OK</a>
  </div>
</div>