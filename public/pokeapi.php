<?php

require_once('../vendor/autoload.php');

use PokePHP\PokeApi;
$api = new PokeApi;

$pokemon = $api->pokemon('1');

$array = json_decode($pokemon, true);
echo "<pre>";
var_dump($array);
echo "</pre>";

$lang = $api->language(5);
$array = json_decode($lang, true);
echo "<pre>";
var_dump($array);
echo "</pre>";
