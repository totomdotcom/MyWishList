<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use wishlist\authentification\Authentification;
use wishlist\model\Liste;
use wishlist\model\Item;
use wishlist\controler\ListeControler;
use wishlist\controler\ItemControler;
use wishlist\controler\HomeControler;
use wishlist\controler\IdentifiantControler;
use wishlist\controler\notFoundControler;
use wishlist\controler\pagePersoControler;
use wishlist\controler\MessagesControler;
use wishlist\view\VueGenerale;

$db = new DB();
$db->addConnection(parse_ini_file("src/conf/conf.ini"));

$db->setAsGlobal();
$db->bootEloquent();

$app = new \Slim\Slim;

session_start();

//Affichage de toutes les listes

$app->get('/liste', function () {

    $c = new ListeControler();
    $c->getAllListe();
});

$app->get('/pagePerso', function () {

    $c = new pagePersoControler();
    $c->getPPerso();
});


$app->post('/pagePerso', function () {
    $c = new pagePersoControler();
    $c->connexion();
});

//Pour les invités
$app->get('/liste/creer', function () {
    $c = new ListeControler();
    $c->getCreation();
});

$app->post('/ajout/message/:no/:token', function ($no, $token) use ($app) {
    $c = new MessagesControler();
    $c->ajoutMessage($no);

    $app->response->redirect("/myWishList/liste/$no/$token", 303);
});



$app->get('/item/ajout/:no/:token', function ($no, $token) {
    $c = new ItemControler();
    $c->getCreation($no, $token);
});

$app->post('/validation/item', function () {
    $c = new ItemControler();
    $c->validerItem();
});

$app->get('/validation/item', function () {

    //TODO
    echo ("Voir les listes");
});

$app->post('/liste/validation/:token', function ($token) {

    $c = new ListeControler();
    $c->insertListe($token);
    $c->getResumeListe($token);
});


$app->get('/liste/validation/:token', function ($token) {

    $c = new ListeControler();
    $c->getResumeListe($token);
});

//Affichage de tous les items

$app->get('/item', function () {


    $c = new ListeControler();
    $c->getAllItem();
});

//Affichage d'une liste via son token + id


$app->get('/liste/:no/:token', function ($no, $token) {

    $c = new ListeControler();
    $c->getListe($no, $token);
});
//->setName('afficheListe')

//Affichage d'un item (via son no) 
$app->get('/item/reservation/:idItem', function ($idItem) {
    $c = new ListeControler();
    $c->getItemListe($idItem);
});

//Affichage d'un item via son id

$app->get('/item/:id', function ($id) {

    $c = new ListeControler();
    $c->getItem($id);
});

$app->get('/', function () {
    $c = new HomeControler();
    $c->getHome();
});

$app->post('/inscription', function () {
    $c = new IdentifiantControler();
    $c->insertUser();
});

$app->get('/connexion', function () {
    $c = new IdentifiantControler();
    $c->getConnexion();
});




$app->get('/deconnexion', function () {
    $c = new IdentifiantControler();
    $c->pageDeconnexion();
});





$app->get('/modification/liste/:id/:token', function ($id, $token) {
    $c = new ListeControler();
    $c->modifierListe($id, $token);
});

$app->post('/modification/liste/:id/:token', function ($id, $token) {
    $c = new ListeControler();
    $c->validerListe($id, $token);
    $c->getResumeListe($token);
});


$app->post('/liste/suppression/:no/:token', function ($no, $token) {
    $c = new ListeControler();
    $c->supprimer($no, $token);
});

$app->post('/liste/preSuppression/:no/:token', function ($no, $token) {
    $c = new ListeControler();
    $c->confirmerSupprListe($no, $token);
});

$app->post('/reservation/:id', function ($id) {
    $c = new ListeControler();
    $c->addRes($id);
    $c->getAllListe();
});

$app->notFound(function () use ($app) {


    $c = new notFoundControler();
    $c->get404();
});

$app->run();
