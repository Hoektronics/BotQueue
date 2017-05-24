<?
/**
 * @package botqueue_browse
 * @var int $total
 * @var int $start
 * @var int $end
 * @var string $word
 */
?>
<? if ($total > 0): ?>
	<h3>Showing <?=$start?> to <?=$end?> of <?= Utility::pluralizeIt($word, $total) ?></h3>
<? endif ?>
