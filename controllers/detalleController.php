<?php


	$tpl = new Kiwi("detalle");
	$tpl->loadTPL();
	$tpl->setVarsTPL(["CHIPID"=>explode("/", $_GET['slug'])[1]]);
	$tpl->printTPL();

 ?>