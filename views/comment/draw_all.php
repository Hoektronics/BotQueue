<? if (!empty($comments)): ?>
  <? foreach ($comments AS $row): ?>
    <? $c = $row['Comment'] ?>
    <? $u = $row['User'] ?>
    <div id="comment_<?=$c->id?>" class="comment_data">
      <div class="comment_meta">
        <h4><?=$u->getLink()?></h4>
        <h4 class="muted"><?= Utility::formatDatetime($c->get('comment_date'))?></h4>
      </div>
      <div class="comment_body"><?= Utility::cleanAndPretty($c->get('comment'))?></div>
    </div>
  <? endforeach ?>
<? endif ?>