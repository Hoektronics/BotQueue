<a href="/api/v1/register">Register a new application / API key.</a><br/>

<? if (!empty($apps)): ?>
	<h2>Your Registered Apps</h2>
	<table>
		<tr>
			<th>Name</th>
			<th>Active?</th>
		</tr>
		<? foreach ($apps AS $row): ?>
			<? $a = $row['OAuthConsumer'] ?>
			<tr>
				<td><?=$a->getLink()?></td>
				<td><?= $a->isActive() ? 'yes' : 'no' ?></td>
			</tr>
		<? endforeach ?>
	</table>
<? endif ?>

<h2>Your Authorized Apps</h2>
<? if (!empty($authorized)): ?>
	<table>
		<tr>
			<th>Name</th>
			<th>Deactivate</th>
		</tr>
		<? foreach ($authorized AS $row): ?>
			<? $a = $row['OAuthConsumer'] ?>
			<? $t = $row['OAuthToken'] ?>

			<tr>
				<td><?=$a->getLink()?></td>
				<td><a href="/api/v1/revoke_">
			</tr>
		<? endforeach ?>
	</table>
<? else: ?>
	<b>No authorized apps found.</b>
<? endif ?>