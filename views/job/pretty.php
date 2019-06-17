<?php
/**
 * @package botqueue_job
 * @var array $jobs
 * @var Job $job
 * @var int $size
 * @var StorageInterface $webcam
 */
?>
<div class="row">
  <?php foreach ($jobs AS $row): ?>
    <?php $job = $row['Job'] ?>
    <?php $webcam = $row['StorageInterface'] ?>
    <div class="span3 bot_thumbnail bot_thumbnail_<?php echo $size ?>" style="margin-bottom: 30px; overflow: hidden;">
      <div class="bot_thumbnail_content">
        <div class="bot_thumbnail_stretcher"></div>
        <div class="real_bot_thumbnail_content">
          <a href="<?php echo $job->getUrl() ?>">
    	      <img src="<?php echo $webcam->getDownloadURL() ?>">
      	  </a>
      	</div>
    	</div>
  	</div>
  <?php endforeach; ?>
</div>