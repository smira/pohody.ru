<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");

session_name("POHODYADMIN");

$default_section = 'people';
$rows_per_screen = 20;
$row_inactive = '#EFEFEF';
$row_active   = '#D0D0D0';
$popup_width  =  600;
$popup_height =  500;
$field_nochange = '<не изменять>';
$site_title = 'Pohody.Ru';
$yes_no = array('Нет', 'Да');

$section_includes = array(
			 );

require('system/settings.php');
require('system/mysql.php');
require('system/nav.php');
require('system/std.php');
require('system/form.php');
require('system/admin.php');

if ($_GET['section'] && $section_includes[$_GET['section']])
	require($section_includes[$_GET['section']]);

$con = new mysql_con($mysql_options['database'], $mysql_options['user'],
                     $mysql_options['password'], $mysql_options['host']);


$admin = array(
  'sections' => array(
                'people'   => array(
                             'title'  => 'Походники',
                             'view'   => 'people_view',
			     'priv'    => 'all',
                           ),
                'boats'    => array(
                             'title'  => 'Суда',
                             'view'   => 'boats_view',
			     'priv'    => 'all',
                           ),
                'rivers'   => array(
                             'title'  => 'Реки',
                             'view'   => 'rivers_view',
			     'priv'    => 'all',
                           ),
                'pohody'   => array(
                             'title'  => 'Походы',
                             'view'   => 'pohody_view',
			     'priv'    => 'all',
                           ),
		'documents' => array(
                             'title'  => 'Документы',
                             'view'   => 'documents_view',
			     'priv'    => 'all',
                           ),
		'comands'  => array(
                             'title'  => 'Экипажи',
                             'view'   => 'comands_view',
			     'priv'    => 'all',
                           ),
		'photos'   => array(
                             'title'  => 'Фотки',
                             'view'   => 'photos_view',
			     'priv'    => 'all',
                           ),
		'news'     => array(
                             'title'  => 'Новости',
                             'view'   => 'news_view',
			     'priv'    => 'all',
                           ),
                'users'    => array(
                             'title'  => 'Пользователи админства',
                             'view'   => 'user_view',
			     'priv'    => 'all',
                           ),
		),
  'views'    => array(
                'user_view'  => array(
                                  'table'   => 'user',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'name'     => 'username',
                                                  'password' => 'password',
                                                  'fullname' => 'fio_user',
                                                  'priv'     => 'userpriv',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'name'     => '30%',
                                                  'password' => '30%',
                                                  'priv' => '40%',
                                               ),
                                ),
                'news_view'  => array(
                                  'table'   => 'news',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'when_'    => 'when_',
                                                  'text'     => 'body',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'when_'    => '30%',
                                                  'text'     => '70%',
                                               ),
                                ),
                'people_view'  => array(
                                  'table'   => 'people',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'name'     => 'name',
                                                  'fullname' => 'fullname',
                                                  'description' => 'description',
                                                  'email'     => 'email',
                                                  'homepage'  => 'homepage',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'name'     => '20%',
                                                  'fullname' => '30%',
                                                  'description' => '50%',
                                               ),
                                ),
                'boats_view'   => array(
                                  'table'   => 'boats',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'name'     => 'name',
                                                  'type'     => 'type',
                                                  'number'   => 'number_people',
                                                  'description' => 'description',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'name'     => '25%',
                                                  'type'     => '25%',
                                                  'description' => '50%',
                                               ),
                                ),
                'rivers_view'   => array(
                                  'table'   => 'rivers',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'name'     => 'name',
                                                  'description' => 'description',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'name'     => '50%',
                                                  'description'     => '50%',
                                               ),
                                ),
                'pohody_view'   => array(
                                  'table'   => 'pohody',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
						  'shortname' => 'name',
                                                  'season'   => 'season',
                                                  'year'     => 'year',
                                                  'number'   => 'number',
                                                  'river'    => 'river',
                                                  'captain'  => 'captain',
						  'pohody_doc' => 'pohody_doc',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
						  'shortname'=> '20%',
                                                  'river'    => '20%',
                                                  'season'   => '20%',
                                                  'year'     => '20%',
                                                  'number'   => '20%',
                                               ),
                                ),
                'comands_view'   => array(
                                  'table'   => 'comands',
                                  'primary' => 'ekipazh',
                                  'fields'  => array(
                                                  'ekipazh'  => 'id',
                                                  'boat'     => 'boat_id',
                                                  'idpohod'  => 'pohod_id',
                                                  'ekipazh_people'   => 'ekipazh_people',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'ekipazh'  => 40,
                                                  'boat'     => '50%',
                                                  'idpohod'  => '50%',
                                               ),
                                ),
                'documents_view'   => array(
                                  'table'   => 'documents',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'title'    => 'title',
                                                  'body'     => 'body',
                                                  'idpeople' => 'author_id',
                                                  'author'   => 'author',
                                                  'author_email'   => 'email',
                                               ),
                                  'filters' => array(
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'title'    => '25%',
                                                  'body'     => '50%',
                                                  'author_id' => '25%',
                                               ),
                                ),
                'photos_view'     => array(
                                  'table'   => 'photos',
                                  'primary' => 'id',
                                  'fields'  => array(
                                                  'id'       => 'id',
                                                  'description' => 'title',
                                                  'idpohod'  => 'pohod_id2',
                                                  'idpara'   => 'idpara',
						  'idriver'  => 'river_id',
						  'idman'    => 'man_id',
                                                  'order_'   => 'order_',
                                                  'width'    => 'width',
                                                  'height'   => 'height',
                                                  'thumb_width'  => 'thumb_width',
                                                  'thumb_height' => 'thumb_height',
                                               ),
                                  'filters' => array(
                                                  'id'       => 'id',
                                                  'idpohod'  => 'pohod_id2',
						  'idriver'  => 'river_id',
						  'idman'    => 'man_id',
                                               ),
                                  'widths'  => array(
                                                  'id'       => 40,
                                                  'description' => '50%',
                                                  'idpohod'  => '10%',
                                                  'idpara'   => '10%',
						  'idriver'  => '10%',
						  'idman'    => '10%',
						  'order_'   => '10%',
                                               ),
                                ),
                ),
   'goto'    => array(

                ),
   'fields'  => array(
   		'id' 		=> array(
   		           		'title' => 'ID',
   		           		'type'  => 'auto',
   		           		'align' => 'center',
   		        	   ),
   		'username'	=> array(
   		 			'title' => 'Имя пользователя',
   		 			'type'  => 'text',
   		 			'size'  => '25',
   		 			'max'   => '25',
   		 			'required' => true,
   		 		   ),	
   		'password'      => array(
   		 			'title' => 'Пароль',
   		 			'type'  => 'text',
   		 			'func'  => 'md5',
   		 			'oneway'=> true,
   		 			'size'  => '25',
   		 			'min'   => '2',
   		 			'required' => true,
   		 		   ),
   		'userpriv'      => array(
   		 			'title' => 'Права',
   		 			'type'  => 'text',
   		 			'size'  => '70',
					'max'   => '512',
   		 		   ),
   		'fio_user'	=> array(
   		 			'title' => 'Фамилия, имя, отчество',
   		 			'type'  => 'text',
   		 			'hide'  => true,
   		 			'size'  => '60',
   		 			'max'   => '255',
   		 		   ),
   		'name'		=> array(
   		 			'title' => 'Имя',
   		 			'type'  => 'text',
   		 			'size'  => '25',
   		 			'max'   => '50',
   		 			'required' => true,
   		 		   ),	
   		'fullname'	=> array(
   		 			'title' => 'Полное имя',
   		 			'type'  => 'text',
   		 			'size'  => '40',
   		 			'max'   => '70',
   		 			'required' => true,
   		 		   ),	
   		'email'		=> array(
   		 			'title' => 'E-mail',
   		 			'type'  => 'text',
   		 			'size'  => '20',
   		 			'max'   => '50',
   		 			'required' => false,
					'hide'  => true,
   		 		   ),	
   		'homepage'	=> array(
   		 			'title' => 'WWW',
   		 			'type'  => 'text',
   		 			'size'  => '20',
   		 			'max'   => '70',
   		 			'required' => false,
					'hide'  => true,
   		 		   ),	
   		'description'	=> array(
   		 			'title' => 'Описание',
   		 			'type'  => 'memo',
   		 			'size'  => 100,
   		 			'lines' => 15,
   		 			'format' => array('cut'),
   		 			'required' => false,
   		 		   ),	
   		'type'		=> array(
   		 			'title' => 'Модель',
   		 			'type'  => 'text',
   		 			'size'  => '25',
   		 			'max'   => '50',
   		 			'required' => true,
   		 		   ),	
   		'number_people'	=> array(
   		 			'title' => 'Кол-во мест',
					'type'  => 'int',
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'season'	=> array(
   		 			'title' => 'Сезон',
   		 			'type'  => 'text',
   		 			'size'  => '15',
   		 			'max'   => '10',
   		 			'required' => true,
   		 		   ),	
   		'year'		=> array(
   		 			'title' => 'Год',
					'type'  => 'int',
   		 			'required' => true,
   		 		   ),	
   		'number'	=> array(
   		 			'title' => 'Номер',
					'type'  => 'int',
   		 			'required' => true,
   		 		   ),	
   		'river'		=> array(
   		 			'title' => 'Река',
					'type'  => 'one2many',
					'lookuptable' => 'rivers',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => true,
   		 		   ),	
   		'captain'	=> array(
   		 			'title' => 'Капитан',
					'type'  => 'one2many',
					'lookuptable' => 'people',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'title'		=> array(
   		 			'title' => 'Заглавие',
   		 			'type'  => 'text',
   		 			'size'  => '40',
   		 			'max'   => '250',
   		 			'required' => true,
   		 		   ),	
   		'body'		=> array(
   		 			'title' => 'Текст',
   		 			'type'  => 'memo',
   		 			'size'  => 100,
   		 			'lines' => 15,
   		 			'format' => array('cut'),
   		 			'required' => true,
   		 		   ),	
   		'author_id'	=> array(
   		 			'title' => 'Автор',
					'type'  => 'one2many',
					'lookuptable' => 'people',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => false,
   		 		   ),	
   		'author'	=> array(
   		 			'title' => 'Автор [внешний]',
   		 			'type'  => 'text',
   		 			'size'  => '40',
   		 			'max'   => '150',
   		 			'required' => false,
   		 		   ),	
   		'pohody_doc'	=> array(
   		 			'title' => 'Дневники',
					'type'  => 'many2many',
					'hide' => true,
					'linktable' => 'pohody_docs',
					'linkprimary' => 'idpohod',
					'linkforeign' => 'iddoc',
					'lookuptable' => 'documents',
					'lookupkey' => 'id',
					'lookupresult' => 'title',
   		 			'required' => true,
   		 		   ),	
   		'ekipazh_people' => array(
   		 			'title' => 'Экипаж',
					'type'  => 'many2many',
					'hide' => true,
					'linktable' => 'ekipazh',
					'linkprimary' => 'idekipazh',
					'linkforeign' => 'idpeople',
					'lookuptable' => 'people',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
   		 			'required' => true,
   		 		   ),	
   		'boat_id'	 => array(
   		 			'title' => 'Судно',
					'type'  => 'one2many',
					'lookuptable' => 'boats',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => true,
   		 		   ),	
   		'pohod_id'	 => array(
   		 			'title' => 'Поход',
					'type'  => 'one2many',
					'lookuptable' => 'pohody',
					'lookupkey' => 'id',
					'lookupresult' => 'shortname',
					'format' => array('lookup'),
   		 			'required' => true,
   		 		   ),	
   		'when_'		=> array(
   		 			'title' => 'Дата',
   		 			'type'  => 'date',
					'default_now' => true,
   		 			'required' => true,
   		 		   ),	
   		'idpara'	=> array(
   		 			'title' => '№ абзаца',
					'type'  => 'int',
   		 			'required' => false,
   		 		   ),	
   		'pohod_id2'	 => array(
   		 			'title' => 'Поход',
					'type'  => 'one2many',
					'lookuptable' => 'pohody',
					'lookupkey' => 'id',
					'lookupresult' => 'shortname',
					'format' => array('lookup'),
   		 			'required' => false,
   		 		   ),	
   		'man_id'	=> array(
   		 			'title' => 'Походник',
					'type'  => 'one2many',
					'lookuptable' => 'people',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => false,
   		 		   ),	
   		'order_'	=> array(
   		 			'title' => 'Порядок',
					'type'  => 'int',
   		 			'required' => true,
   		 		   ),	
   		'width'		=> array(
   		 			'title' => 'Ширина картинки',
					'type'  => 'int',
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'height'	=> array(
   		 			'title' => 'Высота картинки',
					'type'  => 'int',
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'thumb_width'	=> array(
   		 			'title' => 'Ширина мал. картинки',
					'type'  => 'int',
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'thumb_height'	=> array(
   		 			'title' => 'Высота мал. картинки',
					'type'  => 'int',
   		 			'required' => true,
					'hide'  => true,
   		 		   ),	
   		'river_id'	=> array(
   		 			'title' => 'Река',
					'type'  => 'one2many',
					'lookuptable' => 'rivers',
					'lookupkey' => 'id',
					'lookupresult' => 'name',
					'format' => array('lookup'),
   		 			'required' => false,
   		 		   ),	
		 ),
         );

admin_html();


