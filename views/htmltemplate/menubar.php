<section id="navbar">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container" style="width: auto;">
				<div class="pull-right">
					<ul class="nav navbar pull-left">
						<li>
							<a href="/notifications">
								<div id="notification-icon" class="notification<?=($notifications > 0 ? ' active' : '')  ?>">
									<?=$notifications?>
								</div>
							</a>
						</li>
					</ul>
					<button type="button"  class="btn btn-navbar" data-toggle="collapse" data-target="#menu-bar">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<a class="brand" href="/"><?= RR_PROJECT_NAME ?></a>

				<div id="menu-bar" class="nav-collapse collapse">
					<ul class="nav">
						<li class="<?= ($area == 'dashboard') ? 'active' : '' ?>"><a href="/">Dashboard</a></li>
						<li class="<?= ($area == 'create') ? 'active' : '' ?> dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Actions<b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="/upload">Create Job</a></li>
								<li><a href="/bot/register">Register Bot</a></li>
								<li><a href="/queue/create">Create Queue</a></li>
							</ul>
						</li>
						<li class="<?= ($area == 'bots') ? 'active' : '' ?>"><a href="/bots">Bots</a></li>
						<li class="<?= ($area == 'queues') ? 'active' : '' ?>"><a href="/queues">Queues</a></li>
						<li class="<?= ($area == 'jobs') ? 'active' : '' ?>"><a href="/jobs">Jobs</a></li>
						<li class="<?= ($area == 'app') ? 'active' : '' ?>"><a href="/apps">App</a></li>
						<li class="<?= ($area == 'slicers') ? 'active' : '' ?>"><a href="/slicers">Slicers</a></li>
						<li class="<?= ($area == 'stats') ? 'active' : '' ?>"><a href="/stats">Stats</a></li>
						<li class="<?= ($area == 'help') ? 'active' : '' ?>"><a href="/help">Help</a></li>
						<? if (User::isAdmin()): ?>
							<li class="<?= ($area == 'admin') ? 'active' : '' ?> dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin<b
										class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="/admin">Admin Panel</a></li>
									<li><a href="/bots/live">Live view</a></li>
								</ul>
							</li>
						<? endif ?>
					</ul>
					<ul class="nav pull-right">
						<li class="divider-vertical"></li>
						<? if (User::isLoggedIn()): ?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle"
								   data-toggle="dropdown">Hello, <?= User::$me->getName() ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="/preferences">Preferences</a></li>
									<li class="divider"></li>
									<li><a href="/logout">Log Out</a></li>
								</ul>
							</li>
						<? else: ?>
							<li>
								<a href="/login"
								   style="padding-left: 17px; background: transparent url('/img/lock_icon.png') no-repeat 0px center;">Log
									in</a>
							</li>
							<li><a href="/register">Sign up</a></li>
						<? endif ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>