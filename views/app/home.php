<div class="row">
	<div class="span6">
		<h2>Bumblebee - Official Client App</h2>
		<p>
			Bumblebee is the software that runs on your computer itself and turns it into a herder of bots.  This application will pull down jobs from BotQueue.com, and direct your 3D printer to create them.  To get nerdy, it is a cross-platform (w/m/l) python app designed to interface with BotQueue.  It is alpha software and currently only has a commandline interface.
		</p>
		<p>
			It is can control the following types of machines:
			<ul>
				<li>Most RepRap Machines (w/ <a href="https://github.com/grbl/grbl/">grbl</a>, <a href="https://github.com/kliment/Sprinter">Sprinter</a>, <a href="https://github.com/ErikZalm/Marlin/">marlin</a>, etc)</li>
			</ul>
		</p>
		<h3>Getting and Using Bumblebee</h3>
		<p>
			Bumblebee is currently under development, and a release will be made soon.  In the meantime, you can get it from the <a href="https://github.com/Hoektronics/BotQueue">BotQueue github repository</a>.  You can find usage instructions on the <a href="/help">help</a> page.
		</p>
	</div>
	<div class="span6">
		<? if (User::isLoggedIn()): ?>
			<h2>Users - Your Authorized Apps</h2>
			<p>
			These are the apps that you have authorized to have access to your account.  If you use multiple computers, the same app may be listed multiple times below.  If you want to remove an app's access to your account, simply click the revoke link.
			</p>
			<? if (!empty($authorized)): ?>
				<table class="table table-striped table-bordered table-condensed">
					<thead>
						<tr>
							<th>Name</th>
							<th>Deactivate</th>
						</tr>
					</thead>
					<tbody>
						<? foreach ($authorized AS $row): ?>
							<? $a = $row['OAuthConsumer'] ?>
							<? $t = $row['OAuthToken'] ?>

							<tr>
								<td><?=$a->getLink()?></td>
								<td><a href="/app/revoke/<?=$t->get('token')?>">revoke</a></td>
							</tr>
						<? endforeach ?>
					</tbody>
				</table>
			<? else: ?>
				<b>No authorized apps found.</b>
			<? endif ?>

			<? if (!empty($apps)): ?>
				<h2>Developers - Your Registered Apps</h2>
				<p>
					If you are a developer, your app will need its own API key.  First you must <a href="/app/register">register one</a>, and then it will be listed below.  Next, you'll want to visit our <a href="/api/v1">API documentation page</a>.
				</p>
				<table class="table table-striped table-bordered table-condensed">
					<thead>
						<tr>
							<th>Name</th>
							<th>Active?</th>
					</tr>
					<tbody>
						<? foreach ($apps AS $row): ?>
							<? $a = $row['OAuthConsumer'] ?>
							<tr>
								<td><?=$a->getLink()?></td>
								<td><?= $a->isActive() ? 'yes' : 'no' ?></td>
							</tr>
						<? endforeach ?>
					</tbody>
				</table>
			<? endif ?>
		<? else: ?>
			<h2>App Management</h2>
			<p>
				You need to login to the site in order to manage your apps.
			</p>
		<? endif ?>
	</div>
</div>