<?php

foreach (glob(__DIR__.'/*.php') as $file){
    if($file !== __FILE__){
        require_once $file;
    }
}
