<?php

use App\Router;

require '../vendor/autoload.php';

//Mise en place du systeme d'affichage erreurs pour m'aider
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


$router = new Router(dirname(__DIR__) . '/pages/');

$router->getpost('/','listeCourses/choixRecettes', 'choixRecettes');
$router->getpost('/ma-liste-de-courses','listeCourses/listeCourses', 'listeCourses');
$router->getpost('/mon-livre-de-recettes','livreRecettes/index', 'livreRecettes');
$router->getpost('/mon-livre-de-recettes/new','livreRecettes/new', 'new');
$router->getpost('/mon-livre-de-recettes/[*:slug]/edit','livreRecettes/edit', 'edit');
$router->get('/mon-livre-de-recettes/[*:slug]','livreRecettes/recette', 'recette');
$router->getpost('/parametres/edit','parametres/edit', 'parametres');

$router->run();

?>