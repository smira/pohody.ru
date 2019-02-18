<?php

function nav_link($params)
{
	global $_GET, $_SERVER;
	$get = $_GET;
	while (list($key, $value) = each($params))
		if ($value === false)
			unset($get[$key]);
		else
			$get[$key] = $value;
	$a = $_SERVER["SCRIPT_NAME"];
	for ($i = 0; list($key, $value) = each($get); $i++)
		if (is_array($value))
			foreach($value as $k => $val)
				$a .= ($i++?"&":"?").$key.urlencode('['.$k.']')."=".urlencode($val);
		else
			$a .= ($i?"&":"?").$key."=".urlencode($value);
	return $a;
}

function nav_link_ext($url, $params)
{
	$a = $url;
	for ($i = 0; list($key, $value) = each($params); $i++)
		if ($value !== false)
			$a .= ($i?"&":"?").$key."=".urlencode($value);
	return $a;
}

function nav_self()
{
	global $REQUEST_URI;
	return urlencode($REQUEST_URI);
}

function nav_get($variable, $type, $default = false)
{
	global $_GET;
	if (!isset($_GET[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $_GET[$variable];
	settype($r, $type);
	return $r;
}

function nav_get_list($variable, $list, $default = false,$use_default = false)
{
	global $_GET;
	if (!isset($_GET[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $_GET[$variable];
	if (!in_array($r, $list)) {
		if($use_default) 
			$r = $default;
		else
			std_error("incorrect GET-parameter: $variable");
	}
	return $r;
}

function nav_get_word($variable, $default = false)
{
	global $_GET;
	if (!isset($_GET[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $_GET[$variable];
	settype($r, "string");
	if (!preg_match("/^\w+$/", $r))
		std_error("incorrect GET-parameter: $variable");
	return $r;
}

function nav_post($variable, $type, $default = false)
{
	global $_POST;
	if (!isset($_POST[$variable]))
	{
		if ($default !== false) return $default;
		die("POST-parameter expected: $variable");
	}

	$r = $_POST[$variable];
	settype($r, $type);
	return $r;
}

function nav_cookie($variable, $type, $default = false)
{
	global $_COOKIE;
	if (!isset($_COOKIE[$variable]))
	{
		if ($default !== false) return $default;
		std_error("COOKIE expected: $variable");
	}

	$r = $_COOKIE[$variable];
	settype($r, $type);
	return $r;
}


?>
