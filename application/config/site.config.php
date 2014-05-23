<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['manga'] = array(
	// ограничение по длине файла ~ 2 mb
	'length' => 3000000,
	// размер страницы ~ 1 mb
	'page' => 1500000,
	// кэш (24 часа - 24*60*60)
	'cache' => 86400,
);
