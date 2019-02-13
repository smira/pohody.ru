<?

/*	
	Description    : Форум. Полужабаскриптовый со статическими копиями сообщений в виде JScript'ов.
	
	Revisions      : xx-xx-xxxx  | Pavel Savich         | Вроде что-то навоял
*/

/*

CREATE TABLE forum (
	id             INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	parent_id      INT,
	forum_id       INT,
	topic_id       INT,
	descendants    INT NOT NULL DEFAULT '0',
	children       INT NOT NULL DEFAULT '0',
	last_answer_id INT,
	posted         DATETIME NOT NULL,
	updated        DATETIME NOT NULL,
	level          TINYINT NOT NULL DEFAULT '0',
	notify_answers TINYINT NOT NULL DEFAULT '0',
	ip             VARCHAR(15),

	title          VARCHAR(255),
	body           TEXT,
	author         INT,
	author_name    VARCHAR(64),
	author_mail    VARCHAR(128),
	ro	       TINYINT( 1 ) UNSIGNED NOT NULL,

	INDEX (parent_id),
	INDEX (forum_id),
	INDEX (topic_id),
	INDEX (updated)
);

Терминология.
* сообщение (message) - любое сообщение
* форум (forum) - корневое сообщение, не имеющее родителя
* тема (topic) - сообщение, родителем которого является форум

Обязательные поля сообщений.
1. id - уникальный id сообщения
2. parent_id - id родительского сообщения (кроме форума)
3. forum_id - id форума (кроме форума; т.к. при создании форума его ID еще неизвестен)
4. topic_id - id топика (кроме форума и самого топика; т.к. при создании топика его ID еще неизвестен)
5. descendants - общее количество потомков у сообщения
6. children - количество непосредственных детей у сообщения
7. last_answer_id - id последнего сообщения
8. posted - время постинга (UNIX_TIMESTAMP)
9. updated - время последнего обновления дерева (последнего постинга)
10. level - уровень сообщения (0 - форум, 1 - тема, 2 - ответ на тему и т.д.)

Необязательные поля.
Стандартными являются необязательные поля:
1. title - для форума это эхотэг; для остальных сообщений - subj
2. body - тело сообщения

*/

class qforum
{

/**
 * $con
 * Соединение
 */
var $con;

/**
 * $table
 * Таблица БД, в которой хранится информация о соединении.
 */
var $table;

/**
 * $draw
 * Коллбэк-функция для вывода сообщения в списке void draw($msg), где $msg - тело сообщения.
 */
var $draw;

/**
 * $draw_prefix, $draw_suffix
 * Префикс и суффикс для вывода дерева сообщений в списке (открытие и закрытие списков) - строки.
 */
var $draw_prefix, $draw_suffix;

/**
 * $fields
 * Список необязательных полей, необходимых при выводе дерева сообщений. Через запятую.
 */
var $fields;

/**
 * $msg_dir
 * Директория (относительно корня сайта), в которой хранятся js-сообщения форума
 */
var $msg_dir;

/**
 * $forums_draw
 * Коллбэк-функция для вывода информации о форуме в списке форумов
 * void forums_draw($msg, $n), где $msg - тело сообщения, $n - порядковый номер форума в списке.
 */
var $forums_draw;

/**
 * $forums_draw_prefix, $forums_draw_suffix
 * Префикс и суффикс для вывода списка форумов. Без параметров.
 */
var $forums_draw_prefix, $forums_draw_suffix;

/**
 * $max_line
 * Максимальная длина неквотированной строки.
 */
var $max_line;

/**
 * $path
 * Полный путь к скрипту форума (внутри модуля не используется)
 */
var $path;

/**
 * qforum
 * Конструктор
 * @param $con коннект к БД
 */
function qforum(&$con)
{
	$this->con = &$con;
	$this->table = "forum";
	$this->draw_prefix = "<ul>\n";
	$this->draw_suffix = "</ul>\n";
	$this->fields = "title, UNIX_TIMESTAMP(posted) AS posted, author_name, author_mail, ro";
	$this->forums_fields = "title, UNIX_TIMESTAMP(updated) AS updated, author_name, author_mail, body, ro";
	$this->max_line = 40;
}



/**
 * new_forum()
 * Создает новый форум.
 * @param $msg собственно, само добавляемое сообщение
 *     Обязательных полей нет.
 *     Поля, заполняемые автоматически в случае, если они не указаны:
 *     1. id - id сообщения (это поле заполняется автоматически всегда, стирая старое значение)
 *     2. posted - время постинга
 *     3. updated - время последнего апдейда (для нового сообщения = posted)
 *     4. ip - IP написавшего сообщение
 * @return id сообщения, если сообщение успешно добавлено; иначе false
 */
function new_forum(&$msg)
{
	// инициализация
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// автоматические поля
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = 0;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// добавление сообщения в базу
	$msg["id"] = $id = $this->con->set($this->table, &$msg);

	// создаем статическую копию
	$this->create_js_message($msg);

	return $id;
}

/**
 * new_topic()
 * Постит новый топик. Функция сама обновляет поля descendants, children,
 * updated у корневого сообщения форума.
 * @param $forum_id id форума, куда добавляется топик
 * @param $msg собственно, само добавляемое сообщение
 *     Обязательных полей нет.
 *     Поля, заполняемые автоматически в случае, если они не указаны:
 *     1. id - id сообщения (это поле заполняется автоматически всегда, стирая старое значение)
 *     2. forum_id - id форума, куда постится сообщение
 *     3. parent_id - id родителя, куда постится сообщение (= forum_id)
 *     4. posted - время постинга
 *     5. updated - время последнего апдейда (для нового сообщения = posted)
 *     6 ip - IP написавшего сообщение
 * @return id сообщения, если сообщение успешно добавлено; иначе false
 */
function new_topic($forum_id, &$msg)
{
	// все ли ОК?
	if (!$forum_id)
		return false;
	if ($this->con->property("SELECT parent_id FROM ".$forum->table." WHERE id = ".$forum_id))
		return false;

	// инициализация
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// автоматические поля
	if (!isset($msg["forum_id"]))
		$msg["forum_id"] = $forum_id;
	if (!isset($msg["parent_id"]))
		$msg["parent_id"] = $forum_id;
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = 1;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// добавление сообщения в базу
	$msg["id"] = $id = $this->con->set($this->table, &$msg);
	if ($id === false)
		return false;

	// обновление корневого сообщения
	$this->con->update("UPDATE ".$this->table." SET children = children+1, descendants = descendants+1, updated = '".$now."' WHERE id = ".$forum_id);

	// создаем статическую копию
	$this->create_js_message($msg);
	// обновляем дерево
	$this->create_js_tree($msg["forum_id"], $msg["id"]);

	return $id;
}

/**
 * new_answer()
 * Постит новое сообщение-ответ в форум. Функция сама обновляет поля descendants, children,
 * updated у родительских сообщений.
 * @param $msgs структура сообщений (если это ответ)
 * @param $ids структура, позволяющая по id сообщения получить его индекс в $msgs (возвращается read_topic)
 * @param $msg собственно, само добавляемое сообщение
 *     Обязательные поля:
 *     1. parent_id - id родительского сообщения
 *     Поля, заполняемые автоматически в случае, если они не указаны:
 *     1. id - id сообщения (это поле заполняется автоматически всегда, стирая старое значение)
 *     2. forum_id - id форума, куда постится сообщение
 *     3. topic_id - id топика
 *     4. posted - время постинга
 *     5. updated - время последнего апдейда (для нового сообщения = posted)
 *     5. ip - IP написавшего сообщение
 * @return id сообщения, если сообщение успешно добавлено; иначе false
 */
function new_answer(&$msgs, &$ids, &$msg)
{
	// все ли ОК?
	$parent_id = $msg["parent_id"];
	if (!isset($parent_id) || !isset($msgs[$ids[$parent_id]]) || sizeof($msgs) < 2)
		return false;

	// инициализация
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// автоматические поля
	if (!isset($msg["forum_id"]))
		$msg["forum_id"] = $msgs[0]["id"];
	if (!isset($msg["topic_id"]))
		$msg["topic_id"] = $msgs[1]["id"];
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = $msgs[$ids[$parent_id]]["level"]+1;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// добавление сообщения в базу
	$msg["id"] = $id = $this->con->set($this->table, &$msg);
	if ($id === false)
		return false;

	// обновление предков сообщения
	$inc = "children = children+1, descendants = descendants+1, updated = '".$now."', last_answer_id = ".$id;
	while ($parent_id)
	{
		$this->con->update("UPDATE ".$this->table." SET ".$inc." WHERE id = ".$parent_id);
		
		$parent_id = $msgs[$ids[$parent_id]]["parent_id"];
		$inc = "descendants = descendants+1, updated = '".$now."', last_answer_id = ".$id;
	}

	// создаем статическую копию
	$this->create_js_message($msg);

	// обновляем дерево
	$this->create_js_tree($msg["forum_id"], $msg["topic_id"]);

	return $id;
}

/**
 * modify_message()
 * Вносит изменение в сообщение форума.
 * @param $id id изменяемого сообщения
 * @param $changes ассоциативный массив изменяемых полей
 * @param $moderated если true, устанавливает поле moderated равным текущей дате (если оно не установлено)
 * @return true - ок, false - ошибка
 */
function modify_message($id, &$changes, $moderated = false)
{
	// все ли ОК?
	if (!$id)
		return false;

	// инициализация
	unset($changes["id"]);

	// автоматические поля
	if ($moderation && !isset($msg["moderation_time"]))
		$msg["moderated"] = date("Y-m-d H:i:s");
	if (isset($changes["body"]))
		$changes["body"] = str_replace("\r", "", $changes["body"]);

	// обновление базы
	$this->con->set($this->table, &$changes, $id);

	// восстанавливаем jscript-версию
	$msg = $this->con->qquery("SELECT * FROM ".$this->table." WHERE id = ".$id);
	$this->create_js_message($msg);

	// обновляем дерево
	if ($msg["forum_id"])
		$this->create_js_tree($msg["forum_id"], $msg["topic_id"]?$msg["topic_id"]:$msg["id"]);

	return true;
}

/**
 * delete_children()
 * Удаляет всех потомков сообщения; не обновляет системных полей у их родителей.
 * @access private
 * @param $id id родительского сообщения
 * @param $deleted ассоциативный массив, заполняемый id-шниками удаляемых сообщений
 * @return true - ок, false - ошибка
 */
function delete_children($id, &$deleted)
{
	if (!$id) return;
	$deleted[$id] = true;

	// разбираемся с детьми
	$r = $this->con->query("SELECT id FROM ".$this->table." WHERE parent_id = ".$id);
	while (list($child) = $r->fetch_row())
		$this->delete_children($child, &$deleted);

	if ($r->num_rows())
		$this->con->query("DELETE FROM ".$this->table." WHERE parent_id = ".$id);

	$r->free();
}

/**
 * delete_message()
 * Удаляет сообщение из форума. Дети тоже удаляются.
 * @param $id id удаляемого сообщения
 * @return true - ок, false - ошибка
 */
function delete_message($id)
{
	// все ли ОК?
	if (!$id)
		return false;
	
	$parent_id = $this->con->property("SELECT parent_id FROM ".$this->table." WHERE id = ".$id);


	// разбираемся с детьми
	$deleted = array();
	$this->delete_children($id, &$deleted);
	$this->con->query("DELETE FROM ".$this->table." WHERE id = ".$id);

	if ($moderation && !isset($msg["moderation_time"]))
		$msg["moderated"] = date("Y-m-d H:i:s");

	$m_topic_id = false;
	$m_forum_id = false;

	// обновление предков
	$desc = "descendants = descendants-".sizeof($deleted).", children = children-1";
	while ($parent_id)
	{
		list($next_parent_id, $last_answer_id, $topic_id) = $this->con->qquery("SELECT parent_id, last_answer_id FROM ".$this->table." WHERE id = ".$parent_id);
		$this->con->update("UPDATE ".$this->table." SET ".$desc.($deleted[$last_answer_id]?", last_answer_id = NULL":"")." WHERE id = ".$parent_id);

		if (!$topic_id && $next_parent_id)
			$m_topic_id = $parent_id;
		if (!$topic_id && !$next_parent_id)
			$m_forum_id = $parent_id;

		$parent_id = $next_parent_id;
		$desc = "descendants = descendants-".sizeof($deleted);
	}

	// обновляем дерево
	if ($m_forum_id && $m_topic_id)
		$this->create_js_tree($m_forum_id, $m_topic_id);

	return true;
}

/**
 * read()
 * Считывает дерево сообщений.
 * @param $forum_id id форума; если false, считывается список форумов (только корневые сообщения)
 * @param $topic_id id топика, который необходимо считать; если этот параметр НЕ false, считывает ТОЛЬКО
 * топик с данным id + корневое сообщение форума
 * @return array($msgs, $children, $ids)
 *     1. $msgs - массив сообщений (не ассоциативный), упорядоченный по дате постинга;
 *         0 - корневое сообщение форума; 1 - топик... далее все дети и их дети
 *     2. $children - массив детей; для каждого сообщения из $msgs, имеющего детей,
 *     содержит массив собственно детей; дети отсортированы по дате постинга; ключ - порядковый номер
 *     сообщения в $msgs
 *     3. $ids - ассоциативный массив; ключи - id сообщений; значения - порядковые номера этих сообщений в
 *     массиве $msgs
 */
function read($forum_id = false, $topic_id = false)
{
	$main_fields = "id, parent_id, forum_id, topic_id, last_answer_id, descendants, children, level";

	$msgs = array();
	$children = array();
	$ids = array();

	if ($forum_id !== false)
	{
		$all_fields = $main_fields.", ".$this->fields;
		$res = $this->con->query("SELECT ".$all_fields." FROM ".$this->table." WHERE id = ".$forum_id.($topic_id===false?" OR forum_id = ".$forum_id:" OR id = ".$topic_id." OR topic_id = ".$topic_id)." ORDER BY id");
	}
	else {
		$all_fields = $main_fields.", ".$this->forums_fields;
		$res = $this->con->query("SELECT ".$all_fields." FROM ".$this->table." WHERE parent_id IS NULL ORDER BY id");
	}

	for ($i = 0; $r = $res->fetch_array(); $i++)
	{
		$r["@path"] = $this->message_path($r);
		$msgs[] = $r;
		$ids[$r["id"]] = $i;
		if ($r["parent_id"])
		{
			$parent = $ids[$r["parent_id"]];
			if (isset($parent))
				$children[$parent][] = $i;
		}
	}
	$res->free();
	return array($msgs, $children, $ids);
}

/**
 * topics()
 * Возвращает полный список id-шников топиков форума. В виде простого списка.
 * @param $forum_id id форума
 * @param $limit ограничение длины списка (не обязательный параметр)
 * @return массив id-шников топиков
 */
function topics($forum_id, $limit = false)
{
	return $this->con->query_list("SELECT id FROM ".$this->table." WHERE parent_id = ".$forum_id." ORDER BY updated DESC".($limit?" LIMIT ".$limit:""));
}

/**
 * read_message()
 * Возвращает нужные поля из нужного сообщения.
 * @param $id id сообщения
 * @param $fields список полей (через запятую)
 * @return массив значений полей
 */
function read_message($id, $fields)
{
	return $this->con->qquery("SELECT ".$fields." FROM ".$this->table." WHERE id = ".$id);
}

/**
 * html()
 * Выводит дерево сообщений.
 * @param $msgs массив сообщений (возвращаемый функцией forum_read)
 * @param $children массив детей
 * @param $i сообщение, потомков которого необходимо отобразить; по умолчанию - 0, т.е. форум; false
 * означает "выводить все подряд" (например, когда нужно вывести все форумы)
 * @return array($msgs, $children, $ids)
 *     1. $msgs - массив сообщений (не ассоциативный), упорядоченный по дате постинга
 *     2. $children - массив детей; для каждого сообщения из $msgs, имеющего детей,
 *     содержит массив собственно детей; дети отсортированы по дате постинга; ключ - порядковый номер
 *     сообщения в $msgs
 *     3. $ids - ассоциативный массив; ключи - id сообщений; значения - порядковые номера этих сообщений в
 *     массиве $msgs
 */
function html(&$msgs, &$children, $i = 0)
{
	$draw = $this->draw;
	$ch = &$children[$i];

	if ($i)
		echo $this->draw_prefix;
	for ($j = sizeof($ch)-1; $j>=0; $j--)
	{
		$ni = $ch[$j];
		$draw(&$msgs[$ni]);
		if ($children[$ni])
			$this->html($msgs, $children, $ni);
	}
	if ($i)
		echo $this->draw_suffix;
}

/**
 * html_forums()
 * Выводит список форумов.
 * @param $msgs массив сообщений (возвращ
 * @param $forum_topic id топика
 * @param $i сообщение, потомков которого необходимо отобразить; по умолчанию - 0, т.е. форум; false
 * означает "выводить все подряд" (например, когда нужно вывести все форумы)
 * @return array($msgs, $children, $ids)
 *     1. $msgs - массив сообщений (не ассоциативный), упорядоченный по дате постинга
 *     2. $children - массив детей; для каждого сообщения из $msgs, имеющего детей,
 *     содержит массив собственно детей; дети отсортированы по дате постинга; ключ - порядковый номер
 *     сообщения в $msgs
 *     3. $ids - ассоциативный массив; ключи - id сообщений; значения - порядковые номера этих сообщений в
 *     массиве $msgs
 */
function html_forums(&$msgs)
{
	$prefix = $this->forums_draw_prefix;
	$draw = $this->forums_draw;
	$suffix = $this->forums_draw_suffix;

	$prefix();
	foreach ($msgs as $j=>$msg)
		$draw(&$msg, $j);
	$suffix();
}

/**
 * message_path()
 * Вычисляет некий абстрактный "путь" для сообщения. Для форума это просто "$id", для темы - "$forum_id/$id",
 * для остальных сообщений - "$forum_id/$topic_id/$id"
 * @param $msg сообщение
 * @return вычисленный путь
 */
function message_path(&$msg)
{
	// Форум?
	if (!$msg["parent_id"])
		return $msg["id"];
	// Топик?
	if (!$msg["topic_id"])
		return $msg["forum_id"]."/".$msg["id"];
	// Ответ!
	return $msg["forum_id"]."/".$msg["topic_id"]."/".$msg["id"];
}

/**
 * create_js_message()
 * Создает htm-файл с js-дублем сообщения в форуме :-)
 * @param $msg сообщение
 * @return true, в случае успеха; иначе false
 */
function create_js_message(&$msg)
{
	$fname = $this->msg_dir."/".$this->message_path($msg).".htm";
	$f = std_fopen($fname, "w", 0775);
	if ($f === false)
		return false;
	$s = "<html>\n<head>\n";
	$s .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">\n";
	$s .= "<meta http-equiv=\"Cache-Control\" content=\"max-age: 31536000\">\n";
	$s .= "</head>\n";
	$s .= "<script>\n";
	$s .= "if (!window.parent || !window.parent.messageLoaded) document.location='/';\n";
	$s .= "else {\n";
	$s .= "  var msg = new Object;\n";

	foreach ($msg as $key=>$value)
		if (isset($this->static_fields[$key]))
			$s .= "  msg.".$key." = ".
				($this->static_fields[$key]?"\"".preg_replace('/<\/?script(.*?)>/i','',addcslashes($value, "\0..\37\""))."\"":$value).";\n";
	$s .= "  window.parent.messageLoaded(".$msg["id"].", msg)\n";
	$s .= "}\n";
	$s .= "</script>\n";
	$s .= "</html>\n";
	fwrite($f, $s);
	fclose($f);
	chmod($fname, 0664);
	return true;
}

/**
 * create_js_tree()
 * Создает js-файл с js-дублем сообщения в форуме :-)
 * @param $msg сообщение
 * @return true, в случае успеха; иначе false
 */
function create_js_tree($forum_id, $topic_id)
{
	list($msgs, $children, $ids) = $this->read($forum_id, $topic_id);

	ob_start();
	$this->html($msgs, $children);
//	$s = "document.write(\"".addcslashes(ob_get_contents(), "\0..\37\"")."\");";
	$s = ob_get_contents();
	ob_end_clean();

	std_mkdir($this->msg_dir."/".$forum_id."/tree", 0755);
	$fname = $this->msg_dir."/".$forum_id."/tree/".$topic_id.".htm";

	$temp = //tempnam($this->msg_dir."/", "tmp.");
                $this->msg_dir."/".$forum_id."/tree/".$topic_id.".htm2";
	$f = fopen($fname, "w");
	if ($f === false)
	   return false;


	fwrite($f, $s);
	fclose($f);
	//rename($temp, $fname);//, 0775);
	//chmod($fname, 0664);
	return true;
}

/**
 * walk_up()
 * Прогуливается вверх по дереву, вызывая callback-функцию
 * @param $id элемент, с которого начинается обход
 * @param $fields SQL-поля, которые нужно выбирать; через запятую (обязательно parent_id)
 * @param $callback callback функция void callback($msg)
 * @return true, в случае успеха; иначе false
 */
function walk_up($id, $fields, $callback)
{
	$msg = $this->con->qquery("SELECT ".$fields." FROM ".$this->table." WHERE id = ".$id);
	if ($msg === false)
		return;

	$callback($msg);
	if ($msg["parent_id"])
		$this->walk_up($msg["parent_id"], $fields, $callback);
}

/**
 * js_priv_object()
 * Генерит JavaScript, создающий объект, дублирующий параметр $priv.
 * Пример:
 * js_priv_object("priv", array("pray"=>true, "love"=>true))
 *   => "var priv=new Object; priv.pray = true; priv.love = true;"
 * @param $name название JavaScript-переменной
 * @param $priv ассоциативный массив привилегий
 * @return true, в случае успеха; иначе false
 */
function js_priv_object($name, $priv)
{
	$r = "var ".$name." = new Object;\n";
	foreach ($priv as $key=>$value)
		$r .= $name.".".$key." = ".($value?"true":"false").";\n";
	return $r;
}

} // end of class


?>
