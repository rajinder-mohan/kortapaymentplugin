<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
//include_once('ps_imageslider.php');


switch($_REQUEST['action']){

		case 'list':

			$tags = array();
			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(' SELECT * FROM `'._DB_PREFIX_.'korta_tag` WHERE `id_korta`='.$_REQUEST['id_korta']);

			foreach ($row as $key => $value) {
				$tags[] = array("id"=>$value['id'],
								"width"=>$value['width'],
								"height"=>$value['height'],
								"top"=>$value['top'],
								"id_korta"=>$value['id_korta'],
								"left"=>$value['left'],
								"cat_id"=>$value['cat_id'],
								"cat_url"=>$value['cat_url'],
								);
			}
			echo json_encode($tags);
		break;

		case 'delete':

			  Db::getInstance()->execute('DELETE from `'._DB_PREFIX_.'korta_tag` WHERE id ='.$_REQUEST['id']);

		break;

		case 'save':

			// die;
		$res = Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'korta_tag` (`width`,`height`,`top`,`left`,`id_korta`,`cat_id`,`cat_url`)
			VALUES('.$_REQUEST['width'].', '.$_REQUEST['height'].', '.$_REQUEST['top'].', '.$_REQUEST['left'].', '.$_REQUEST['id_korta'].','.$_REQUEST['cat_id'].',"'.$_REQUEST['cat_url'].'")'
		);

		 print_r($res);
		 echo mysqli_insert_id();
		exit();

		break;

	}
