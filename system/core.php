<?php

header("Content-type: text/html; charset=utf-8");

require('system/std.php');
require('system/nav.php');
require('system/mysql.php');
require('system/cache.php');
require('system/settings.php');
require('system/templates.php');

$cache = array(
	"default"		=> array("dir"=>"cache/", "time_limit"=>100, "location"=>"manydirs", "type"=>"timeout","period"=>"36000"),
	"/index.php"		=> array("params"=>array("action"=>false), "period"=>"600", "location"=>"onefile"),
	"/photo.php"		=> array("params"=>array("fav"=>true, "travel"=>true, "page"=>true, "id"=>true)),
	"/travels.php"	=> array("params"=>array("id"=>true, "doc"=>true)),
	"/people.php"		=> array("params"=>array("id"=>true)),
	"/rivers.php"		=> array("params"=>array("id"=>true)),
	"/articles.php"	=> array("params"=>array("id"=>true, "page"=>true)),
);

if ($_SERVER["REMOTE_ADDR"] != "127.0.0.1")
	cache_control($cache, true);


$con = new mysql_con($mysql_options['database'], $mysql_options['user'],
                     $mysql_options['password'], $mysql_options['host']);

// seed with microseconds
function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

mt_srand(make_seed());

?>
