<?php

// Web proxy
$config['proxy_name'] = 'wwwcache.gla.ac.uk';
$config['proxy_port'] = 8080;

// No proxy
$config['proxy_name'] = '';
$config['proxy_port'] = '';

$config['flickr_api_key']  = ''; // paste your Flickr API key here
$config['flickr_group_id'] = '806927@N20'; // EOL

//--------------------------------------------------------------------------------------------------
/**
 * @brief Test whether HTTP code is valid
 *
 * HTTP codes 200 and 302 are OK.
 *
 * For JSTOR we also accept 403
 *
 * @param HTTP code
 *
 * @result True if HTTP code is valid
 */
function HttpCodeValid($http_code)
{
	if ( ($http_code == '200') || ($http_code == '302') || ($http_code == '403'))
	{
		return true;
	}
	else{
		return false;
	}
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief GET a resource
 *
 * Make the HTTP GET call to retrieve the record pointed to by the URL. 
 *
 * @param url URL of resource
 *
 * @result Contents of resource
 */
function get($url, $userAgent = '', $timeout = 0)
{
	global $config;
	
	$data = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	//curl_setopt ($ch, CURLOPT_HEADER,		  1);  

	curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	
	if ($userAgent != '')
	{
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	}	
	
	if ($timeout != 0)
	{
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	}
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
	
			
	$curl_result = curl_exec ($ch); 
	
	//echo $curl_result;
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		 //$header = substr($curl_result, 0, $info['header_size']);
		//echo $header;
		
		
		$http_code = $info['http_code'];
		
		//echo "<p><b>HTTP code=$http_code</b></p>";
		
		if (HttpCodeValid ($http_code))
		{
			$data = $curl_result;
		}
	}
	return $data;
}


//--------------------------------------------------------------------------------------------------
// Find how many photos in group
$params = array(
	'api_key'	=> $config['flickr_api_key'],
	'method'	=> 'flickr.groups.getInfo',
	'group_id' => $config['flickr_group_id'],
	'format' => 'json',
	'nojsoncallback' => 1
);

$encoded_params = array();

foreach ($params as $k => $v){

	$encoded_params[] = urlencode($k).'='.urlencode($v);
}

$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);

$rsp = get($url);


$obj = json_decode($rsp);

//echo "\nNumber of photos in group: " . $obj->group->pool_count->_content . "\n";

//--------------------------------------------------------------------------------------------------
// Fetch photos 

// fetch in "pages" of 100
$pages = floor($obj->group->pool_count->_content/ 100) + 1;

for ($page = 1; $page <= $pages; $page++)
{
	#
	# build the API URL to call
	#
	
	$params = array(
		'api_key'	=> $config['flickr_api_key'],
		'method'	=> 'flickr.groups.pools.getPhotos',
		
		'group_id' => $config['flickr_group_id'],
		'extras' => 'geo,machine_tags',
		
		'format' => 'json',
		'nojsoncallback' => 1,
		
		'per_page' => 100,
		'page' => $page
	);
	
	//print_r($params);
	
	$encoded_params = array();
	
	foreach ($params as $k => $v){
	
		$encoded_params[] = urlencode($k).'='.urlencode($v);
	}
		
	#
	# call the API and decode the response
	#
	
	$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
	
	$rsp = get($url);
	
	
	$rsp_obj = json_decode($rsp);
	
	
	foreach ($rsp_obj->photos->photo as $photo)
	{
		echo $photo->id;
		echo "\t" . $photo->owner;
		echo "\t" . $photo->title;
		echo "\t" . $photo->latitude;
		echo "\t" . $photo->longitude;
		echo "\t" . $photo->machine_tags;
		echo "\n";
	}
}

?>