<?php

$urls = array();
$images = array();

	function crawl_page($url, $depth = 1)
	{
	    static $seen = array();
	    if (isset($seen[$url]) || $depth === 0) {
	        return;
	    }

	    $seen[$url] = true;

	    $dom = new DOMDocument('1.0');
	    @$dom->loadHTMLFile($url);

	    $anchors = $dom->getElementsByTagName('a');
	    $imgs = $dom->getElementsByTagName('img');
	    foreach ($anchors as $element) {
	        $href = $element->getAttribute('href');
	        if (0 !== strpos($href, 'http')) {
	            $path = '/' . ltrim($href, '/');
	            if (extension_loaded('http')) {
	                $href = http_build_url($url, array('path' => $path));
	            } else {
	                $parts = parse_url($url);
	                $href = $parts['scheme'] . '://';
	                if (isset($parts['user']) && isset($parts['pass'])) {
	                    $href .= $parts['user'] . ':' . $parts['pass'] . '@';
	                }
	                $href .= $parts['host'];
	                $thishost = $parts['host'];
	                if (isset($parts['port'])) {
	                    $href .= ':' . $parts['port'];
	                }
	                $href .= $path;
				    foreach($imgs as $img) {
						$temp = $img->getAttribute('src');
						$path = Relative_2_Absolute($temp, $thishost);
			     		if ( !in_array($path, $images) ) {
			             	$code = Get_Http_Code($path);
				    		$images[$path] = $code;
					    }
				    }
				  	if ( !in_array($href, $urls) ) {
						$code = Get_Http_Code($url);
						$urls[$href] = $code;
	    				crawl_page($href, $depth - 1);
				    }
	            }
	        }
		    // echo $href . "<br />",PHP_EOL;
	        //  $_SERVER['SERVER_NAME']
		    // handle 'img'
	    }

	    // echo "URL:",$url,PHP_EOL,"CONTENT:",PHP_EOL,$dom->saveHTML(),PHP_EOL,PHP_EOL;
	    // foreach ($urls as $url => $value) {
	    // 	// $code = Get_Http_Code($url);
	    // 	echo "$url = [$value]<br />";// . Get_Http_Code($url);
	    // }

		foreach ($urls as $url => $value) {
			// $code = Get_Http_Code($url);
			echo "$url = [$value]<br />";		// . Get_Http_Code($url);
		}

		echo "*************  IMAGES  *************<br />";
		foreach ($images as $img => $value) {
			echo "$img = [$value]<br />";
		}

	}
	
	/* Thank you StackOverflow...
	 * http://stackoverflow.com/questions/1243418/php-how-to-resolve-a-relative-url
	 * This function would have taken a week to write.
	 * */
	function Relative_2_Absolute($rel, $base) {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

        /* queries and anchors */
        if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

        /* parse base URL and convert to local variables:
         $scheme, $host, $path */
        extract(parse_url($base));
		
		$scheme = "http"; 
        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/') $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

        /* absolute URL is ready! */
        return $scheme.'://'.$abs;
    }

	function Get_Http_Code($url) {  
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_HEADER, 1);
	  curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
	  $data = curl_exec($ch);
	  $headers = curl_getinfo($ch);
	  curl_close($ch);

	  return $headers['http_code'];
	}


$url = "http://spsu.edu/";
crawl_page($url, 1);