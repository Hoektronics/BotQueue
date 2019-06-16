<?
/**
 * @package botqueue_comment
 * @var array $comments
 * @var Comment $c
 * @var User $u
 */
?>
<? if (!empty($comments)): ?>
  <? foreach ($comments AS $row): ?>
    <? $c = $row['Comment'] ?>
    <? $u = $row['User'] ?>
    <div id="comment_<?php echo $c->id ?>" class="comment_data">
      <div class="comment_meta">
        <h4><?php echo $u->getLink() ?></h4>
        <h4 class="muted"><?php echo Utility::formatDateTime($c->get('comment_date')) ?></h4>
      </div>
      <div class="comment_body"><?php echo Utility::cleanAndPretty($c->get('comment')) ?></div>
    </div>
  <? endforeach ?>
<? else: ?>
  <div class="alert alert-info">
    No comments found.
  </div>
<? endif ?>