<?php
namespace Enola\Support;
use Enola\EnolaContext as Ctx;
/* 
Este modulo hace de Proxy de la clase View para tener todas sus funciones disponibles de manera de no tener que instanciar 
la clase.
*/

/**
 * Realiza el include de una vista dentro de otra
 * @param string $view
 */
function includeView($view, $params = NULL){
    if($params != NULL && is_array($params)){
        foreach ($params as $key => $value) {
            $$key= $value;
        }
    }
    $dir= PATHAPP . 'src/view/' . $view . '.php';
    include $dir;
}
/**
 * Retorna la baseurl
 * @return string
 */
function base(){
    return Ctx::getInstance()->app->view->base();
}
//Funciones de alcance HTTP
if(ENOLA_MODE == 'HTTP'){
    /**
     * Retorna la real_baseurl
     * @return string
     */
    function realBase(){
        return Ctx::getInstance()->app->view->realBase();
    }    
    /**
     * Retorna la base url con el locale actual
     * @return string
     */
    function baseLocale(){
        return Ctx::getInstance()->app->view->baseLocale();
    }
    /**
     * Arma una url para un recurso
     * @param string $internalUri
     * @return string 
     */
    function urlResourceFor($internalUri){
        return Ctx::getInstance()->app->view->urlResourceFor($internalUri);
    }
    /**
     * Arma una url para una URI interna
     * @param type $internalUri
     * @param type $locale
     * @return string 
     */
    function urlFor($internalUri, $locale = NULL){
        return Ctx::getInstance()->app->view->urlFor($internalUri, $locale);
    }
    /**
     * Arma una url internacionalizada (locale actual) para una URI interna
     * @param string $internalUri
     * @return string 
     */
    function urlLocaleFor($internalUri){
        return Ctx::getInstance()->app->view->urlLocaleFor($internalUri);
    }    
    /**
     * Retorna el locale actual.
     * En caso de que el locale este indicado en la URL sera igual a locale_uri, si no sera igual al locale definido por defecto.
     * @return string
     */
    function locale(){
        return Ctx::getInstance()->app->view->locale();
    }    
    /**
     * Retorna el locale actual de la url
     * @return string o null
     */
    function localeUri(){
        return Ctx::getInstance()->app->view->localeUri();
    }
}
/**
 * reemplaza $for por $replace en el string $string
 * @param string $replace
 * @param string $for
 * @param string $string
 * @return string
 */
function replace($replace, $for, $string){
    return Ctx::getInstance()->app->view->replace($replace, $for, $string);
}    
/**
 * Quita los blancos del string por -
 * @param string $string
 * @return string
 */
function replaceSpaces($string){
    return Ctx::getInstance()->app->view->replaceSpaces($string);
}
/**
 * Carga un archivo de internacionalizacion. Si no se especifica el locale carga el archivo por defecto, si no
 * le agrega el locale pasado como parametro
 * @param string $file
 * @param string $locale
 */
function i18n($file, $locale = NULL){
    return Ctx::getInstance()->app->view->i18n($file, $locale);
}    
/**
 * Cambia el archivo de internacionalizacion cargado. Lo cambia segun el locale pasado
 * @param string $locale
 */
function i18n_change_locale($locale){
    return Ctx::getInstance()->app->view->i18n_change_locale($locale);
}    
/**
 * Devuelve el valor segun el archivo de internacionalizacion que se encuentre cargado
 * @param string $val_key
 * @param array $params
 * @return string
 */
function i18n_value($val_key, $params = NULL){
    return Ctx::getInstance()->app->view->i18n_value($val_key, $params);
}    
/**
 * Retorna el locale configurado para el contenido internacionalizado
 * @return string
 */
function i18n_locale(){
    return Ctx::getInstance()->app->view->i18n_locale();
}