<?php
 
// define( "CRAWL_LIMIT_PER_DOMAIN", 2 );
// Used to store the number of pages crawled per domain.
// $domains = array();
// List of all our crawled URLs.
// $urls = array();
 

class OurStuff {
		public $domain;
		public $urls = array();
		public $all_found = array();
		public $scanned = array();
		public $dirty = array();
		public $depth = 1;
		public $errors = array();
		public $total_scanned = 0;
		public function __construct() {
			$this->domain = "http://spsu.edu/";
			$this->urls = array();
			$this->all_found[] = "http://spsu.edu";
			$this->scanned = array();
			$this->dirty = array();
			$this->depth = 1;
			$this->errors = array();
			$this->total_scanned = 0;
		}
	  }

class Crawler {    
	function crawl( $stuff ) {		
		if (count($stuff->urls) > 500) { 
    		// echo $stuff->total_scanned . "<br />";
    		echo "total urls = " . count($stuff->urls) . "<br />";
    		return; }

		$url = array_pop($stuff->all_found);

		$content = file_get_contents( $url );    
		if ( $content === FALSE ) {
			// echo "$url  ==>  Error.\n";
			$stuff->errors[] = $url;
			$this->crawl($stuff);
		}

		$parse = parse_url( $url );

		$host = $parse['host'];
		// $stuff->domains[ $host ]++;
		$stuff->urls[$host] = $url;

		$DOM = new DOMDocument;
		$DOM->loadHTML($content);

		$tmp1 = $this->getAllLinks($DOM);
		foreach ($tmp1 as $link) {
			if ( !in_array($link, $stuff->scanned) ) {
				// $currentdomain = $stuff->domains[count($stuff->domain)-1];
				$path = $this->Relative_2_Absolute($link,$stuff->domain);
				$code = $this->Get_Http_Code($path);
				$stuff->scanned[$link] = $code;
				$stuff->all_found[] = $path;
			}
    		// var_dump($stuff);
    		echo "$path = [$code]<br />";
			unset($path);
			unset($code);
		}


	    $this->crawl($stuff);
	}
	function getAllLinks(DOMDocument $thisDOM) {
	  $array;
	  $aVals = $thisDOM->getElementsByTagName('a');
	  foreach ($aVals as $found_a) {
	  //    echo $DOM->saveHtml($node), PHP_EOL;
	      $array[] = $found_a->getAttribute('href');        
	  }
	  return $array;
	}
	function getAllImages(DOMDocument $thisDOM) {
	  $imgs = $thisDOM->getElementsByTagName('img');
	  foreach($imgs as $img){
	      $array[] = $img->getAttribute('src');
	  }
	  return $array;
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

}
$stuff = new OurStuff();
// var_dump($stuff);
// echo "begin<br />" . array_pop($stuff->all_found);
// $stuff = new OurStuff;

$spider = new Crawler();
$spider->crawl($stuff);

// foreach ($stuff->all_found as $key) {
// 	echo "url = $key";
// }