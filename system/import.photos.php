#!/usr/local/bin/php
<?

chdir('/home/pohody.ru');

require('system/mysql.php');
require('system/settings.php');

$con = new mysql_con($mysql_options['database'], $mysql_options['user'],
                     $mysql_options['password'], $mysql_options['host']);

if ($argc != 4)
{
	echo "Usage: import.photos.php <no_from> <no_to> <pohod_id>\n";
	die;
}

$no_from = (int)$argv[1];
$no_to = (int)$argv[2];
$pohod_id = (int)$argv[3];
$order = 50;

for ($i = $no_from; $i <= $no_to; $i++)
{
	if ($con->property("SELECT id FROM photos WHERE id = $i"))
	{
		echo "Photo $i already in DB\n";
		die;
	}
	$info1 = @getimagesize(sprintf('public_html/images/i%04d.jpg', $i));
	if (!$info1)
	{
		echo "Unable to get info about picture, $i\n";
		die;
	}
	$info2 = @getimagesize(sprintf('public_html/images/s%04d.jpg', $i));
	if (!$info1)
	{
		echo "Unable to get info about thumbnail, $i\n";
		die;
	}
	$con->insert("INSERT INTO photos SET id = $i, idpohod = ".($pohod_id ? $pohod_id : 'NULL').", ".
		     "width = {$info1[0]}, height = {$info1[1]}, thumb_width = {$info2[0]}, ".
		     "thumb_height = {$info2[1]}, order_ = $order");
	$order += 50;
}

?>
