<? if ($total > 0): ?>
	<div class="pagination_info">
		Showing <?=$start?> to <?=$end?> of <?= Utility::pluralizeIt($word, $total) ?>
	</div>
<? endif ?>
