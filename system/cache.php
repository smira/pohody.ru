<?

function cache_start($p_cache_dir, $p_cache_file = false, $p_cache_gzip = false, $p_cache_timeout = false, $p_cache_time = false)
{
	global $cache_dir, $cache_file, $cache_gzip, $cache_timeout;
	$cache_dir = $p_cache_dir;
	$cache_file = $p_cache_file?$p_cache_dir.$p_cache_file.($p_cache_gzip?".gz":""):false;
	$cache_timeout = $p_cache_timeout;
	$cache_time = $p_cache_time;
	$cache_gzip = $p_cache_gzip;

	// already cached?
	if ($cache_file && file_exists($cache_file) && (!$cache_timeout || (time()-filemtime($cache_file) <= $cache_timeout)) && (!$cache_time || filemtime($cache_file) >= $cache_time))
	{
		if ($cache_gzip)
			header("Content-Encoding: gzip");
		$f = @@fopen($cache_file, "rb");
		if ($f !== false)
			fpassthru($f);
		exit();
	}

	// not cached, but be ready to cache!
	if ($cache_file || $cache_gzip)
	{
		ob_start(); ob_implicit_flush(0);
		std_register_finish(cache_finish);
	}
}

function cache_finish()
{
	global $cache_dir, $cache_file, $cache_gzip;

	if ($cache_gzip)
	{
		$content = ob_get_contents();
		ob_end_clean();
		$content = "\x1f\x8b\x08\x00\x00\x00\x00\x00".gzcompress($content, 9);
		header("Content-Encoding: gzip");
		echo $content;
	}
	else if ($cache_file)
	{
		$content = ob_get_contents();
		ob_end_flush();
	}
	if ($cache_file)
	{
		std_mkdir($cache_dir, 0755);
		$fname = tempnam($cache_dir, "");
		std_mkdir(dirname($cache_file), 0755);
		$f = @@fopen($fname, "wb");
		if ($f !== false)
		{
			fwrite($f, $content);
			fclose($f);
		}
		@@unlink($cache_file);
		@@rename($fname, $cache_file);
	}
}


$script = array(
	"default"=>array("dir"=>"cache/", "time_limit"=>100, "location"=>"manydirs"),
	"/script.phtml"=>array("params"=>array("id"=>true, "page"=>false), "type"=>"everyday")
);



////////////////////////////////////////////////////////////
// string cache_check(array params)
// Функция проверяет, позволяют ли GET-параметры текущей страницы кэшировать (или читать из кэша) запрос.
// params - ассоциативный массив, описывающий параметры текущей страницы.
// Пример:
// array("id"=>true, "page"=>true, "search"=>false)
// Это означает, что страница будет закэширована и имя файла-кэша будет содержать поля id и page. Но кэширование
// будет производиться только если ОТСУТСТВУЕТ GET-параметр "search". Так же очень важно, что все остальные параметры
// будут игнорироваться, т.е. с точки зрения кэширования страницы a.phtml?id=12 и a.phtml?id=12&fuck=off идентичны.
// Проверка значений осуществляется по рег. выражению $regexp (по умолчанию, "/^\w+$/").
// Функция возвращает false в случае, если кэширование невозможно, и строку с зашифрованными параметрами, если возможно.
function cache_check_params($params, $regexp = false)
{
	if ($regexp === false)
		$regexp = "/^\w+$/";

	$r = "";
	if (!is_array($params))
		return false;
	foreach ($params as $key=>$allow)
		if (!$allow)
		{
			if (isset($_GET[$key]))
				return false;
		}
		else {
			$value = $_GET[$key];
			if (isset($value))
			{
				if (!preg_match($regexp, $value))
					return false;
				if ($r)
					$r .= "&";
				$r .= $key."=".$value;
			}
		}
	return $r;
}

function cache_control($cache_info, $cache_gzip, $cachefile_appendix = "")
{
	$cache_time = false;
	$cache_timeout = false;
	$cache_file = false;
	$cache_dir = false;

	if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($cache_info[$_SERVER['SCRIPT_NAME']]))
	{
		$cache_info = isset($cache_info["default"])?array_merge($cache_info["default"], $cache_info[$_SERVER['SCRIPT_NAME']]):$cache_info[$_SERVER['SCRIPT_NAME']];
		$params_line = cache_check_params(&$cache_info["params"]);
		if ($params_line !== false)
		{
			if (!$params_line)
				$params_line = "!no-params";

			$period = $cache_info["period"];
			if (!isset($period))
				$period = 0;

			switch ($cache_info["type"])
			{
			case "everyday": $cache_time = mktime(0, 0, 0) + $period; break;
			case "everyweek": $now = getdate(); $cache_time = mktime(0, 0, 0, $now["mon"], $now["mday"]-($now["wday"]+6)%7) + $period; break;
			case "everymonth": $now = getdate(); $cache_time = mktime(0, 0, 0, $now["mon"], 1) + $period; break;
			case "timeout":
			default:
				$cache_timeout = $period;
			}
			switch ($cache_info["location"])
			{
			case "onefile": $cache_file = str_replace("/", "-", substr($_SERVER['SCRIPT_NAME'], 1)).$cachefile_appendix; break;
			case "manydirs": $cache_file = substr($_SERVER['SCRIPT_NAME'], 1).$cachefile_appendix."/".$params_line; break;
			case "root": $cache_file = str_replace("/", "-", substr($_SERVER['SCRIPT_NAME'], 1)).$cachefile_appendix.".".$params_line; break;
			case "onedir":
			default:
				$cache_file = str_replace("/", "-", substr($_SERVER['SCRIPT_NAME'], 1)).$cachefile_appendix."/".$params_line;
			}
		}
		if ($cache_info["local"])
			header("Cache-Control: max-age=".$cache_info["local"]);
		$cache_dir = $cache_info["dir"];
	}
	else {
		if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($cache_info["default"]["local"]))
			header("Cache-Control: max-age=".$cache_info["default"]["local"]);
		$cache_dir = $cache_info["default"]["dir"];
	}
	cache_start($cache_dir, $cache_file, $cache_gzip && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip"), $cache_timeout, $cache_time);
}


?>
