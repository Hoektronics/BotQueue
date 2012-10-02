<div id="viewer" style="width: <?=$width?>; height: <?=$height?>;"></div>

<script src="/thingiview/Three.js"></script>
<script src="/thingiview/plane.js"></script>
<script src="/thingiview/thingiview.js"></script>
<script>
  $(function() {
  thingiurlbase = "/thingiview";
  thingiview = new Thingiview("viewer");
  thingiview.setObjectColor('#C0D8F0');
  thingiview.initScene();
  thingiview.loadSTL("/passthru:<?=$file->id?>");
  });
</script>