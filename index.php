<?php
ini_set('display_errors', 0);

function multiscrappingcurl($urls, $options = array()) {
    $ch = array();
    $results = array();
    $mh = curl_multi_init();
    foreach($urls as $key => $val) {
        $ch[$key] = curl_init();
        if ($options) {
            curl_setopt_array($ch[$key], $options);
        }
        curl_setopt($ch[$key], CURLOPT_URL, $val);
        curl_multi_add_handle($mh, $ch[$key]);
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    }
    while ($running > 0);
    // Get content and remove handles.
    foreach ($ch as $key => $val) { 
        $results[$key] = curl_multi_getcontent($val);
        curl_multi_remove_handle($mh, $val);
    }
    curl_multi_close($mh);
	
    return $results;
}

$doc_to_parse = array(
	'http://www.sindoweekly-magz.com',
	'http://www.kaskus.co.id',
	'http://www.tempo.co'
);

$scrap_data = multiscrappingcurl($doc_to_parse, array(CURLOPT_RETURNTRANSFER => TRUE));
$dom = new DOMDocument();
$doc->preserveWhiteSpace = false;
$scrap = array();
$index = 0;
foreach($scrap_data as $html) {
	$dom->loadHTML($html);
	$title = $dom->getElementsByTagName('title');
	$domxpath = new DOMXPath($dom);
	$title = $domxpath->query('//title');
	$keyword = $domxpath->query('//meta[@name="keywords"]');
	$description = $domxpath->query('//meta[@name="description"]');
	
	for ($i = 0; $i < $title->length; $i++) {
		$scrap[$index]['url'] = $doc_to_parse[$index];
		$scrap[$index]['title'] = $title->item($i)->nodeValue;
		$scrap[$index]['keyword'] = $keyword->item($i)->getAttribute('content');
		$scrap[$index]['description'] = $description->item($i)->getAttribute('content');
	}
	
	$index++;
}

print '<pre>';
print_r($scrap);
print '</pre>';