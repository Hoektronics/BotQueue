<?
/**
 * @package botqueue_job
 * @var array $jobs
 * @var Job $job
 * @var int $size
 * @var S3File $webcam
 */
?>
<div class="row">
  <? foreach ($jobs AS $row): ?>
    <? $job = $row['Job'] ?>
    <? $webcam = $row['S3File'] ?>
    <div class="span3 bot_thumbnail bot_thumbnail_<?=$size?>" style="margin-bottom: 30px; overflow: hidden;">
      <div class="bot_thumbnail_content">
        <div class="bot_thumbnail_stretcher"></div>
        <div class="real_bot_thumbnail_content">
          <a href="<?=$job->getUrl()?>">
    	      <img src="<?=$webcam->getRealUrl()?>">
      	  </a>
      	</div>
    	</div>
  	</div>
  <? endforeach ?>
</div>