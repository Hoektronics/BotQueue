<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var Form $form
 * @var array $bots
 * @var Bot $b
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<div class="row">
		<div class="span9">
			<?php echo $form->render() ?>
		</div>
		<div class="span3">
			<h3>Bots</h3>
			<?php if (!empty($bots)): ?>
				<p>These bots are assigned to this app:</p>
				<ul>
					<?php foreach ($bots AS $row): ?>
						<?php $b = $row['Bot'] ?>
						<li><?php echo $b->getLink() ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p>No bots are assigned to this app.</p>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>

