<form method="post" action="/bot/register">
	<? if (!empty($errors)): ?>
		<div class="BaseError">There were errors :(</div>
	<? endif ?>
	<table>
		<tr>
			<td>Name</td>
			<td><input type="text" name="name" value="<?=$name?>"></td>
		</tr>
		<tr>
			<td>Queue</td>
			<td><?= Controller::byName('form')->renderView('selectfield', array('options' => $queues))?></td>
		</tr>
		<? if ($errors['name']): ?>
			<tr>
				<td class="2"><span class="FormError"><?=$errors['name']?></span></td>
			</tr>
		<? endif ?>
		<tr>
			<td>Bot Manufacturer</td>
			<td><?= Controller::byName('form')->renderView('selectfield', array('name' => 'manufacturer', 'options' => array(
				'MakerBot',
				'Ultimaker',
				'Printrbot',
				'Self Made (DIY)',
				'Printrbot',
				'Other'
			)))?></td>
		</tr>
		<tr>
			<td>Bot Model</td>
			<td><?= Controller::byName('form')->renderView('selectfield', array('name' => 'model', 'options' => array(
				'Replicator',
				'Thing-o-matic',
				'Ultimaker',
				'Rostock',
				'Darwin Variant',
				'Mendel Variant',
				'Printrbot',
				'Other'
			)))?></td>
		</tr>
		
		<tr>
			<td>Electronics Used</td>
			<td><input type="text" name="electronics"></td>
		</tr>
		<tr>
			<td>Firmware Used</td>
			<td><input type="text" name="firmware"></td>
		</tr>
		<tr>
			<td>Extruder Used</td>
			<td><input type="text" name="extruder"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Register Bot"></td>
		</tr>
	</table>
</form>
