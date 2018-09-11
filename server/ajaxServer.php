<?php
/**
 * User: mimo
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

header('Content-Type: application/json');

$return["success"] = "false";
$return["text"] = "Nope!";
$return["return"] = "";

switch ($_REQUEST["a"]) {


	case "search":
		require_once("functions.php");
		/*$params["name"] = $_REQUEST["name"];
		$params["party"] = $_REQUEST["party"];
		$params["wahlperiode"] = $_REQUEST["wahlperiode"];*/
		$allowedParams = array_intersect_key($_REQUEST,array_flip(array("a","name","party","wahlperiode","timefrom","timeto","gender","degree","aw_uuid","rednerID","sitzungsnummer")));
		$return["success"] = "true";
		$return["text"] = "searchresults";
		$return["return"] = searchParams($allowedParams,$_REQUEST["q"]);
		//WORKS: $return["return"] = searchSpeech(array(array("key"=>"last_name","val"=>"Fechner","op"=>"="),array("key"=>"timestamp", "val"=>"1528840800","op"=>">"),array("key"=>"timestamp", "val"=>"1529272800","op"=>"<")));
		//$return["return"] = searchSpeech(array(array("key"=>"last_name","val"=>"/man/","op"=>"reg")));
	break;
	case "find":
		require_once("functions.php");
		$return["success"] = "true";
		$return["text"] = "searchresults";
		$return["return"] = searchInContent($_REQUEST["q"]);
	break;
	default:
	break;


}

echo json_encode($return,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>