<form method="post" autocomplete="off" action="/queue/create">
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
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Create"></td>
		</tr>
	</table>
</form>
