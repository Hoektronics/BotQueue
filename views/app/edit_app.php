<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var Form $form
 * @var array $apps
 * @var OAuthToken $app
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
			<h3>Apps</h3>
			<?php if(!empty($apps)): ?>
				<p>These apps are using this consumer:</p>
				<ul>
					<?php foreach ($apps AS $row): ?>
						<?php $app = $row['OAuthToken'] ?>
						<li><?php echo $app->getLink() ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p>No apps are using this consumer.</p>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>