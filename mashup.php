<?php
	
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    
    $index_media_file = file_get_contents('data/index_media.json');
    $index_media = json_decode($index_media_file, true);

    $index_people_file = file_get_contents('data/index_people.json');
    $index_people = json_decode($index_people_file, true);

	$index_filtered = $index_media;

	if ($_REQUEST["a"] == "search") {
		require_once(__DIR__."/server/functions.php");
		$index_filtered = searchParams($_REQUEST,$_REQUEST["q"]);
	}

    foreach($index_filtered as $mediaKey => $mediaItem) {
        if (strlen($mediaItem['mediaID']) == 0) {
            unset($index_filtered[$mediaKey]);
        }
    }
    
    $speechID = $_REQUEST['id'];
    
    $speech = $index_media[$speechID];

    $speechIndex = array_search($speechID, array_keys($index_filtered));
    $prevSpeech = ($speechIndex > 0) ? array_values(array_slice($index_filtered, $speechIndex-1, 1))[0] : null;
    $nextSpeech = ($speechIndex < count($index_filtered)) ? array_values(array_slice($index_filtered, $speechIndex+1, 1))[0] : null;
    
    $currentPerson = $index_people[$speech['rednerID']];

    $pathPeriod = sprintf('%02d',(int) $speech['wahlperiode']);
    $pathSession = sprintf('%03d',(int) $speech['sitzungsnummer']);

	$speechTOPTitle = $speech["headline"];

    $speechTitleShort = 'Redebeitrag '.$currentPerson['degree'].' '.$currentPerson['first_name'].' '.$currentPerson['last_name'].', '.$currentPerson['party'].'  (WP '.$speech['wahlperiode'].' | Sitzung '.$speech['sitzungsnummer'].' ('.$speech['date'].') | '.$speech['top'].' | '.$speech['date'].')';

    $speechTitle = 'WP '.$speech['wahlperiode'].' | Sitzung '.$speech['sitzungsnummer'].' ('.$speech['date'].') | '.$speech['top'].' <div class=\"speechTOPs\">'.$speechTOPTitle.'</div><h3><b>Redebeitrag '.$currentPerson['degree'].' '.$currentPerson['first_name'].' '.$currentPerson['last_name'].', '.$currentPerson['party'].'</b></h3>';

    $mediaSource = 'https://static.p.core.cdn.streamfarm.net/1000153copo/ondemand/145293313/'.$speech['mediaID'].'/'.$speech['mediaID'].'_h264_1920_1080_5000kb_baseline_de_5000.mp4';

    $htmlSource = $pathPeriod.$pathSession.'-Rede-'.$speechID.'.html';

    $annotationSource = 'data/'.$pathPeriod.'/'.$pathSession.'/'.$pathPeriod.$pathSession.'-Rede-'.$speechID.'_annotations.json';

    $htmlContents = file_get_contents('data/'.$pathPeriod.'/'.$pathSession.'/'.$htmlSource);
    $escapedHtmlContents = addslashes(str_replace(array("\r", "\n"), "", $htmlContents));

    $paramStr = "";
    if ($_REQUEST["a"]=="search") {
        $paramStr = "&";
        $allowedParams = array_intersect_key($_REQUEST,array_flip(array("a","q","name","party","wahlperiode","timefrom","timeto","gender","degree","aw_uuid","rednerID","sitzungsnummer")));
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

    $isResult = (strlen($paramStr) > 2) ? true : false;

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
    	<meta property="og:title" content="<?= $speechTitleShort ?> | video.abgeordnetenwatch.de" />
    	<meta property="og:description" content="Weil Transparenz Vertrauen schafft" />
    	<meta property="og:image" content="client/images/aw_share_image_default.jpg" />
    	<meta name="twitter:card" content="summary_large_image" />
    	<meta name="twitter:site" content="@a_watch" />
    	<meta name="twitter:site:id" content="35142791" />
    	<meta name="twitter:url" content="https://video.abgeordnetenwatch.de/" />
    	<meta name="twitter:title" content="<?= $speechTitleShort ?> | video.abgeordnetenwatch.de" />
    	<meta name="twitter:description" content="Weil Transparenz Vertrauen schafft" />
    	<meta name="twitter:image" content="client/images/aw_share_image_default.jpg" />
    	<meta itemprop="name" content="Bundestag" />
    	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    	<!-- END AW Code Block -->

    	<title><?= $speechTitleShort ?> | video.abgeordnetenwatch.de</title>
    	
    	<!-- Library Code -->

        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/html5reset/html5.reset.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/perfectscrollbar/perfect-scrollbar.min.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/jquery.ui/jquery-ui.min.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/openlayers/ol.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/codemirror/codemirror.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/codemirror/lint.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/codemirror/theme/hopscotch.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_lib/flexicolorpicker/colorpicker.css">

        <link rel="shortcut icon" href="favico.png">

        <script type="text/javascript" src="lib/frametrail/_lib/parsers/vtt.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/jquery/jquery-3.1.1.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/jquery.form/jquery.form.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/jquery.ui/jquery-ui.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/jquery.mousewheel/jquery.mousewheel.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/perfectscrollbar/perfect-scrollbar.min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/openlayers/ol.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/raphaeljs/raphael-min.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/raphaeljs/raphael-connections.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/jquery.collisiondetection/jquery.collisiondetection.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/animejs/anime.min.js"></script>

        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/codemirror.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/javascript.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/jshint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/css.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/css-hint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/xml.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/html-hint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/lint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/javascript-lint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/css-lint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/html-lint.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/codemirror/formatting.js"></script>

        <script type="text/javascript" src="lib/frametrail/_lib/wysihtml5/wysihtml5-parser.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/wysihtml5/wysihtml5.js"></script>
        <script type="text/javascript" src="lib/frametrail/_lib/flexicolorpicker/colorpicker.js"></script>


        <script type="text/javascript" src="lib/frametrail/_shared/frametrail-core/frametrail-core.js"></script>

        <!--
            [if lt IE 9]>
            <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
            <![endif]
        -->


        <!-- Application Code -->

        <link rel="stylesheet" type="text/css" id="FrameTrailCSSVariables" href="lib/frametrail/_shared/styles/variables.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/styles/frametrail-webfont.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/styles/generic.css">

        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/Annotation/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/Hypervideo/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/Overlay/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/Resource/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceImage/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceLocation/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourcePDF/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceAudio/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceVideo/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceVimeo/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceWebpage/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceWikipedia/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceYoutube/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/types/ResourceText/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/Subtitle/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/CodeSnippet/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/types/ContentView/style.css">

        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/modules/ResourceManager/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/modules/UserManagement/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/_shared/modules/ViewResources/style.css">

        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/Interface/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/InterfaceModal/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/Sidebar/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/Titlebar/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/ViewOverview/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/ViewVideo/style.css">
        <link rel="stylesheet" type="text/css" href="lib/frametrail/player/modules/ViewLayout/style.css">

        <script type="text/javascript" src="lib/frametrail/player/types/Annotation/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/types/Hypervideo/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/types/Overlay/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/Resource/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceImage/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceLocation/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourcePDF/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceAudio/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceVideo/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceVimeo/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceWebpage/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceWikipedia/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceYoutube/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/types/ResourceText/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/types/Subtitle/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/types/CodeSnippet/type.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/types/ContentView/type.js"></script>

        <script type="text/javascript" src="lib/frametrail/_shared/modules/Database/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/modules/TagModel/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/modules/ResourceManager/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/modules/UserManagement/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/modules/ViewResources/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/_shared/modules/RouteNavigation/module.js"></script>

        <script type="text/javascript" src="lib/frametrail/_shared/modules/UserTraces/module.js"></script>

        <script type="text/javascript" src="lib/frametrail/player/modules/AnnotationsController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/PlayerLauncher/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/HypervideoModel/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/HypervideoController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/InteractionController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/Interface/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/InterfaceModal/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/OverlaysController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/Sidebar/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/SubtitlesController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/Titlebar/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/CodeSnippetsController/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/ViewOverview/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/ViewVideo/module.js"></script>
        <script type="text/javascript" src="lib/frametrail/player/modules/ViewLayout/module.js"></script>

        <!-- Custom CSS in /_data/ -->
        <link rel="stylesheet" type="text/css" href="client/css/custom.css">

        <!-- AW CSS Parts -->
        <link type="text/css" rel="stylesheet" href="client/css/style.css" media="all" />

        <script type="text/javascript">

            $(document).ready( function() {

                window.myInstance = FrameTrail.init({
                    target:             '#awplayer',
                    contentTargets:     {},
                    contents:           [{
                        hypervideo: {
                            "meta": {
                                "name": "<?= $speechTitle ?>",
                                "thumb": "",
                                "creator": "Video Transcript Generator",
                                "creatorId": "0",
                                "created": 1519713627469,
                                "lastchanged": 1521025330334
                            },
                            "config": {
                                "slidingMode": "adjust",
                                "slidingTrigger": "key",
                                "autohideControls": true,
                                "captionsVisible": false,
                                "hidden": false,
                                "layoutArea": {
                                    "areaTop": [],
                                    "areaBottom": [],
                                    "areaLeft": [
                                        {
                                            "type": "CustomHTML",
                                            "contentSize": "large",
                                            "name": "<span class=\"icon-doc-text-1\"></span>",
                                            "description": "",
                                            "cssClass": "",
                                            "collectionFilter": {
                                                "tags": [],
                                                "types": [],
                                                "users": [],
                                                "text": ""
                                            },
                                            "onClickContentItem": "",
                                            "html": "<?= $escapedHtmlContents ?>",
                                            "transcriptSource": ""
                                        }
                                    ],
                                    "areaRight": []
                                }
                            },
                            "clips": [
                                {
                                    "resourceId": null,
                                    "src": "<?= $mediaSource ?>",
                                    "duration": 0,
                                    "start": 0,
                                    "end": 0,
                                    "in": 0,
                                    "out": 0
                                }
                            ],
                            "globalEvents": {
                                "onReady": "",
                                "onPlay": "",
                                "onPause": "",
                                "onEnded": ""
                            },
                            "customCSS": "",
                            "contents": [
                            <?php 
                            $rCnt = 0;
                            foreach ($index_filtered[$speechID]['finds'] as $result) { 
                                
                                $rCnt++;

                                $resultIndex = array_search($result, array_keys($index_filtered[$speechID]['finds']));

                                $nextResult = $index_filtered[$speechID]['finds'][$rCnt];

                            ?>
                                {
                                    "@context": [
                                        "http://www.w3.org/ns/anno.jsonld",
                                        {
                                            "frametrail": "http://frametrail.org/ns/"
                                        }
                                    ],
                                    "creator": {
                                        "nickname": "demo",
                                        "type": "Person",
                                        "id": "1"
                                    },
                                    "created": "Wed Mar 14 2018 11:33:19 GMT+0100 (CET)",
                                    "type": "Annotation",
                                    "frametrail:type": "CodeSnippet",
                                    "target": {
                                        "type": "Video",
                                        "source": "<?= $mediaSource ?>",
                                        "selector": {
                                            "conformsTo": "http://www.w3.org/TR/media-frags/",
                                            "type": "FragmentSelector",
                                            "value": "t=<?= (float)$result['data-end'] ?>"
                                        }
                                    },
                                    "body": {
                                        "type": "TextualBody",
                                        "frametrail:type": "codesnippet",
                                        "format": "text/javascript",
                                        "value": "myInstance.pause();myInstance.currentTime=<?= $nextResult['data-start'] ?>;myInstance.play();",
                                        "frametrail:name": "Custom Code Snippet",
                                        "frametrail:thumb": null,
                                        "frametrail:resourceId": null
                                    },
                                    "frametrail:attributes": {}
                                },
                            <?php 
                            } 
                            ?>
                                {
                                    "@context": [
                                        "http://www.w3.org/ns/anno.jsonld",
                                        {
                                            "frametrail": "http://frametrail.org/ns/"
                                        }
                                    ],
                                    "creator": {
                                        "nickname": "demo",
                                        "type": "Person",
                                        "id": "1"
                                    },
                                    "created": "Wed Mar 14 2018 11:33:19 GMT+0100 (CET)",
                                    "type": "Annotation",
                                    "frametrail:type": "CodeSnippet",
                                    "target": {
                                        "type": "Video",
                                        "source": "<?= $mediaSource ?>",
                                        "selector": {
                                            "conformsTo": "http://www.w3.org/TR/media-frags/",
                                            "type": "FragmentSelector",
                                            "value": "t=<?= (float)end($index_filtered[$speechID]['finds'])['data-end'] ?>"
                                        }
                                    },
                                    "body": {
                                        "type": "TextualBody",
                                        "frametrail:type": "codesnippet",
                                        "format": "text/javascript",
                                        "value": "myInstance.pause();location.search=$('.nextSpeech').attr('href');",
                                        "frametrail:name": "Custom Code Snippet",
                                        "frametrail:thumb": null,
                                        "frametrail:resourceId": null
                                    },
                                    "frametrail:attributes": {}
                                }
                            ],
                            "subtitles": []
                        },//annotations: ["<?=$annotationSource?>"]
                        annotations: null
                    }],
                    startID:            '0',
                    resources:          [{
                                                label: "Choose Resources",
                                                data: {},
                                                type: "frametrail"
                                            },
                                            {
                                                label: "British Library",
                                                data: "https://..../lorem.json",
                                                type: "iiif"
                                         }],
                    tagdefinitions: {
                        "prototype": {
                            "en": {
                                "label": "Prototype",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Prototyp",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        },
                        "testtag": {
                            "en": {
                                "label": "First Test Tag",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Erster Test Tag",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        },
                        "zweiter_test_tag": {
                            "en": {
                                "label": "Second Test Tag",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Zweiter Test Tag",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        },
                        "shortform_tag": {
                            "en": {
                                "label": "Longform Tag",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Tag Name",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        },
                        "shortform_test": {
                            "en": {
                                "label": "Longform Test",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Langer Tag Test",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        },
                        "tagtest12": {
                            "en": {
                                "label": "Tagtest",
                                "description": "Lorem English Dolor Sit Amet"
                            },
                            "de": {
                                "label": "Tagtest",
                                "description": "Lorem Deutsch Dolor Sit Amet"
                            }
                        }
                    },
                    config: {
                        "updateServiceURL": "https://update.frametrail.org",
                        "autoUpdate": false,
                        "defaultUserRole": "user",
                        "captureUserTraces": true,
                        "userTracesStartAction": "UserLogin",
                        "userTracesEndAction": "UserLogout",
                        "userNeedsConfirmation": false,
                        "alwaysForceLogin": false,
                        "allowCollaboration": false,
                        "allowUploads": true,
                        "theme": "abgeordnetenwatch",
                        "defaultHypervideoHidden": false,
                        "userColorCollection": [
                            "597081",
                            "339966",
                            "16a09c",
                            "cd4436",
                            "0073a6",
                            "8b5180",
                            "999933",
                            "CC3399",
                            "7f8c8d",
                            "ae764d",
                            "cf910d",
                            "b85e02"
                        ]
                    }
                });
                
                myInstance.on('ready', function() {

                    var downloadOptions = $('<div class="downloadOptions">'
                                        +       '<div class="icon icon-download"></div>'
                                        +   '</div>');

                    var prevLabel = "<?= ($isResult) ? 'Vorheriges Ergebnis' : 'Vorheriger Redebeitrag' ?>";
                    var prevSpeaker = <?= ($prevSpeech) ? '"'.$index_people[$prevSpeech['rednerID']]['degree'].' '.$index_people[$prevSpeech['rednerID']]['first_name'].' '.$index_people[$prevSpeech['rednerID']]['last_name'].' ('.$index_people[$prevSpeech['rednerID']]['party'].') <br>Sitzung '.$prevSpeech['sitzungsnummer'].' ('.$prevSpeech['date'].') <br>'.$prevSpeech['headline'].'"' : 'null' ?>;

                    var nextLabel = "<?= ($isResult) ? 'Nächstes Ergebnis' : 'Nächster Redebeitrag' ?>";
                    var nextSpeaker = <?= ($nextSpeech) ? '"'.$index_people[$nextSpeech['rednerID']]['degree'].' '.$index_people[$nextSpeech['rednerID']]['first_name'].' '.$index_people[$nextSpeech['rednerID']]['last_name'].' ('.$index_people[$nextSpeech['rednerID']]['party'].') <br>Sitzung '.$nextSpeech['sitzungsnummer'].' ('.$nextSpeech['date'].') <br>'.$nextSpeech['headline'].'"' : 'null' ?>;
                    
                    var navigationOptions = $('<div class="navigationOptions"></div>');
                    if (prevSpeaker) {
                        navigationOptions.append('<a href="?id=<?= $prevSpeech['id'].$paramStr ?>" class="prevSpeech"><b>'+ prevLabel +'</b><br>'+ prevSpeaker +'</a>');
                    }
                    if (nextSpeaker) {
                        navigationOptions.append('<a href="?id=<?= $nextSpeech['id'].$paramStr ?>" class="nextSpeech"><b>'+ nextLabel +'</b><br>'+ nextSpeaker +'</a>');
                    }
                    
                    var playerOptions = $('<div class="playerOptions"></div>');
                    playerOptions.append(navigationOptions, downloadOptions);

                    $('.frametrail-body .titlebar').append(playerOptions);

                    window.setTimeout(function() {
                        myInstance.currentTime = <?= $index_filtered[$speechID]['finds'][0]['data-start'] ?>;
                        myInstance.play();
                    }, 1000);

                });

                myInstance.on('ended', function() {
                    //location.search = $('.nextSpeech').attr('href');
                });

            });

        </script>

    </head>
    <body class="html front">
    	<div class="page-container" data-sidebar-container>
    		
    		<main id="content" style="height: 100vh;">
    			<a id="main-content"></a>

                
                <!-- FrameTrail Container -->
    			<div id="awplayer"></div>

    		</main>

        </div>
    </body>
</html>