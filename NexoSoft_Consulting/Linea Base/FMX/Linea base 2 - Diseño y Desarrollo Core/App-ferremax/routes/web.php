<?php

foreach (glob(__DIR__.'/casos/cu*.php') as $routes) {
    require $routes;
}
