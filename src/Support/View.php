<?php
namespace Enola\Support;
use Enola\EnolaContext;

/**
 * Esta clase provee comportamiento para facilitar el armado de la vista proveyendo diferentes metodos que simplifican situacines
 * tipicas en el armado de la vista.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class View{
    /** Referencia al nucleo de la aplicacion
     * @var \Enola\Application */
    public $app;
    /** Referencia al context de la aplicacion 
     * @var EnolaContext */
    public $context;
    /** Referencia al Request actual 
     * @var Request */
    public $request;
    /** Referencia al Response actual 
     * @var Response */
    public $response;
    //i18n
    /** Locale actual
     * @var string */
    protected $locale;
    /** Path archivo de internacionalizacion
     * @var string */
    protected $fileName;
    /** Contenido del archivo cargado por linea en un array
     * @var array */
    protected $i18nContent;
    /*
     * Constructor - Setea variables que necesitara luego para resolver su comportamiento 
     */
    public function __construct() {
        $this->context= EnolaContext::getInstance();
        $this->app= $this->context->app;
        $this->request= $this->app->getRequest();
        $this->response= $this->app->getResponse();
    }
    /**
     * Retorna la baseurl
     * @return string
     */
    function base(){
        return BASEURL;
    }
    /**
     * Retorna la real_baseurl
     * @return string
     */
    function realBase(){
        return $this->request->realBaseUrl;
    }    
    /**
     * Retorna la base url con el locale actual
     * @return string
     */
    function baseLocale(){
        return $this->request->baseUrlLocale;
    }
    /**
     * Arma una url para un recurso
     * @param string $internalUri
     * @return string 
     */
    function urlResourceFor($internalUri){
        $internalUri= ltrim($internalUri, '/');
        return BASEURL . 'resources/' . $internalUri;
    }
    /**
     * Arma una url para una URI interna
     * @param type $internalUri
     * @param type $locale
     * @return string 
     */
    function urlFor($internalUri, $locale = NULL){
        $internalUri= ltrim($internalUri, '/');
        if($locale == NULL)return $this->request->realBaseUrl . $internalUri;
        else return $this->request->realBaseUrl . $locale . '/' . $internalUri;
    }
    /**
     * Arma una url internacionalizada (locale actual) para una URI interna
     * @param string $internalUri
     * @return string 
     */
    function urlLocaleFor($internalUri){
        $internalUri= ltrim($internalUri, '/');
        return $this->request->baseUrlLocale . $internalUri;
    }
    /**
     * Retorna el locale actual.
     * En caso de que el locale este indicado en la URL sera igual a locale_uri, si no sera igual al locale definido por defecto.
     * @return string
     */
    function locale(){
        return $this->request->locale;
    }    
    /**
     * Retorna el locale actual de la url
     * @return string o null
     */
    function localeUri(){
        return $this->request->localeUri;
    }
    /**
     * reemplaza $for por $replace en el string $string
     * @param string $replace
     * @param string $for
     * @param string $string
     * @return string
     */
    function replace($replace, $for, $string){
        return str_replace($for, $replace, $string);
    }    
    /**
     * Quita los blancos del string por -
     * @param string $string
     * @return string
     */
    function replaceSpaces($string){
        return str_replace(" ", "-", $string);
    }
    /**
     * Carga un archivo de internacionalizacion. Si no se especifica el locale carga el archivo por defecto, si no
     * le agrega el locale pasado como parametro
     * @param string $file
     * @param string $locale
     */
    function i18n($file, $locale = NULL){
        $this->fileName= $file;
        $this->i18nContent= NULL;
        if($locale != NULL){
            if(file_exists(PATHAPP . 'src/content/' . $file . "_$locale" . '.txt')){
                $this->i18nContent= \E_fn\load_application_file('src/content/' . $file . "_$locale" . '.txt');
                $this->i18nContent= \E_fn\parse_properties($this->i18nContent);
                $this->locale= $locale;
            }
        }
        if($this->i18nContent == NULL){
            $this->i18nContent= \E_fn\load_application_file('src/content/' . $file . '.txt');
            $this->i18nContent= \E_fn\parse_properties($this->i18nContent);
            $this->locale= 'Default';
        }
    }    
    /**
     * Cambia el archivo de internacionalizacion cargado. Lo cambia segun el locale pasado
     * @param string $locale
     */
    function i18n_change_locale($locale){
        if(isset($this->fileName)){
            i18n($this->fileName, $locale);
        }
        else{
            \Enola\Error::general_error('I18n Error', 'Before call i18n_change_locale is necesary call i18n');
        }
    }    
    /**
     * Devuelve el valor segun el archivo de internacionalizacion que se encuentre cargado
     * @param string $val_key
     * @param array $params
     * @return string
     */
    function i18n_value($val_key, $params = NULL){
        if(isset($this->i18nContent)){
            if(isset($this->i18nContent[$val_key])){
                $mensaje= $this->i18nContent[$val_key];
                
                //Analiza si se pasaron parametros y si se pasaron cambia los valores correspondientes
                if($params != NULL){
                    foreach ($params as $key => $valor) {
                        $mensaje= str_replace(":$key", $valor, $mensaje);
                    }
                }                
                return $mensaje;
            }
        }
        else{
            \Enola\Error::general_error('I18n Error', 'Not specified any I18n file to make it run the i18n function');
        }
    }    
    /**
     * Retorna el locale configurado para el contenido internacionalizado
     * @return string
     */
    function i18n_locale(){
        if(isset($this->locale)){
            return $this->locale;
        }else{
            return 'Default';
        }
    }    
}