<? if (!empty($bots)): ?>
	<table>
		<tr>
			<th>Name</th>
		</tr>
		<? foreach ($bots AS $row): ?>
			<? $b = $row['Bot'] ?>
			<tr>
				<td><?=$b->getLink()?></td>
			</tr>
		<?endforeach?>
	</table>
<? else: ?>
	<b>No bots.</b>
<? endif ?>