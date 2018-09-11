<?php
	
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	
	$index_media_file = file_get_contents('data/index_media.json');
	$index_media = json_decode($index_media_file, true);

	$index_filtered = $index_media;

	if ($_REQUEST["a"] == "search") {
		require_once(__DIR__."/server/functions.php");
		$index_filtered = searchParams($_REQUEST,$_REQUEST["q"]);
	}

	foreach($index_filtered as $mediaKey => $mediaItem) {
		if (strlen($mediaItem['mediaID']) == 0) {
			unset($mediaItem);
		}
	}

	$index_people_file = file_get_contents('data/index_people.json');
	$index_people = json_decode($index_people_file, true);

?>
<!DOCTYPE html>
<html lang="de" dir="ltr">
	<head class="html5">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="Expires" content="-1">

		<!-- Start AW Code Block -->
		<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
		<link rel="canonical" href="https://video.abgeordnetenwatch.de/" />
		<link rel="shortlink" href="https://video.abgeordnetenwatch.de/" />
		<meta property="og:site_name" content="video.abgeordnetenwatch.de" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="https://video.abgeordnetenwatch.de/" />
		<meta property="og:title" content="video.abgeordnetenwatch.de" />
		<meta property="og:description" content="Weil Transparenz Vertrauen schafft" />
		<meta property="og:image" content="client/images/aw_share_image_default.jpg" />
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:site" content="@a_watch" />
		<meta name="twitter:site:id" content="35142791" />
		<meta name="twitter:url" content="https://video.abgeordnetenwatch.de/" />
		<meta name="twitter:title" content="video.abgeordnetenwatch.de" />
		<meta name="twitter:description" content="Weil Transparenz Vertrauen schafft" />
		<meta name="twitter:image" content="client/images/aw_share_image_default.jpg" />
		<meta itemprop="name" content="Bundestag" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<!-- END AW Code Block -->

		<title>video.abgeordnetenwatch.de</title>
		
		<!-- Library Code -->

		<link rel="shortcut icon" href="favico.png">

		<script type="text/javascript" src="lib/frametrail/_lib/jquery/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="lib/frametrail/_lib/jquery.form/jquery.form.min.js"></script>
		<script type="text/javascript" src="lib/frametrail/_lib/jquery.ui/jquery-ui.min.js"></script>
		
		<!--
			[if lt IE 9]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
			<![endif]
		-->


		<!-- Application Code -->

		<link rel="stylesheet" type="text/css" id="FrameTrailCSSVariables" href="lib/frametrail/_shared/styles/variables.css">
		<link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/styles/frametrail-webfont.css">
		<!--<link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/styles/generic.css">-->

		<!-- AW CSS Parts -->
		<link type="text/css" rel="stylesheet" href="client/css/style.css" media="all" />

		<script type="text/javascript">

			$(document).ready( function() {

				$(document).on("click",".dropdown__trigger", function() {
					$(this).parent().toggleClass("dropdown--open");
				});

				$('[name="name"]').val(getQueryVariable('name'));
				$('[name="q"]').val(getQueryVariable('q'));

				var partyQueries = getQueryVariable('party');

				if (partyQueries) {
					for (var p=0; p<partyQueries.length; p++) {
						var cleanValue = partyQueries[p].replace('+', ' ').toUpperCase();
						if ($('[name="party[]"][value="'+cleanValue+'"]').length != 0) {
							$('[name="party[]"][value="'+cleanValue+'"]')[0].checked = true;
						}
					}
				}

				/* DATE FUNCTIONS START */

				var minDate = new Date('2017-10-01');
				var maxDate = new Date();
				var options = { year: 'numeric', month: '2-digit', day: '2-digit' };

				var queryFrom = getQueryVariable('timefrom');
				var queryTo = getQueryVariable('timeto');

				var queryFromDate = new Date(queryFrom*1000);
				var queryToDate = new Date(queryTo*1000);

				$('#slider-range').slider({
					range: true,
					max: Math.floor((maxDate.getTime() - minDate.getTime()) / 86400000),
					slide: function (event, ui) {
						
						var date1 = new Date(minDate.getTime());
						date1.setDate(date1.getDate() + ui.values[0]);
						var date2 = new Date(minDate.getTime());
						date2.setDate(date2.getDate() + ui.values[1]);
						
						$("#timeRange").val( date1.toLocaleDateString('de-DE', options) + " - " + date2.toLocaleDateString('de-DE', options) );

						$('#timefrom').val(date1.getTime()/1000);
						$('#timeto').val(date2.getTime()/1000);

					},
					values: [(queryFrom) ? Math.round( (queryFromDate.getTime() - minDate.getTime()) / 86400000) : 0, (queryTo) ? Math.round( (queryToDate.getTime() - minDate.getTime()) / 86400000) : Math.floor((maxDate.getTime() - minDate.getTime()) / 86400000)]
				});

				var startDate = (queryFrom) ? queryFromDate.toLocaleDateString('de-DE', options) : minDate.toLocaleDateString('de-DE', options);
				var endDate = (queryTo) ? queryToDate.toLocaleDateString('de-DE', options) : maxDate.toLocaleDateString('de-DE', options);

				$( "#timeRange" ).val( startDate + " - " + endDate );
				
				$('#timefrom').val((queryFrom) ? queryFrom : minDate.getTime() / 1000);
				$('#timeto').val((queryTo) ? queryTo : maxDate.getTime() / 1000);

				/* DATE FUNCTIONS END */
				

			});

			function getQueryVariable(variable) {
				var query = window.location.search.substring(1),
					vars = query.split("&"),
					pair,
					returnValues = null;
				for (var i = 0; i < vars.length; i++) {
					pair = vars[i].split("=");
					
					pair[0] = decodeURIComponent(pair[0]);
					pair[1] = decodeURIComponent(pair[1]);
					
					if (pair[0].indexOf('[]') != -1) {
						if (!returnValues) returnValues = [];
						if (pair[0].replace('[]', '') == variable) {
							returnValues.push(pair[1]);
						}
					} else if (pair[0] == variable) {
						returnValues = pair[1];
					}
				}

				return returnValues;
			}

		</script>

	</head>

	<body class="html front">
		<div id="skip-link">
			<a href="#main-content" class="element-invisible element-focusable">Zur Übersicht</a>
		</div>
		<div class="page-container" data-sidebar-container>
			
			<?php include_once('header.php'); ?>

			<main id="content">
				<a id="main-content"></a>

				<div class="intro">
					<h1 class="title">Videos</h1>
				</div>

				<div class="filterbar" style="margin-top: 0px;">
					<div class="filterbar__inner">
						<form data-ajax-target="#ajax" method="get" accept-charset="UTF-8" class="form form--pw-profiles-filters-form preventMultiPost-processed">
							<input type="hidden" name="a" value="search">
							<div class="filterbar__pre_swiper" style="z-index: 104;">
								<div class="filterbar__item filterbar__item--label">
									<i class="icon icon-investigation"></i> Filter
								</div>
								<div class="filterbar__item filterbar__item--input" style="padding-right: 10px;">
									<div class="form__item form__item--textfield form__item--keys">
										<label class="form__item__label sr-only" for="edit-keys">Suche </label>
										<input style="border-right: 1px solid #dfdbd2;" placeholder="Namen eingeben" id="edit-keys" name="name" value="" size="60" maxlength="128" class="form__item__control" type="text">
									</div>
								</div>
							</div>
							<div class="filterbar__swiper swiper-container-horizontal swiper-container-free-mode" style="right: 84px; left: 407px; z-index: 103;">
								<div class="filterbar__swiper__inner" style="transform: translate3d(0px, 0px, 0px);">
									
									<div class="filterbar__item filterbar__item--dropdown dropdown">
										<div class="dropdown__trigger">
											Partei <i class="icon icon-arrow-down"></i>
										</div>
										<div class="dropdown__list">
											<div class="form__item form__item--checkboxes form__item--party">
												<label class="form__item__label sr-only" for="edit-party">Partei </label>
												<div class="form__item form__item--checkbox form__item--party-16120">
													<input id="edit-party-16120" name="party[]" value="CSU" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-16120">CSU </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-16122">
													<input id="edit-party-16122" name="party[]" value="DIE GRÜNEN" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-16122">DIE GRÜNEN </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-16124">
													<input id="edit-party-16124" name="party[]" value="DIE LINKE" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-16124">DIE LINKE </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-16118">
													<input id="edit-party-16118" name="party[]" value="SPD" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-16118">SPD </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-17362">
													<input id="edit-party-17362" name="party[]" value="CDU" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-17362">CDU </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-17363">
													<input id="edit-party-17363" name="party[]" value="FDP" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-17363">FDP </label>

												</div>
												<div class="form__item form__item--checkbox form__item--party-17364">
													<input id="edit-party-17364" name="party[]" value="AfD" class="form__item__control" type="checkbox"> <label class="form__item__label option" for="edit-party-17364">AfD </label>

												</div>

											</div>
										</div>
									</div>
									<div class="filterbar__item filterbar__item--input" style="padding: 10px; width: 300px;">
										<div class="form__item form__item--textfield form__item--keys">
											<label class="form__item__label sr-only" for="edit-query">Suchbegriff eingeben </label>
											<input style="border-right: 1px solid #dfdbd2;" placeholder="Suchbegriff eingeben" id="edit-query" name="q" value="" size="60" maxlength="128" class="form__item__control" type="text">
										</div>
									</div>
									<div class="filterbar__item filterbar__item--input" style="height: 60px; width: 70px; border-right: none;">
										<button type="submit" id="edit-submit" class="btn">Profile filtern</button>
									</div>
								</div>
								<div class="swiper-button-next swiper-button-disabled"></div>
								<div class="swiper-button-prev swiper-button-disabled"></div>
							</div>
							<div style="clear: both; z-index: 2; padding: 10px; border-top: 1px solid #dfdbd2; margin: 0 15px;">
								<p>
									<label for="timeRange">Zeitraum:</label>
									<input type="text" id="timeRange" readonly style="border:0; width: 500px;"/>
									<div id="slider-range"></div>
									<input type="hidden" id ="timefrom" name="timefrom"/>
									<input type="hidden" id ="timeto" name="timeto"/>
								</p>
							</div>
						</form>
						<!--
						<ul class="filterbar__view_options view-mode-processed">
							<li class="filterbar__view_options__item active"><a href="#" class="filterbar__view_options__item__link"><i class="icon icon-th"></i></a></li>
						</ul>
						-->
					</div>
				</div>

				<!-- Video List Container -->
				<div id="videoListContainer">
					<div class="tile-wrapper">
						<div class="filter-summary">
							<div class="filter-summary__content">
								<p><strong><?= count($index_filtered) ?></strong> Videos gefunden</p>
								<?php 
								if ($_REQUEST["a"] == "search") { ?>
									<a href="?" class="btn active"><i class="icon icon-close"></i>Alle Filter entfernen</a>
								<?php } ?>
							</div>
							<p><strong>Sortierung:</strong> Älteste zuerst</p>
						</div>
						<?php
						if ($_REQUEST["a"]=="search") {
							$paramStr = "&";
							$allowedParams = array_intersect_key($_REQUEST,array_flip(array("a","q","name","party","wahlperiode","timefrom","timeto","gender","degree","aw_uuid","rednerID","sitzungsnummer")));
							//print_r($allowedParams);
							foreach ($allowedParams as $k=>$v) {
								if (is_array($v)) {
									foreach ($v as $i) {
										$paramStr .= "&".$k."[]=".$i;
									}
								} else {
									$paramStr .= "&".$k."=".$_REQUEST[$k];
								}

							}
						}
						$tmpCnt = 0;
						foreach($index_filtered as $mediaKey => $mediaItem) {
								if (strlen($mediaItem['mediaID']) == 0) {
									continue;
								}

								$formattedDuration = gmdate("H:i:s", $mediaItem['duration']);

								$highlightedName = $index_people[$mediaItem['rednerID']]["first_name"].' '.$index_people[$mediaItem['rednerID']]["last_name"];
								if (strlen($_REQUEST['name']) > 1) {
									$highlightedName = str_replace($_REQUEST['name'], '<span class="hit">'.$_REQUEST['name'].'</span>', $highlightedName);
								}

								//$paramStr = ($paramStr) ? $paramStr."&index=".$tmpCnt : "";
								$tmpCnt++;
							?>
							<article class="video tile">
								<a style="display: block;" href="player.php?id=<?= $mediaKey.$paramStr ?>">
									<figure class="tile__image">
									<!--
										<img src="<?= $mediaItem['picture']['url'] ?>" width="500" height="333"/>
										<figcaption class="figcaption-overlay"><span>©&nbsp;<?= $mediaItem['picture']['copyright'] ?></span></figcaption>
									-->
									</figure>
									<div class="icon-play-circled2"></div>
									<div class="tile__date">
										<span class="date-display-single"><?= $mediaItem['date'] ?><br><?= $formattedDuration ?></span>
									</div>
									<div class="tile__meta__video">
										<div>WP <?= $mediaItem['wahlperiode'] ?> | Sitzung <?= $mediaItem['sitzungsnummer'] ?><br>
											<b><?= $highlightedName .' ('.$index_people[$mediaItem['rednerID']]["party"].')' ?></b></div>
									</div>
									<h3 class="tile__title mh-item">
										<?= $mediaItem['top'] ?><hr>
										<?php
										echo $mediaItem["headline"];
										/*foreach ($mediaItem["headline"] as $t) {
											echo $t;
										}*/
										?>
									</h3>
								</a>
								<?php 
								if (count($mediaItem['finds']) > 0) {
									echo '<div class="tile__timeline">';
								}
								foreach($mediaItem['finds'] as $result) {
									
										$leftPercent = 100 * ((float)$result['data-start'] / $mediaItem['duration']);
										$widthPercent  = 100 * (($result['data-end'] - $result['data-start']) / $mediaItem['duration']);
									?>
									<a class="hit" data-context="<?= $result['context'] ?>" href="player.php?id=<?= $mediaKey.$paramStr.'#t='.$result['data-start'] ?>" style="left: <?= $leftPercent ?>%; width: <?= $widthPercent ?>%;"></a>
									<?php
								}
								if (count($mediaItem['finds']) > 0) {
									echo '</div>';
								}
								?>
							</article>
							<?php
						}
						?>

					</div>         
				</div>

			</main>

			<?php include_once('footer.php'); ?>

		</div>
	</body>
</html>