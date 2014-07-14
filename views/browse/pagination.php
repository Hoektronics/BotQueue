<?
/**
 * @package botqueue_browse
 * @var int $total
 * @var int $per_page
 * @var int $prev_page
 * @var string $base_url
 * @var int $min_page
 * @var int $max_page
 * @var int $page
 * @var int $next_page
 * @var string $fragment
 */
?>
<?  if ($total > $per_page): ?>
	<div class="pagination">
		<ul>
		<? if ($prev_page > 0): ?>
			<li><a href="<?=$base_url?>/page:<?=$prev_page?><?=$fragment?>">&laquo; prev</a></li>
		<? endif ?>
		<? for ($i=$min_page; $i<$max_page+1; $i++): ?>
			<? if ($i == $page): ?>
				<li class="active"><a href="<?=$base_url?>/page:<?=$i?><?=$fragment?>"><?=$i?></a></li>
			<? else: ?>
				<li><a href="<?=$base_url?>/page:<?=$i?><?=$fragment?>"><?=$i?></a></li>
			<? endif ?>
		<? endfor ?>
		<? if ($next_page <= $max_page): ?>
			<li><a href="<?=$base_url?>/page:<?=$next_page?><?=$fragment?>">next &raquo;</a></li>
		<? endif ?>
	</div>
<? endif ?>
