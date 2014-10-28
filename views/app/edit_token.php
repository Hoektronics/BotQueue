<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var Form $form
 * @var array $bots
 * @var Bot $b
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<div class="row">
		<div class="span9">
			<?= $form->render() ?>
		</div>
		<div class="span3">
			<h3>Bots</h3>
			<? if (!empty($bots)): ?>
				<p>These bots are assigned to this app:</p>
				<ul>
					<? foreach ($bots AS $row): ?>
						<? $b = $row['Bot'] ?>
						<li><?= $b->getLink() ?></li>
					<? endforeach ?>
				</ul>
			<? else: ?>
				<p>No bots are assigned to this app.</p>
			<? endif ?>
		</div>
	</div>
<? endif ?>

