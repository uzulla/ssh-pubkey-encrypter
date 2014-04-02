<?php
session_start();
require __DIR__.'/../vendor/autoload.php';

$app = new \Slim\Slim([
    'templates.path' => __DIR__.'/../templates',
    'view' => new \Slim\Views\Twig(),
    'debug' => TRUE,
]);

$app->view()->parserExtensions = [ new \Slim\Views\TwigExtension() ];
$app->add(new \Slim\Extras\Middleware\CsrfGuard());

$app->get('/', function()use($app){
    $app->render('index.twig');
})->name('form');

$app->post('/encode',  function()use($app){

    // get ssh-pub-key
    try{
        $ssh_pub_key = \SPE\Logic::getPubKey($app->request()->params('key'));
    }catch(\Exception $e){
        throw $e;
        throw new \Exception("get key fail.".$e->getMessage());
    }

    // Encrypt
    try {
        $crypted = \Uzulla\Crypt\Misc::encodeBySshPubKey($ssh_pub_key, $app->request()->params('data'));
    } catch (\Exception $e) {
        throw new \Exception("encode fail.".$e->getMessage());
    }

    $crypted = rtrim(\SPE\Logic::encodeBase64($crypted));

    $app->render('encode.twig', [
        'crypted'=>$crypted,
        'pub_key'=>$ssh_pub_key
    ]);
})->name('encode');

$app->run();

