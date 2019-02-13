<?

function nav_link($params)
{
	global $HTTP_GET_VARS, $HTTP_SERVER_VARS;
	$get = $HTTP_GET_VARS;
	while (list($key, $value) = each($params))
		if ($value === false)
			unset($get[$key]);
		else
			$get[$key] = $value;
	$a = $HTTP_SERVER_VARS["SCRIPT_NAME"];
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
	global $HTTP_GET_VARS;
	if (!isset($HTTP_GET_VARS[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $HTTP_GET_VARS[$variable];
	settype($r, $type);
	return $r;
}

function nav_get_list($variable, $list, $default = false,$use_default = false)
{
	global $HTTP_GET_VARS;
	if (!isset($HTTP_GET_VARS[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $HTTP_GET_VARS[$variable];
	if (!in_array($r, $list)) {
		if($use_default) 
			$r = $default;
		else
			std_error("incorrect GET-parameter: $variable");
	}
	return $r;
}

/*
function nav_get_callback($variable, $callback, $default = false)
{
	global $HTTP_GET_VARS;
	if (!isset($HTTP_GET_VARS[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $HTTP_GET_VARS[$variable];
	if (!$callback(&$r))
		std_error("invalid GET-parameter: $variable");
	return $r;
}
*/
function nav_get_word($variable, $default = false)
{
	global $HTTP_GET_VARS;
	if (!isset($HTTP_GET_VARS[$variable]))
	{
		if ($default !== false) return $default;
		std_error("GET-parameter expected: $variable");
	}

	$r = $HTTP_GET_VARS[$variable];
	settype($r, "string");
	if (!preg_match("/^\w+$/", $r))
		std_error("incorrect GET-parameter: $variable");
	return $r;
}

function nav_post($variable, $type, $default = false)
{
	global $HTTP_POST_VARS;
	if (!isset($HTTP_POST_VARS[$variable]))
	{
		if ($default !== false) return $default;
		die("POST-parameter expected: $variable");
	}

	$r = $HTTP_POST_VARS[$variable];
	settype($r, $type);
	return $r;
}

function nav_cookie($variable, $type, $default = false)
{
	global $HTTP_COOKIE_VARS;
	if (!isset($HTTP_COOKIE_VARS[$variable]))
	{
		if ($default !== false) return $default;
		std_error("COOKIE expected: $variable");
	}

	$r = $HTTP_COOKIE_VARS[$variable];
	settype($r, $type);
	return $r;
}


?>
