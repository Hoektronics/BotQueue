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
 */
?>
<?
// Display a maximum of 5 to the left and right
$min_page = max($page - 4, 1);
$max_page = min($page + 4, ceil($total / $per_page));
?>
<? if ($total > $per_page): ?>
	<div class="pagination">
		<ul>
			<? if ($page > 1): ?>
				<li><a href="<?php echo $base_url ?>/page:<?php echo $page - 1 ?>">&laquo; prev</a></li>
			<? endif ?>

			<? for ($i = $min_page; $i < $max_page + 1; $i++): ?>
				<? if ($i == $page): ?>
					<li class="active">
				<? else: ?>
					<li>
				<? endif ?>
				<a href="<?php echo $base_url ?>/page:<?php echo $i ?>"><?php echo $i ?></a>
				</li>
			<? endfor ?>

			<? if ($page < $max_page): ?>
				<li><a href="<?php echo $base_url ?>/page:<?php echo $page + 1 ?>">next &raquo;</a></li>
			<? endif ?>
	</div>
<? endif ?>
