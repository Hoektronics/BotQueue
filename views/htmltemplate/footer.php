		&copy; <?= date("Y") ?> <?= COMPANY_NAME ?>.
		Generated in <?= round(microtime(true) - START_TIME, 2) ?> seconds.
		
		<?= Controller::$content_for['footer']; ?>
	</body>
</html>