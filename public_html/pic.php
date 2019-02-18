<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');

$id = nav_get("id", "integer");

$title = htmlspecialchars($con->property("SELECT description FROM photos WHERE id = $id"));

$text = "<p style='text-align: center; font-size: 14px'><img src='/images/".img_name($id)."'><br>".
     "<a href='javascript:window.close()'>Закрыть окно</a></p>";

?>
<html>
<head>
<title><?=$site_title?> &gt;&gt; <?=$s_photos?> &gt;&gt; <?=$title?></title>
<link rel="stylesheet" type="text/css" href="main.css" />
</head>
<body>
<?php block($title, $text) ?>
</body>
</html>
