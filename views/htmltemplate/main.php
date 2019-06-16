<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<? if ($title): ?>
		<title><?php echo strip_tags($title) ?> - <?php echo RR_PROJECT_NAME ?></title>
	<? else: ?>
		<title><?php echo RR_PROJECT_NAME ?>: Internets + Digital Fabrication = Win</title>
	<? endif ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="Zach Hoeken / Justin Nesselrotte">
	<link rel="icon" href="/favicon.gif" type="image/gif">

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<link href="/bootstrap/2.3.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="/bootstrap/2.3.0/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="/css/botqueue.css?version=3" rel="stylesheet">

	<!-- Le jquery -->
	<script src="/js/jquery-1.11.0.min.js"></script>
	<script src="/js/jquery-ui-1.10.3/ui/minified/jquery-ui.min.js"></script>
	<script language="javascript" type="text/javascript" src="/js/jquery.imagesloaded.min.js"></script>
	<script language="javascript" type="text/javascript" src="/js/flot-0.7/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="/js/flot-0.7/jquery.flot.selection.js"></script>
	<script language="javascript" type="text/javascript" src="/js/jquery.flot.tooltip.min.js"></script>

	<!-- Backbone -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.7.0/underscore.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone-min.js" type="text/javascript"></script>

	<? if (defined('GOOGLE_ANALYTICS_TRACKING_CODE')): ?>
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS_TRACKING_CODE ?>']);
			_gaq.push(['_setDomainName', "<?php echo SITE_HOSTNAME ?>"]);
			_gaq.push(['_setAllowLinker', true]);
			_gaq.push(['_trackPageview']);

			(function () {
				var ga = document.createElement('script');
				ga.type = 'text/javascript';
				ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(ga, s);
			})();
		</script>
	<? endif ?>
	<? if (!empty(Controller::$rssFeeds)): ?>
		<? foreach (Controller::$rssFeeds AS $feed): ?>
			<link rel="alternate" type="application/rss+xml" title="<?php echo RR_PROJECT_NAME ?> - <?php echo $feed['title'] ?>"
				  href="<?php echo $feed['url'] ?>"/>
		<? endforeach ?>
	<? endif ?>

</head>
<body class="preview" data-spy="scroll" data-target=".subnav" data-offset="50">
<div class="container">

	<?php echo Controller::byName('htmltemplate')->renderView('menubar', array('area' => $area)); ?>

	<section id="content" style="margin-top:60px">
		<? if ($title): ?>
			<div class="page-header">
				<h1><?php echo $title ?></h1>
			</div>
		<? endif ?>

		<div class="row">
			<div class="span12">
				<?php echo $content ?>
			</div>
		</div>

		<br/><br/>

		<div class="alert alert-info">
			<strong>Hey You!</strong> If you run into any problems, please <a
				href="https://github.com/Hoektronics/BotQueue/issues/new">report a bug</a>. Make sure to include the
			<strong>bumblebee/info.log</strong> file if it is client-related.
		</div>

	</section>

	<footer>
		<div class="row">
			<div class="span6">
				<h3>Connect</h3>
				<a href="http://www.hoektronics.com">Blog</a><br/>
				<a href="https://twitter.com/hoeken">Twitter</a><br/>
				<a href="irc://irc.freenode.net/botqueue">Freenode #BotQueue</a><br/>
				<a href="https://groups.google.com/d/forum/botqueue">Google Group</a><br/>
			</div>
			<div class="span6">
				<h3>Info</h3>
				Made by Zach Hoeken and Justin Nesselrotte. (<a href="/about">about</a>)<br/>
				Software licensed under the <a href="http://www.gnu.org/copyleft/gpl.html">GPL v3.0</a>. Code at <a
					href="https://github.com/Hoektronics/BotQueue">GitHub</a>.<br/>
				&copy; <?php echo date("Y") ?> <a href="http://www.hoektronics.com"><?php echo COMPANY_NAME ?></a>. Powered by <a
					href="http://www.botqueue.com">BotQueue</a>.<br/>
				Page generated in <?php echo round(microtime(true) - START_TIME, 3) ?> seconds.
			</div>
		</div>
		<br/>
	</footer>

</div>

<? if (!empty(Controller::$scriptContents)): ?>
	<? foreach (Controller::$scriptContents AS $id => $content): ?>
		<script type="<?php echo Controller::$scriptTypes[$id] ?>" id="<?php echo $id ?>">
			<?php echo $content ?>
		</script>
	<? endforeach ?>
<? endif ?>
<? if (!empty(Controller::$scripts)): ?>
	<? foreach (Controller::$scripts AS $src): ?>
		<script src="<?php echo $src ?>"></script>
	<? endforeach ?>
<? endif ?>
<script src="/js/botqueue.js"></script>
<script src="/bootstrap/2.3.0/js/bootstrap.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
</body>
</html>
