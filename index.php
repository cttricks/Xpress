<?php

include __DIR__ .'/src/xpress.php';

APP::get('/', function($req, $res){
    $res->send('Xpress is live! 🥳🥳');
});

APP::end();

?>
