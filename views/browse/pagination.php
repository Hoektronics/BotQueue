<?  if ($total > $per_page): ?>
	<div class="pagination">
		<? if ($prev_page > 0): ?>
			<a href="<?=$base_url?>/page:<?=$prev_page?><?=$fragment?>">&laquo; prev</a>
		<? endif ?>
		
		<? if ($next_page <= $max_page): ?>
			<a href="<?=$base_url?>/page:<?=$next_page?><?=$fragment?>">next &raquo;</a>
		<? endif ?>
	</div>
<? endif ?>
