<?php

define('FMX_APP_PATH', realpath(__DIR__.'/../../FMX-H2_ Diseño y Desarrollo Core/App-ferremax'));

if (! FMX_APP_PATH || ! is_file(FMX_APP_PATH.'/vendor/autoload.php')) {
    throw new RuntimeException('Instale las dependencias de App-ferremax del Hito 2 con composer install.');
}

require FMX_APP_PATH.'/vendor/autoload.php';
