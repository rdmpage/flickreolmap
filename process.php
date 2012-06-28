<?php

// Read tab-delimited list of Flickr photos
$filename = 'photos.txt';

$file_handle = fopen($filename, "r");

$squares = array();

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	$parts = explode("\t", $line);
	
	$latitude 	= $parts[3];
	$longitude 	= $parts[4];
	
	// If we have latitude and longitude, round to nearest degree
	if (($latitude != 0) && ($longitude != 0))
	{
		$lat = intval($latitude);
		if (!isset($squares[$lat]))
		{
			$squares[$lat] = array();
		}
		$long = intval($longitude);
		if (!isset($squares[$lat][$long]))
		{
			$squares[$lat][$long] = 0;
		}
		
		// increment number of photos from this degree square
		$squares[$lat][$long]++;
	}
}

// Generate SVG map
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns:xlink="http://www.w3.org/1999/xlink" 
xmlns="http://www.w3.org/2000/svg" 
width="720px" height="360px">
   <style type="text/css">
      <![CDATA[     
      .region 
      { 
        fill:blue; 
        opacity:0.4; 
        stroke:blue;
      }
      ]]>
   </style>
  <rect id="dot1" x="-1" y="-1" width="1" height="1" style="stroke:none; stroke-width:0; fill:#ffff00"/>
  <rect id="dot10" x="-1" y="-1" width="1" height="1" style="stroke:none; stroke-width:0; fill:ffcc00"/>
  <rect id="dot100" x="-1" y="-1" width="1" height="1" style="stroke:none; stroke-width:0; fill:ff9900"/>
  <rect id="dot1000" x="-1" y="-1" width="1" height="1" style="stroke:none; stroke-width:0; fill:ff6600"/>
  <rect id="dot10000" x="-1" y="-1" width="1" height="1" style="stroke:none; stroke-width:0; fill:ff3300"/>

  <!-- background map image -->
  <image x="0" y="0" width="720" height="360" xlink:href="' . 'gbif720x360.png"/>

  <g transform="translate(360,180) scale(2,-2)">';
 

foreach ($squares as $lat => $longitude)
{
	foreach ($longitude as $long => $count)
	{
		$n = intval(log10($count));
		switch ($n)
		{
			case 0:
				$id = 'dot1';
				break;
			case 1:
				$id = 'dot10';
				break;
			case 2:
				$id = 'dot100';
				break;
			case 3:
				$id = 'dot1000';
				break;
			case 4:
				$id = 'dot10000';
				break;
			default:
				$id = 'dot1';
				break;
		}
				
		$xml .= '<!-- ' . $count . ' -->' . "\n";
	
		$xml .= '   <use xlink:href="#' . $id . '" transform="translate(' . $long . ',' . $lat . ')" />' . "\n";
	}
}

$xml .= '
      </g>
	</svg>';
	

echo $xml;

?>