<?php
namespace infrajs\controller;
use infrajs\event\Event;
use infrajs\path\Path;
use infrajs\view\View;
use infrajs\layer\tpl\Tpl;

/**
 * У слоя созданы свойства
 * tpl, json, dataroot, tplroot, data, tplcheck, datacheck
 **/

Path::req('*controller/infra.php');

Event::handler('oninit', function () {
	Layer::parsedAdd('tpl');
	Layer::parsedAdd('json');
	Layer::parsedAdd('dataroot');
	Layer::parsedAdd('tplroot');
}, 'tpl');


Event::handler('layer.oncheck', function (&$layer) {
	Tpl::tplroottpl($layer);
	Tpl::dataroottpl($layer);
	Tpl::tpltpl($layer);
	Tpl::jsontpl($layer);
}, 'tpl:div,env,config');



Event::handler('layer.isshow', function (&$layer) {
	if (@$layer['tpl']) return;

	$r = true;
	if (!empty($layer['parent'])) {//Пустой слой не должен обрывать наследования если какой=то родитель скрывает всю ветку		
		$r = $layer['parent']['is_save_branch'];
		if (is_null($r)) $r = true;
	}
	$layer['is_save_branch'] = $r;
	return false;
}, 'tpl:div,is');

Event::handler('layer.isshow', function (&$layer) {
	//tpl depricated
	if (is_string(@$layer['tpl']) && @$layer['tplcheck']) {
		//Мы не можем делать проверку пока другой плагин не подменит tpl
		$res = Load::loadTEXT($layer['tpl']);
		if (!$res) {
			return false;
		}
	}
}, 'tpl:div,is');
Event::handler('layer.isshow', function (&$layer) {
	//tpl depricated
	if (Layer::pop($layer, 'onlyclient')) return;
	return Tpl::jsoncheck($layer);
}, 'tpl:div,is');


Event::handler('layer.onshow', function (&$layer) {
	if (Layer::pop($layer, 'onlyclient')) return;
	$layer['html'] = Tpl::getHtml($layer);
}, 'tpl:div');

Event::handler('layer.onshow', function (&$layer) {
	//tpl
	if (Layer::pop($layer, 'onlyclient')) return;
	if(!empty($layer['div'])){
		$div = $layer['div'];
	}else{
		$div = null;
	}
	$r = View::html($layer['html'], $div);
	if (!$r && (!isset($layer['divcheck']) || !$layer['divcheck'])) {
		echo 'Не найден div '.$layer['div'].' infra_html<br>';
	}
	unset($layer['html']);//нефиг в памяти весеть
}, 'tpl:div');
