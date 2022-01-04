<?php

require 'modules/default.php';

$modules = Config::getInstance()->data->modules;
foreach ($modules as $name => $config) {
    require 'modules' . DS . $name . '.php';
}
