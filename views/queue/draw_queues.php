<? if (!empty($queues)): ?>
	<table>
		<tr>
			<th>Name</th>
		</tr>
		<? foreach ($queues AS $row): ?>
			<? $q = $row['Queue'] ?>
			<tr>
				<td><?=$q->getLink()?></td>
			</tr>
		<?endforeach?>
	</table>
<? else: ?>
	<b>No queues.</b>
<? endif ?>