<?php
ini_set("memory_limit", "132M");
//error_reporting(0); // Set E_ALL for debuging

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

/**
 * # Dropbox volume driver need "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
 * * dropbox-php: http://www.dropbox-php.com/
 * * PHP OAuth extension: http://pecl.php.net/package/oauth
 * * PEAR's HTTP_OAUTH package: http://pear.php.net/package/http_oauth
 *  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
 */
// Required for Dropbox.com connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';

// Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
// define('ELFINDER_DROPBOX_META_CACHE_PATH',''); // optional for `options['metaCachePath']`

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$ds = DIRECTORY_SEPARATOR;
// $documentroot = str_replace($ds.'mg-core'.$ds.'script'.$ds.'elfinder'.$ds.'php','',dirname(__FILE__)).$ds; 

$url = SITE.'/uploads';
if($_REQUEST['dir']=='template'){
  $url = SITE.'/mg-templates';
  $path = SITE_DIR.'mg-templates';
}
if($_REQUEST['dir']=='uploads'){
  $url = SITE.'/uploads';
  $path = SITE_DIR.'uploads';  
}
if($_REQUEST['dir']=='temp'){
	$url = SITE.DS.TEMP_DIR;
	$path = SITE_DIR.TEMP_DIR;  
}

$opts = array(
	// 'debug' => true,
	'roots' => array(
		  array(
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => $path,         // path to files (REQUIRED)
			'URL'           => $url, // URL to files (REQUIRED)
			'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
     	    'mimeDetect'    => "internal",
		)
	)
);

if (CREATE_TMB == '0') {
	$opts['roots'][0]['tmbPath'] = '';
	$opts['roots'][0]['quarantine'] = '';
}

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

