<?php

if(!defined('ACCESS') && ACCESS !== 'permit'){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю

require 'parameters.php';

// Рекурентно определяем абсолютный путь до начального каталога, определяемого константой THIS_DIR
function PATH($path, $i=0){

    while (basename($path) !== THIS_DIR && $i++ < 10){
        $path = realpath($path. '/../');
    }
    return $path;
};
