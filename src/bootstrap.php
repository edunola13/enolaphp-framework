<?php
namespace Enola;

//Carga el setup de la aplicacion
include $path_application . 'setup.php';
//Tiempo de Inicio de la aplicación
$timeBegin= microtime(TRUE);

//Instancio la Clase EnolaContext que carga la configuracion de la aplicacion
$context= new EnolaContext($path_root, $path_framework, $path_application, $configurationType, $configurationFolder, $charset, $timeZone, $multiDomain, $folderDomain, $configFiles, $cache);
//Una vez realizada la carga de la configuracion empieza a trabajar el core del Framework
$app= new Application($context);

//Seteo el caluclo de la performance, si corresponde
$app->initPerformance($timeBegin);

//Ejecuto el requerimiento actual
$app->request();