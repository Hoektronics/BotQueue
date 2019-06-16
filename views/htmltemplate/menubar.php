<?php if (!User::isLoggedIn()): ?>
	<a href="https://github.com/Hoektronics/BotQueue">
		<img style="position: absolute; top: 40px; right: 0; border: 0;"
		     src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67"
		     alt="Fork me on GitHub"
		     data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png">
	</a>
<?php endif ?>
<section id="navbar">
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<div class="pull-right">
					<?php if(User::isLoggedIn()): ?>
					<ul class="nav navbar pull-left">
						<li>
							<a href="/notifications">
								<div id="notification-icon"
								     class="notification<?php echo ($notifications > 0 ? ' active' : '') ?>">
									<?php echo $notifications ?>
								</div>
							</a>
						</li>
					</ul>
					<?php endif ?>
					<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#menu-bar">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<a class="brand" style="margin-left:0" href="/"><?php echo RR_PROJECT_NAME ?></a>

				<div id="menu-bar" class="nav-collapse collapse">
					<ul class="nav">
						<li class="<?php echo ($area == 'dashboard') ? 'active' : '' ?>"><a href="/">Dashboard</a></li>
						<li class="<?php echo ($area == 'create') ? 'active' : '' ?> dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Actions<b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="/upload">Create Job</a></li>
								<li><a href="/bot/register">Register Bot</a></li>
								<li><a href="/queue/create">Create Queue</a></li>
							</ul>
						</li>
						<li class="<?php echo ($area == 'bots') ? 'active' : '' ?>"><a href="/bots">Bots</a></li>
						<li class="<?php echo ($area == 'queues') ? 'active' : '' ?>"><a href="/queues">Queues</a></li>
						<li class="<?php echo ($area == 'jobs') ? 'active' : '' ?>"><a href="/jobs">Jobs</a></li>
						<li class="<?php echo ($area == 'app') ? 'active' : '' ?>"><a href="/apps">App</a></li>
						<li class="<?php echo ($area == 'slicers') ? 'active' : '' ?>"><a href="/slicers">Slicers</a></li>
						<li class="<?php echo ($area == 'stats') ? 'active' : '' ?>"><a href="/stats">Stats</a></li>
						<li class="<?php echo ($area == 'help') ? 'active' : '' ?>"><a href="/help">Help</a></li>
						<?php if (User::isAdmin()): ?>
							<li class="<?php echo ($area == 'admin') ? 'active' : '' ?> dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin<b
										class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="/admin">Admin Panel</a></li>
									<li><a href="/bots/live">Live view</a></li>
								</ul>
							</li>
						<?php endif ?>
					</ul>
					<ul class="nav pull-right">
						<li class="divider-vertical"></li>
						<?php if (User::isLoggedIn()): ?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle"
								   data-toggle="dropdown">Hello, <?php echo User::$me->getName() ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="/preferences">Preferences</a></li>
									<li class="divider"></li>
									<li><a href="/logout">Log Out</a></li>
								</ul>
							</li>
						<?php else: ?>
							<li>
								<a href="/login"
								   style="padding-left: 17px; background: transparent url('/img/lock_icon.png') no-repeat 0px center;">Log
									in</a>
							</li>
							<li><a href="/register">Sign up</a></li>
						<?php endif ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>