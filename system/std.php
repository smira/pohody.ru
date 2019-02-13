<?php
/*
 * std - ������� �������� ����������
 * �� ������� ������ �������.
 */

/**
 * std_redirect()
 * ���������� �� ������ �������� � ���������� ���������� php-�������. std_finish �� ��������
 * @param $location url ��� ���������
 */
function std_redirect($location)
{
	header("Location: ".$location);
	exit;
}

/**
 * std_rand_value()
 * ���������� ��������������� ����� ������� ����� (25 ����).
 * @return ��������������� �����
 */
function std_rand_value()
{
	return mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999);
}

/**
 * std_error()
 * ����������� ������� ��� ��������� ���������� ��������� �� ������. ���� ���������� ������� error(),
 * �������� ��, ��������� �� ���� �������� - ��������� ���������. ����� ����� ��������� ������ �������.
 * @param $msg ��������� �� ������
 */
function std_error($msg)
{
	if (function_exists("error"))
		error($msg);
	else
		echo "$msg";
	die();
}

/**
 * std_html()
 * ��������� ������� ����� � ������� ������� htmlspecialchars.
 * @param $arr ���� ������, ������ ����� (����� �������������� ��� ������)
 * @return �������������� ������
 */
function std_html($arr)
{
	if ($arr === false)
		return false;
	if (is_array($arr))
	{
		foreach ($arr as $key=>$value)
			$arr[$key] = htmlspecialchars($value);
		return $arr;
	}
	return htmlspecialchars($arr);
}

/**
 * std_once()
 * ������� ��������� ��������� ����� ��� ������ ���� ���. ���������� ����� ������ ;-)
 * @param $key ���������� ����
 * @return true, ���� � ������ ������ ������� ������� � ������ ���; ��� ����������� ���� - false
 */
$std_once = array();
function std_once($key)
{
	global $check_once;
	if (isset($check_once[$key]))
		return false;
	return $check_once[$key] = true;
}

/**
 * std_register_finish()
 * ������� ������������ ����� callback-�������, ����������� ���� ������������ ��������, � �������
 * �������, ����������� ������ �������. ��� ��� ������� ����������� ������ ���� ������ ������� �������
 * std_finish(). ����� �������, � ������, ��������, ������ ��� ������� �� �����������. ��� ����� ���
 * �����������.
 * @param $key ���������� ����
 * @return true, ���� � ������ ������ ������� ������� � ������ ���; ��� ����������� ���� - false
 */
$std_finish = array();
function std_register_finish($func)
{
	global $std_finish;
	$std_finish[] = $func;
}

/**
 * std_register_finish()
 * ��������� � �������� ������� ������, ������������������ �������� std_register_finish() � �������
 * �������.
 * @param $param �������������� ��������, ������������ ������ �������
 */
function std_finish($param = false)
{
	global $std_finish;
	for ($i = sizeof($std_finish)-1; $i >= 0; $i--)
		$std_finish[$i]($param);
	$std_finish = array();
}

/**
 * std_mkdir()
 * ��������� ��������� ����, �������� ����������� ����������.
 * @param $path ����, ������� ���������� ���������/�������
 * @param $mode mode ��� ����� ����������� ����������
 * @return true, ���� ���� ���������� ��� ���� ������� ������� ����������� ����������; false, ����
 * ������� ���������� �� �������
 */
function std_mkdir($path, $mode)
{
	if (!$path)
		return false;
	if ($path[strlen($path)-1] != "/")
		$path .= "/";

	for ($i = 1; ($i = strpos($path, "/", $i)) !== false; $i++)
	{
		$dir = substr($path, 0, $i);
		if (!is_dir($dir) && !@mkdir($dir, $mode))
			return false;
	}
	return true;
}

/**
 * std_fopen()
 * ��������� ����, �������� ����������� ����������.
 * @param $fname ��� �����
 * @param $mode ����� �������� �����
 * @param $dir_mode mode ��� ����� ����������� ����������
 * @return ���������� �����, ���� ������� ���� �������; ����� false
 */
function std_fopen($fname, $mode, $dir_mode)
{
	// ������ �������; ���� ������, ���������� ����������
	$f = @fopen($fname, $mode);
	if ($f !== false)
		return $f;

	// ��������; ���� ��������� ����, ����� ���������� ����������; ���� ����� ���, ������
	// ���� ������ ������� � ����� � ����������� ����
	$p = strrpos($fname, "/");
	if ($p === false)
		return false;

	// ���������� ����; ���� ���������� ����������, ���� �������� ����, ��� ��� ���� �������
	// ������ ���, ����� ����� ������ ������� �������� �����
	$path = substr($fname, 0, $p);
	if (!is_dir($path) && !std_mkdir($path, $dir_mode))
		return false;

	// �� ���, ��������� �������
	$f = @fopen($fname, $mode);
	return $f;
}

/**
 * std_rename()
 * ��������������� ����, �������� ����������� ����������.
 * @param $oldname ������ ��� �����
 * @param $newname ����� ���
 * @param $dir_mode mode ��� ����� ����������� ����������
 * @return true - ��, false - ����� ����!
 */
function std_rename($oldname, $newname, $dir_mode)
{
	// ������ �������; ���� ������, ���������� ����������
	$r = @rename($oldname, $newname);
	if ($r)
		return true;

	// ��������; ���� ��������� ����, ����� ���������� ����������; ���� ����� ���, ������
	// ���� ������ ������� � ����� � ����������� ����
	$p = strrpos($newname, "/");
	if ($p === false)
		return false;

	// ���������� ����; ���� ���������� ����������, ���� �������� ����, ��� ��� ���� �������
	// ������ ���, ����� ����� ������ ������� �������� �����
	$path = substr($newname, 0, $p);
	if (!is_dir($path) && !std_mkdir($path, $dir_mode))
		return false;

	// �� ���, ��������� �������
	$r = @rename($oldname, $newname);
	return $r;
}

?>