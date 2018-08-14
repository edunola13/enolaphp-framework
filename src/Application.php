<?php
namespace Enola;
use Enola\Support\Error;

/**
 * Esta clase representa el Nucleo del framework. En esta se cuentra la funcionalidad principal del framework
 * En su instanciacion cargara todos los modulos de soporte, librerias definidas por el usuario y demas comportamiento
 * sin importar el tipo de requerimiento.
 * Mediante el metodo request atendera el requerimiento actual donde segun el tipo del mismo cargara los modulos principales
 * correspondientes y les cedera el control a cada uno como corresponda.
 * Permite la administracion de variables de tipo aplicacion mediante la cache. 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola
 * @internal
 */
class Application{
    /** Referencia a la clase EnolaContext 
     * @var \Enola\EnolaContext */
    public $context;
    /** Instancia del Sistema de Cache de uso interno 
     * @var Enola\Support\Cache\CacheInterface */
    public $cache;
    /** Prefijo a utilizar en el Sistema de Cache 
     * @var string */
    private $prefixApp= 'APP';
    
    /** Referencia al nucleo HTTP 
     * @var Http\HttpCore */
    public $httpCore;
    /** Referencia al nucleo Core 
     * @var Cron\CronCore */
    public $cronCore;
    /** Referencia a la clase View 
     * @var Support\View */
    public $view;
    
    /** Instancia del motor de dependencias
     * @var Support\DependenciesEngine */
    public $dependenciesEngine;
    /**
     * Constructor - Ejecuta metodo init
     * @param EnolaContext $context
     */
    public function __construct($context) {
        $this->context= $context;
        $this->context->app= $this;
        $this->init();
    }   
    /**
     * Responde al requerimiento analizando el tipo del mismo, HTTP,CLI,ETC.
     */
    public function request(){        
        //Cargo el modulo correspondiente en base al tipo de requerimiento
        if(ENOLA_MODE == 'HTTP'){
            //Cargo el modulo Http
            $this->loadHttpModule();
        }else{
            //Cargo el modulo Cron
            $this->loadCronModule();
        }        
        //Luego de la carga de todos los modulos creo una instancia de Support\View
        $this->view= new Support\View();
        //Cargo la configuracion del usuario
        $this->loadUserConfig();
        //Analizo si estoy en modo HTTP o CLI
        if(ENOLA_MODE == 'HTTP'){
            //Ejecuto el controlador correspondiente
            $this->httpCore->executeHttpRequest();
        }else{
            //Ejecuta el cron controller
            $this->cronCore->executeCronController();
        }        
    }    
    /**
     * Realiza la carga de modulos, librerias y soporte que necesita el framework para su correcto funcionamiento
     * sin importar el tipo de requerimiento (HTTP, CLI, Etc).
     */
    private function init(){
        //Load Archivos de Funciones
        $this->loadFunctionsFiles();
        //Instancio el sistema de Cache
        $this->cache= new Support\Cache\Cache();
        //EnolaContext->init(): Cargo las configuraciones de contexto faltante
        $this->context->init();
        //Instancio el motor de Dependencias
        $this->dependenciesEngine= new Support\DependencyEngine\DependenciesEngine();
        //Cargo las librerias definidas por el usuario
        $this->loadLibraries();
    }
    /**
     * Carga de modulos de soporte para que el framework trabaje correctamente
     */ 
    protected function loadFunctionsFiles(){
        //Carga del modulo errores - se definen manejadores de errores
        require $this->context->getPathFra() . 'Support/fn_generic.php'; 
        //Carga del modulo errores - se definen manejadores de errores
        require $this->context->getPathFra() . 'Support/fn_error.php';    
        //Carga de modulo para carga de archivos
        require $this->context->getPathFra() . 'Support/fn_load_files.php';      
        //Carga el modulo de funciones de vista exportadas al usuario de manera simple
        require $this->context->getPathFra() . 'Support/fn_view.php';
    }
    /**
     * Carga todas las librerias particulares de la aplicacion que se cargaran automaticamente indicadas en el archivo de configuracion
     * @deprecated since version 1.2
     */
    protected function loadLibraries(){       
        //Recorro de a una las librerias, las importo
        foreach ($this->context->getLibrariesDefinition() as $libreria) {
            //$libreria['class'] tiene la direccion completa desde LIBRARIE, no solo el nombre
            $dir= $libreria['path'];
            Support\import_librarie($dir);
        }
    }
    /**
     * Carga e inicializa el modulo HTTP
     */
    protected function loadHttpModule(){        
        //Cargo el modulo HTTP e instancio el Core que se encarga de crear el HttpRequest que representa el requerimiento HTTP
        $this->httpCore= new Http\HttpCore($this);
        //Analiza el paso de un error HTTP        
        Error::catch_server_error();
    }
    /**
     * Carga el modulo cron y ejecuta el Cron correspondiente
     * @global array $argv
     * @global array $argc
     */
    protected function loadCronModule(){
        //Consigo las variables globales para linea de comandos
        global $argv, $argc;
        //Analizo si se pasa por lo menos un parametros (nombre cron), el primer parametros es el nombre del archivo y el segundo en nombre de la clase
        //pregunta por >= 2
        if($argc >= 2){
            $this->cronCore= new Cron\CronCore($this, $argv);            
        }else{
            Error::general_error('Cron Controller', 'There isent define any cron controller name');
        }    
    }       
    /**
     * Despues de la carga inicial y las libreria permite que el usuario realice su propia configuracion
     * Antes de atender el requerimiento HTTP o CLI
     */
    protected function loadUserConfig(){
        require $this->context->getPathApp() . 'load_user_config.php';    
    }
    
    /**
     * Retorna el Requerimiento actual
     * @return Support\Request
     */
    public function getRequest(){
        if($this->httpCore != NULL){
            return $this->httpCore->httpRequest;
        }else{
            return $this->cronCore->cronRequest;
        }
    }
    /**
     * Retorna el Response actual
     * @return Support\Response
     */
    public function getResponse(){
        if($this->httpCore != NULL){
            return $this->httpCore->httpResponse;
        }else{
            return $this->cronCore->cronResponse;
        }
    }
    /**
     * Devuelve un atributo en cache a nivel aplicacion. Si no existe devuelve NULL.
     * @param string $key
     * @return data
     */
    public function getAttribute($key){
        return $this->cache->get($this->prefixApp . $key);
    }
    /**
     * Guarda un atributo en cache a nivel aplicacion. Por tiempo indefinido.
     * @param string $key
     * @param data $value
     */
    public function setAttribute($key, $value){
        return $this->cache->store($this->prefixApp . $key, $value);
    }
    /**
     * Elimina un atributo en cache a nivel aplicacion.
     * @param string $key
     * @return boolean
     */
    public function deleteAttribute($key){
        return $this->cache->delete($this->prefixApp . $key);
    }
}