<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 * @var Form $form
 * @var array $apps
 * @var OAuthToken $app
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
			<h3>Apps</h3>
			<? if(!empty($apps)): ?>
				<p>These apps are using this consumer:</p>
				<ul>
					<? foreach ($apps AS $row): ?>
						<? $app = $row['OAuthToken'] ?>
						<li><?= $app->getLink() ?></li>
					<? endforeach ?>
				</ul>
			<? else: ?>
				<p>No apps are using this consumer.</p>
			<? endif ?>
		</div>
	</div>
<? endif ?>