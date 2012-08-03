<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="cufon-active cufon-ready">
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<? if ($title): ?>
			<title><?=strip_tags($title)?> // <?=RR_PROJECT_NAME?></title>
		<? else: ?>
			<title><?=RR_PROJECT_NAME?></title>
		<? endif ?>
		<link href="/css/style.css" rel="stylesheet" type="text/css">
		<link href="/css/navigation.css" rel="stylesheet" type="text/css">
		<link href="/css/modules.css" rel="stylesheet" type="text/css">
		<link href="/css/tms.css" rel="stylesheet" type="text/css">
		<link href="/css/superfish.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="/js/cufon-yui.js"></script>
		<style type="text/css">
			cufon{text-indent:0!important;}@media screen,projection{cufon{display:inline!important;display:inline-block!important;position:relative!important;vertical-align:middle!important;font-size:1px!important;line-height:1px!important;}cufon cufontext{display:-moz-inline-box!important;display:inline-block!important;width:0!important;height:0!important;overflow:hidden!important;text-indent:-10000in!important;}cufon canvas{position:relative!important;}}@media print{cufon{padding:0!important;}cufon canvas{display:none!important;}}
		</style>
		<script type="text/javascript" src="/js/cufon-replace.js"></script>
		<script type="text/javascript" src="/js/Oswald_400.font.js"></script>
		<script type="text/javascript" src="/js/superfish.js"></script>
		<script type="text/javascript" src="/js/jquery.tooltip.js"></script>
		<script type="text/javascript" src="/js/tms-0.3.js"></script>
		<script type="text/javascript" src="/js/tms_presets.js"></script>
		<script type="text/javascript" src="/js/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="/js/scripts.js"></script>
		
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

<!-- Main Container Start -->
<div id="main_container">

<!-- Page Wrapper Start -->
<div id="page_wraper">

<!-- Top Area Start -->
<div id="top_area">

<!-- Logo Start -->
<div class="logo">
<a href="http://www.templatemagician.co/cssStatic/1568/index.html"><img src="/img/logo.png" alt="Logo" width="403" height="108" border="0"></a></div>

<!-- Top RightArea Start -->
<div id="top_right">
<div class="toplinks">
<ul class="menu">
<li><a href="http://www.templatemagician.co/cssStatic/1568/#"><span>Site Map</span></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/#"><span>Search</span></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/#"><span>Faq’s</span></a></li>
</ul>
</div>
<div class="top">
<div class="search">
<div class="form">
<input name="input" type="text" class="inputbox" id="input" style="outline:none" value="search...">
</div>
</div>
</div>
</div>

<!-- Navigation Start -->
<div class="nav">
<div class="inner-nav">
<ul class="sf-menu sf-js-enabled">
<li class="current"><a href="http://www.templatemagician.co/cssStatic/1568/index.html"><cufon class="cufon cufon-canvas" alt="Home" style="width: 44px; height: 18px; "><canvas width="66" height="27" style="width: 66px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>Home</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/aboutus.html"><cufon class="cufon cufon-canvas" alt="about " style="width: 59px; height: 18px; "><canvas width="84" height="27" style="width: 84px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>about </cufontext></cufon><cufon class="cufon cufon-canvas" alt="us" style="width: 23px; height: 18px; "><canvas width="42" height="27" style="width: 42px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>us</cufontext></cufon></a></li>
<li class=""><a href="http://www.templatemagician.co/cssStatic/1568/services.html" class="sf-with-ul"><cufon class="cufon cufon-canvas" alt="services" style="width: 74px; height: 18px; "><canvas width="94" height="27" style="width: 94px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>services</cufontext></cufon><span class="sf-sub-indicator"></span></a>
<ul style="display: none; ">
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html"><cufon class="cufon cufon-canvas" alt="Lorem " style="width: 44px; height: 14px; "><canvas width="64" height="21" style="width: 64px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>Lorem </cufontext></cufon><cufon class="cufon cufon-canvas" alt="ipsum" style="width: 39px; height: 14px; "><canvas width="53" height="21" style="width: 53px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>ipsum</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html"><cufon class="cufon cufon-canvas" alt="vitae " style="width: 38px; height: 14px; "><canvas width="57" height="21" style="width: 57px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>vitae </cufontext></cufon><cufon class="cufon cufon-canvas" alt="nibh" style="width: 29px; height: 14px; "><canvas width="44" height="21" style="width: 44px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>nibh</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html"><cufon class="cufon cufon-canvas" alt="Parturit" style="width: 60px; height: 14px; "><canvas width="76" height="21" style="width: 76px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>Parturit</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html" class="sf-with-ul"><cufon class="cufon cufon-canvas" alt="sit " style="width: 23px; height: 14px; "><canvas width="43" height="21" style="width: 43px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>sit </cufontext></cufon><cufon class="cufon cufon-canvas" alt="amet" style="width: 33px; height: 14px; "><canvas width="49" height="21" style="width: 49px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>amet</cufontext></cufon><span class="sf-sub-indicator"></span></a>
<ul style="display: none; ">
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html"><cufon class="cufon cufon-canvas" alt="nasctur " style="width: 62px; height: 14px; "><canvas width="81" height="21" style="width: 81px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>nasctur </cufontext></cufon><cufon class="cufon cufon-canvas" alt="mus" style="width: 28px; height: 14px; "><canvas width="43" height="21" style="width: 43px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>mus</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/more.html"><cufon class="cufon cufon-canvas" alt="Integer " style="width: 53px; height: 14px; "><canvas width="72" height="21" style="width: 72px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>Integer </cufontext></cufon><cufon class="cufon cufon-canvas" alt="ac " style="width: 21px; height: 14px; "><canvas width="41" height="21" style="width: 41px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>ac </cufontext></cufon><cufon class="cufon cufon-canvas" alt="lacus" style="width: 41px; height: 14px; "><canvas width="56" height="21" style="width: 56px; height: 21px; top: -6px; left: -2px; "></canvas><cufontext>lacus</cufontext></cufon></a></li>
</ul>
</li>
</ul>
</li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/projects.html"><cufon class="cufon cufon-canvas" alt="Projects" style="width: 77px; height: 18px; "><canvas width="97" height="27" style="width: 97px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>Projects</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/equipment.html"><cufon class="cufon cufon-canvas" alt="Equipment" style="width: 87px; height: 18px; "><canvas width="108" height="27" style="width: 108px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>Equipment</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/partners.html"><cufon class="cufon cufon-canvas" alt="Partners" style="width: 80px; height: 18px; "><canvas width="100" height="27" style="width: 100px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>Partners</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/ournews.html"><cufon class="cufon cufon-canvas" alt="Our " style="width: 39px; height: 18px; "><canvas width="64" height="27" style="width: 64px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>Our </cufontext></cufon><cufon class="cufon cufon-canvas" alt="news" style="width: 45px; height: 18px; "><canvas width="64" height="27" style="width: 64px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>news</cufontext></cufon></a></li>
<li><a href="http://www.templatemagician.co/cssStatic/1568/contacts.html"><cufon class="cufon cufon-canvas" alt="contacts" style="width: 84px; height: 18px; "><canvas width="104" height="27" style="width: 104px; height: 27px; top: -7px; left: -3px; "></canvas><cufontext>contacts</cufontext></cufon></a></li>
</ul>
</div>
<div class="clear"></div>
</div>
</div>


<!-- Header Start -->
<div class="header_area">
<div class="slider_wrap">

            	<div class="slider" style="position: relative; overflow: hidden; ">

                  <ul class="items">

                    <li><img src="/img/slider1.jpg" alt="" width="880" height="389"></li>

                    <li><img src="/img/slider2.jpg" alt="" width="880" height="389">                    </li>

                    <li><img src="/img/slider3.jpg" alt="" width="880" height="389">                    </li>

                    <li><img src="/img/slider4.jpg" alt="" width="880" height="389">                    </li>
                  </ul>
              <div class="pic" style="overflow: hidden; width: 880px; height: 389px; background-image: url(http://www.templatemagician.co/cssStatic/1568/images/slider1.jpg); background-position: 0px 0px; background-repeat: no-repeat no-repeat; "><div class="mask" style="position: absolute; width: 100%; height: 100%; left: 0px; top: 0px; z-index: 1; "><div style="left: 0px; top: 0px; position: absolute; width: 880px; height: 389px; background-image: url(http://www.templatemagician.co/cssStatic/1568/images/slider1.jpg); opacity: 1; display: none; background-position: 0px 0px; "></div></div></div><ul class="pagination"><li class="current"><a href="http://www.templatemagician.co/cssStatic/1568/#"></a></li><li class=""><a href="http://www.templatemagician.co/cssStatic/1568/#"></a></li><li class=""><a href="http://www.templatemagician.co/cssStatic/1568/#"></a></li><li class=""><a href="http://www.templatemagician.co/cssStatic/1568/#"></a></li></ul></div>
        </div>
</div>


<!-- Banner Area Start -->
<div class="banr_area">
<div class="banr_top">
<div class="text"><cufon class="cufon cufon-canvas" alt="HEAVY " style="width: 65px; height: 22px; "><canvas width="96" height="32" style="width: 96px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>HEAVY </cufontext></cufon><cufon class="cufon cufon-canvas" alt="EQUIPMENT " style="width: 109px; height: 22px; "><canvas width="140" height="32" style="width: 140px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>EQUIPMENT </cufontext></cufon><cufon class="cufon cufon-canvas" alt="&amp; " style="width: 21px; height: 22px; "><canvas width="52" height="32" style="width: 52px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>&amp; </cufontext></cufon><cufon class="cufon cufon-canvas" alt="MACHINERY" style="width: 107px; height: 22px; "><canvas width="132" height="32" style="width: 132px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>MACHINERY</cufontext></cufon></div>
</div>
<div class="banr_1">
<a href="http://www.templatemagician.co/cssStatic/1568/#"><cufon class="cufon cufon-canvas" alt="Industrial " style="width: 114px; height: 22px; "><canvas width="144" height="32" style="width: 144px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Industrial </cufontext></cufon><cufon class="cufon cufon-canvas" alt="Gases" style="width: 61px; height: 22px; "><canvas width="85" height="32" style="width: 85px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Gases</cufontext></cufon></a>
</div>
<div class="banr_2">
<a href="http://www.templatemagician.co/cssStatic/1568/#"><cufon class="cufon cufon-canvas" alt="Metals " style="width: 77px; height: 22px; "><canvas width="108" height="32" style="width: 108px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Metals </cufontext></cufon><cufon class="cufon cufon-canvas" alt="&amp; " style="width: 21px; height: 22px; "><canvas width="52" height="32" style="width: 52px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>&amp; </cufontext></cufon><cufon class="cufon cufon-canvas" alt="Mining" style="width: 65px; height: 22px; "><canvas width="89" height="32" style="width: 89px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Mining</cufontext></cufon></a>
</div>
<div class="banr_3">
<a href="http://www.templatemagician.co/cssStatic/1568/#"><cufon class="cufon cufon-canvas" alt="Manufacturing" style="width: 159px; height: 22px; "><canvas width="182" height="32" style="width: 182px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Manufacturing</cufontext></cufon></a>
</div>
<div class="banr_4">
<a href="http://www.templatemagician.co/cssStatic/1568/#"><cufon class="cufon cufon-canvas" alt="Power " style="width: 71px; height: 22px; "><canvas width="102" height="32" style="width: 102px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Power </cufontext></cufon><cufon class="cufon cufon-canvas" alt="Generation" style="width: 111px; height: 22px; "><canvas width="135" height="32" style="width: 135px; height: 32px; top: -9px; left: -3px; "></canvas><cufontext>Generation</cufontext></cufon></a>
</div>
</div>


<!-- Body Area Start -->
<div class="body_area">

TEST BODY

