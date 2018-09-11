<?php
/**
 * User: mimo
 */


/**
 *
 * Translates $_REQUEST to searchable parameter for function searchSpeech()
 * if $find is set (as string or array) it looks fot the content of the speeches for matches
 *
 * Right now there are following parameter:
 * $request["name"] - expects a string of a persons first or last name (or both). Case insensitive
 * $request["party"] - expects an array of partys. Case insensitive. like ["foo"] or ["foo","bar"]
 * $request["wahlperiode"] - expects an array of election periods. like ["foo"] or ["foo","bar"]
 * $request["timefrom"] - expects an unix timestamp and looks for newer speeches (>=)
 * $request["timeto"] - expects an unix timestamp and looks for older speeches (<=)
 * $request["gender"] - expects a string "male" or "female"
 * $request["degree"] - expects a string like "Dr." or "Prof. Dr."
 * $request["aw_uuid"] - expects a string of an Abgeordnetenwatch unique ID
 * $request["rednerID"] - expects a string of a Bundestag-PersonID
 * $request["sitzungsnummer"] - expects a string of a session number. Makes sense to combine it with wahlperiode or e.g. to get every first session of every period
 *
 * @param array $request
 * @find bool|array $request
 * @return array
 */
function searchParams($request,$find=false) {

	$params = array();

	if ($request["name"]) {
		$params[] = array(
			"key"=>"full_name",
			"op"=>"regex",
			"val"=>"/".$request["name"]."/i"
		);
	}

	if ($request["party"]) {
		$tmpRegex = "/(";
		if (is_array($request["party"])) {
			foreach ($request["party"] as $tmpK => $tmpParty) {
				$tmpRegex .= $tmpParty . ($tmpK + 1 < count($request["party"]) ? "|" : "");
			}
			$tmpRegex .= ")/i";
		} else {
			$tmpRegex = "/".$request["party"]."/i";
		}

		$params[] = array(
			"key"=>"party",
			"op"=>"regex",
			"val"=>$tmpRegex
		);
	}
	if ($request["wahlperiode"]) {
		$tmpRegex = "/(";
		foreach($request["wahlperiode"] as $tmpK=>$tmpParty) {
			$tmpRegex .= $tmpParty.($tmpK+1<count($request["wahlperiode"]) ? "|": "");
		}
		$tmpRegex .= ")/";

		$params[] = array(
			"key"=>"wahlperiode",
			"op"=>"regex",
			"val"=>$tmpRegex
		);
	}

	if ($request["timefrom"]) {
		$params[] = array(
			"key"=>"timestamp",
			"op"=>">=",
			"val"=>$request["timefrom"]*1
		);
	}

	if ($request["timeto"]) {
		$params[] = array(
			"key"=>"timestamp",
			"op"=>"<=",
			"val"=>$request["timeto"]
		);
	}

	if ($request["gender"]) {
		$params[] = array(
			"key"=>"gender",
			"op"=>"=",
			"val"=>$request["gender"]
		);
	}

	if ($request["degree"]) {
		$params[] = array(
			"key"=>"degree",
			"op"=>"regex",
			"val"=>"/".$request["degree"]."/i"
		);
	}



	if ($request["aw_uuid"]) {
		$params[] = array(
			"key"=>"aw_uuid",
			"op"=>"=",
			"val"=>$request["aw_uuid"]
		);
	}



	if ($request["rednerID"]) {
		$params[] = array(
			"key"=>"rednerID",
			"op"=>"=",
			"val"=>$request["rednerID"]
		);
	}



	if ($request["sitzungsnummer"]) {
		$params[] = array(
			"key"=>"sitzungsnummer",
			"op"=>"=",
			"val"=>$request["sitzungsnummer"]
		);
	}

	//print_r($params);
	if (count($params)<1) {
		$params=false;
	}

	return searchSpeech($params,$find);


}
/**
 * searches for people who are represented in our speech database. (people who delivered a speech)
 * expects as first parameter an object with following type:
 * [
 * 	{
 *	 	"key": "name", //the key in the index_media merged with index_people database
 * 		"val": "Michael", //the value of the given key
 * 		"op": "=" // Operator. available: = (which is ==), !=, <=, >=, <, >, regex (which expects val to be a valid regex)
 *	}, //As you can see this will not find any result because there is no person with the very name "Michael". To find any Michaels use a regex with wildcards.
 *	...
 * ]
 * @param array|bool $params
 * @param array|bool $find
 * @return array
 */
function searchSpeech($params=false,$find=false) {
	if ($params) {
		$med = json_decode(file_get_contents(__DIR__."/../data/index_media.json"),true);
		$ppl = json_decode(file_get_contents(__DIR__."/../data/index_people.json"),true);
		$return = array();
		foreach ($med as $k=>$m) {
			$m = array_merge($m, $ppl[$m["rednerID"]]);
			$m["timestamp"] = strtotime($m["date"]);
			$m["full_name"] = $m["first_name"]." ".$m["last_name"];
			$keep = 1;
			foreach ($params as $param) {
				if (!num_cond($m[$param["key"]],$param["op"],$param["val"])) {
					$keep = 0;
				}
			}
			if ($keep == 1) {
				$return[$m["id"]] = $m;
			}
		}
		if ($find && $return) {
			//If there are results and a search for content is given
			return searchInContent($find,$return);
		} else {
			//If there is no content search
			return $return;
		}

	} elseif ($find) {
		//if there is just a search for content
		return searchInContent($find);
	}
}



function num_cond ($var1, $op, $var2) {

	switch ($op) {
		case "=":  return $var1 == $var2;
		case "!=": return $var1 != $var2;
		case ">=": return $var1 >= $var2;
		case "<=": return $var1 <= $var2;
		case ">":  return $var1 >  $var2;
		case "<":  return $var1 <  $var2;
		case "regex":  return preg_match($var2,$var1);
		default:       return true;
	}
}


/**
 *
 * searchInContent finds words appearing in speeches contents.
 * if pattern is an array it just returns results where all values match (like a "&&" operator. e.g. $pattern[0] && $pattern[1] && ...)
 * if a subset is given, it just search inner these. The expected subset is an array of speeches in the form of index_media
 *
 * @param string|array $pattern
 * @param bool|array $subset
 * @return array
 */
function searchInContent($pattern,$subset=false) {
	if ($pattern) {
		if ($subset) {
			$med = $subset;
		} else {
			$med = json_decode(file_get_contents(__DIR__."/../data/index_media.json"),true);
		}
		$ppl = json_decode(file_get_contents(__DIR__."/../data/index_people.json"),true);


		$return = array();

		foreach ($med as $k=>$m) {
			$period = sprintf('%02d',(int)$m["wahlperiode"]);
			$session = sprintf('%03d',(int)$m["sitzungsnummer"]);
			$html = file_get_contents(__DIR__."/../data/".$period."/".$session."/".$period.$session."-Rede-".$m["id"].".html");
			if ($html) {
				$dom = new DOMDocument();
				$dom->loadHTML($html);
				$xPath = new DOMXPath($dom);
				//$elems = $xPath->query("//div[@class='rede']/*");
				$elems = $xPath->query("//div[@class='rede']/div | //div[@class='rede']/p/span");
				foreach($elems as $k=>$elem) {
					$matches = false;
					if (is_array($pattern)) {
						foreach($pattern as $p) {
							if (!preg_match("/\b(\w*".$p."\w*)\b/",$elem->nodeValue)) {
								$matches = false;
								break;
							} else {
								$matches = true;
							}
						}
					} else {
						if (preg_match("/\b(\w*".$pattern."\w*)\b/",$elem->nodeValue)) {
							$matches = true;
						}
					}
					if ($matches) {
						//$tmp = array();
						//$tmp = array_merge($tmp, $m);
						/*$tmp["period"] = $period;
						$tmp["session"] = $session;
						$tmp["rede"] = $m["id"];
						$tmp["mediaID"] = $m["mediaID"];
						$tmp["rednerID"] = $m["rednerID"];
						$tmp["headline"] = $m["headline"];
						$tmp["headline"] = $m["headline"];*/
						//array_merge($return[$m["id"]],$m);
						if (!array_key_exists($m["id"],$return)) {
							$m = array_merge($m, $ppl[$m["rednerID"]]);
							$m["timestamp"] = strtotime($m["date"]);
							$m["full_name"] = $m["first_name"]." ".$m["last_name"];
							$return[$m["id"]] = $m;
						}
						$tmp["data-start"] = $elem->getAttribute("data-start");
						$tmp["data-end"] = $elem->getAttribute("data-end");
						$tmp["class"] = ($elem->hasAttribute("class")) ? $elem->getAttribute("class") : "";
						$tmp["klasse"] = ($elem->hasAttribute("klasse")) ? $elem->getAttribute("klasse") : "";
						$tmp["context"] = $elem->nodeValue;
						$return[$m["id"]]["finds"][] = $tmp;
					}
				}
			}

		}
		return $return;


	}


}



?>