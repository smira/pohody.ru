<?php
/*
 * std - Функции базового назначения
 * Не требует других модулей.
 */

/**
 * std_redirect()
 * Редиректит на другую страницу и прекращает выполнение php-скрипта. std_finish не вызывает
 * @param $location url для редиректа
 */
function std_redirect($location)
{
	header("Location: ".$location);
	exit;
}

/**
 * std_rand_value()
 * Возвращает псевдослучайное очень длинное число (25 цифр).
 * @return псевдослучайное число
 */
function std_rand_value()
{
	return mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999).mt_rand(10000, 99999);
}

/**
 * std_error()
 * Стандартная функция для обработки фатального сообщения об ошибке. Если существует функция error(),
 * вызывает ее, передавая ей свой параметр - текстовое сообщение. После этого завершает работу скрипта.
 * @param $msg сообщение об ошибке
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
 * Выполняет квотинг тэгов с помощью функции htmlspecialchars.
 * @param $arr либо строка, массив строк (тогда обрабатываются все строки)
 * @return результирующий массив
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
 * Функция позволяет выполнять некий код только один раз. Использует метод флажка ;-)
 * @param $key уникальный ключ
 * @return true, если с данным ключом функция вызвана в первый раз; все последующие разы - false
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
 * Функция регистрирует некую callback-функцию, принимающую один произвольный параметр, в очереди
 * функций, завершающих работу скрипта. Все эти функции выполняются ТОЛЬКО если скрипт вызовет функцию
 * std_finish(). Таким образом, в случае, например, ошибки эти функции не выполняются. Что важно для
 * кэширования.
 * @param $key уникальный ключ
 * @return true, если с данным ключом функция вызвана в первый раз; все последующие разы - false
 */
$std_finish = array();
function std_register_finish($func)
{
	global $std_finish;
	$std_finish[] = $func;
}

/**
 * std_register_finish()
 * Выполняет в обратном порядке фунции, зарегистрированные функцией std_register_finish() и очищает
 * очередь.
 * @param $param необязательный параметр, передаваемый каждой функции
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
 * Проверяет указанный путь, создавая недостающие директории.
 * @param $path путь, который необходимо проверить/создать
 * @param $mode mode для вновь создаваемых директорий
 * @return true, если путь правильный или если удалось создать недостающие директории; false, если
 * создать директории не удалось
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
 * Открывает файл, создавая недостающие директории.
 * @param $fname имя файла
 * @param $mode режим открытия файла
 * @param $dir_mode mode для вновь создаваемых директорий
 * @return дескриптор файла, если открыть файл удалось; иначе false
 */
function std_fopen($fname, $mode, $dir_mode)
{
	// первая попытка; если удачно, возвращаем дескриптор
	$f = @fopen($fname, $mode);
	if ($f !== false)
		return $f;

	// неудачно; ищем последний слэш, чтобы определить директорию; если слэша нет, значит
	// файл нельзя открыть в связи с отсутствием прав
	$p = strrpos($fname, "/");
	if ($p === false)
		return false;

	// воссоздаем путь; если директория существует, есть мизерный шанс, что она была создана
	// только что, сразу после первой попытки открытия файла
	$path = substr($fname, 0, $p);
	if (!is_dir($path) && !std_mkdir($path, $dir_mode))
		return false;

	// ну усе, последняя попытка
	$f = @fopen($fname, $mode);
	return $f;
}

/**
 * std_rename()
 * Переименовывает файл, создавая недостающие директории.
 * @param $oldname старое имя файла
 * @param $newname новое имя
 * @param $dir_mode mode для вновь создаваемых директорий
 * @return true - ОК, false - плохо дело!
 */
function std_rename($oldname, $newname, $dir_mode)
{
	// первая попытка; если удачно, возвращаем дескриптор
	$r = @rename($oldname, $newname);
	if ($r)
		return true;

	// неудачно; ищем последний слэш, чтобы определить директорию; если слэша нет, значит
	// файл нельзя открыть в связи с отсутствием прав
	$p = strrpos($newname, "/");
	if ($p === false)
		return false;

	// воссоздаем путь; если директория существует, есть мизерный шанс, что она была создана
	// только что, сразу после первой попытки открытия файла
	$path = substr($newname, 0, $p);
	if (!is_dir($path) && !std_mkdir($path, $dir_mode))
		return false;

	// ну усе, последняя попытка
	$r = @rename($oldname, $newname);
	return $r;
}

?>