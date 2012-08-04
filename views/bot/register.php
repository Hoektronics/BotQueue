<form method="post" autocomplete="off" action="/bot/register">
	<? if (!empty($errors)): ?>
		<div class="BaseError">There were errors :(</div>
	<? endif ?>
	<table>
		<tr>
			<td>Name</td>
			<td><input type="text" name="name" value="<?=$name?>"></td>
		</tr>
		<? if ($errors['name']): ?>
			<tr>
				<td class="2"><span class="FormError"><?=$errors['name']?></span></td>
			</tr>
		<? endif ?>
		<tr>
			<td>Bot Model</td>
			<td><?= Controller::byName('form')->renderView('selectfield', array('options' => array(
				'MakerBot Replicator',
				'MakerBot Thing-o-matic',
				'Ultimaker',
				'Rostock',
				'RepRap Mendel Variant',
				'Printrbot',
				'Other'
			)))?></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Register Bot"></td>
		</tr>
	</table>
</form>
