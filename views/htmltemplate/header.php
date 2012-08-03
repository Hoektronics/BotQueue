<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<? if ($title): ?>
			<title><?=strip_tags($title)?> // <?=RR_PROJECT_NAME?></title>
		<? else: ?>
			<title><?=RR_PROJECT_NAME?></title>
		<? endif ?>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Language" content="EN" />
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="author" content="BotQueue" />
		<meta name="distribution" content="IU" />

		<!-- load the rest of our JS for BotQueue. -->
		<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="/js/botqueue.js?ver=1"></script>
		
		<link rel="stylesheet" type="text/css" href="/css/style.css?version=1" />

		<? if (!empty(Controller::$rssFeeds)): ?>
			<? foreach (Controller::$rssFeeds AS $feed): ?>
				<link rel="alternate" type="application/rss+xml" title="<?= RR_PROJECT_NAME ?> - <?=$feed['title']?>" href="<?=$feed['url']?>" />
			<? endforeach ?>
		<? endif ?>
		
		<? if (IS_DEV_SITE): ?>
			<style>
				body
				{
					background-image: url("/img/devsite.png");
					background-repeat: repeat-all;
				}
			</style>
		<? endif ?>
	  <?= Controller::$content_for["head"] ?>
	</head>
	<body <?= Controller::$content_for["body"] ?>>
		<div id="ajax_loader" style="display:none;">
			<img src="/img/ajax-loader.gif" alt="Loading..."/>
		</div>
		<div id="main">