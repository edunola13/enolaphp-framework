<?php
namespace Enola;
/**
 * Esta clase es la encargada de administrar toda la configuracion global de la aplicacion de una manera central y sencilla.
 * Esta va a ser instanciada una unica vez.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola
 * @internal
 */
class EnolaContext {
    /** Instancia de el mismo. Singleton
     * @var EnolaContext */
    private static $instance;
    /** Referencia el nucleo del framework 
     * @var \Enola\Application */
    public $app;
    /** Variables de contexto definidas en el archivo de configuracion.
     * @var mixed[] */
    private $contextVars;
    //Path basicos
    /** Path raiz de toda la aplicacion
     * @var string */
    private $pathRoot;
    /** Path de la carpeta framework
     * @var string */    
    private $pathFra;
    /** Path de la carpeta application
     * @var string */
    private $pathApp;
    //URLs base
    /** Direccion donde funciona la aplicacion
     * @var string */
    private $urlApp;
    /** Direccion base relativa donde funciona la aplicacion
     * @var string */
    private $relativeUrl;
    /** Direccion url donde funciona la aplicacion en base a donde ejecuta 
     * @var string */
    private $baseUrl;
    /** Archivo index de la aplicacion
     * @var string */
    private $indexPage;
    /** Nombre de la variable de sesion que contiene el perfil del usuario
     * @var string */
    private $sessionProfile;
    //Variables simples
    /** Nivel de errores a controlar
     * @var string */
    private $error;
    /** Ambiente actual de la aplicacion
     * @var string */
    private $environment;
    /** Charset a utilizar en PHP
     * @var string */
    private $charset;
    /** Time Zone default en PHP
     * @var string */
    private $timeZone;
    /** Indica si soporta o no multi dominios
     * @var string */
    private $multiDomain;
    /** Dominio de la App
     * @var string */
    private $domain;
    /** Prefijo para carpetas donde se guarda informacion de cada dominio
     * @var string */
    private $folderDomain;
    /** Archivos de configuracion por dominio
     * @var mixed */
    private $configFiles= array();
    /** Tipo de configuracion a utilizar
     * @var string */
    private $configurationType;
    /** Carpeta donde se encuentra la configuracion
     * @var string */
    private $configurationFolder;
    /** Indica se se cachean los archivos de configuracion
     * @var string */
    private $cacheConfigFiles;
    /** Indica el metodo de autenticacion que usara la aplicacion
     * @var boolean*/
    private $authentication;
    /** Indica si la session se inicia automaticamente por el framework
     * @var boolean*/
    private $sessionAutostart;
    /** Path archivo de authorization
     * @var string */
    private $authorizationFile;
    /** Path archivo de configuracion de database
     * @var string */
    private $databaseConfiguration;
    //Definiciones de diferentes aspectos/partes
    /** Definicion de librerias
     * @var string */
    private $librariesDefinition;
    /** Definicion de archvios de dependencias
     * @var string */
    private $dependenciesFile;
    /** Definicion de todos los arhivos con controladores
     * @var string */
    private $controllersFile;
    /** Contiene la definicion de todos los controladores
     * @var array */
    private $controllersDefinition;
    /** Contiene la definicion de todos los middlewares
     * @var array */
    private $middlewaresDefinition;
    /** Definicion de filtros pre procesameinto
     * @var string */
    private $filtersBeforeDefinition;
    /** Definicion de filtros post procesamiento
     * @var string */
    private $filtersAfterDefinition;
    //I18n
    /** Locale por defecto de la aplicacion
     * @var string */
    private $i18nDefaultLocale;
    /** Locales soportados por la aplicacion
     * @var string */
    private $i18nLocales;
    
    /**
     * Crea una instancia de la clase, llama al metodo init y guarda la instancia
     * @param string $path_root
     * @param string $path_framework
     * @param string $path_application
     */
    public function __construct($path_root, $path_framework, $path_application, $configurationType, $configurationFolder, $charset, $timeZone, $multiDomain, $folderDomain, $configFiles, $cache) {
        //Librarie to YAML if it's necessary
        if($configurationType == 'YAML'){
            require $path_framework . 'Support/Spyc.php';
        }
        
        //PATH_ROOT: direccion base donde se encuentra la aplicacion completa, es el directorio donde se encuentra el archivo index.php        
        $this->pathRoot= $path_root;
        //PATHFRA: direccion de la carpeta del framework - definida en index.php
        $this->pathFra= $path_framework; 
        //PATHAPP: direccion de la carpeta de la aplicacion - definida en index.php
        $this->pathApp= $path_application;
        //CONFIGURATION_TYPE: Indica el tipo de configuracion a utilizar
        $this->configurationType= $configurationType;
        //CONFIGURATION_FOLDER: Carpeta base de configuracion - definida en index.php
        $this->configurationFolder= $configurationFolder;
        //CACHE_CONFIG_FILES: Indica si se cachean los archivos de configuracion
        $this->cacheConfigFiles= $cache;
        //CHARSET: Indica el charset que se esta utilizando en PHP
        $this->charset= $charset;
        //TIMEZONE: Indica el default Time Zone
        $this->timeZone= $timeZone;
        //Setea constantes basicas
        $this->setBasicConstants();
        //MULTI_DOMAIN: Indica si se soporta multiple dominios
        $this->multiDomain= $multiDomain;
        //DOMAIN: Dominio de la App
        if(ENOLA_MODE == 'HTTP'){
            $this->domain= filter_input(INPUT_SERVER, 'SERVER_NAME');
            $this->domain= str_replace('www.', '', $this->domain);
        }else{
            //Consigo las variables globales para linea de comandos
            global $argv;
            if(strpos($argv[1], 'domain=') !== false){
                $pos= strpos($argv[1], 'domain=');
                $this->domain= substr($argv[1], $pos + 7);
            }
        }
        if($this->multiDomain){
            //FOLDER_DOMAIN: Indica prefijo para las carpetas de configuracion por dominio
            $this->folderDomain= $folderDomain;
            $this->configFiles= $configFiles;
        }
        //Guardo la instancia para qienes quieran consultar desde cualqueir ubicacion
        self::$instance= $this;
    }
    
    /**
     * Devuelve la instancia creada automaticamente por el framework en su carga
     * @return EnolaContext
     */
    public static function getInstance(){
        return self::$instance;
    }
    /**
     * Setea la instancia de la clase
     * @param EnolaContext $instance
     */
    public static function setInstance($instance){
        self::$instance= $instance;
    }
    
    /**
     * Carga la configuracion global de la aplicacion 
     * @param string $path_root
     * @param string $path_framework
     * @param string $path_application
     */
    public function init(){
        $file= 'config';
        //Seleccion el archivo de configuracion correspondiente segun el MODO y el DOMINIO
        if($this->multiDomain && $this->domain){
            //Ya no importa el modo, se definio todo antes si busca o no por dominio
            //if(ENOLA_MODE == 'HTTP'){
                $file= $this->getConfigFile();
            //}else{
                //reset($this->configFiles);
                //$file= next($this->configFiles);
            //}
        }
        $config= $this->readConfigurationFile($file);
        //Define si muestra o no los errores y en que nivel de detalle dependiendo en que fase se encuentre la aplicacion
        switch ($config['environment']){
            case 'development':
                error_reporting(E_ALL);
                $this->error= 'ALL';
                break;
            case 'production':
                error_reporting(0);
                $this->error= 'NONE';
                break;
            default:
                //No realiza el llamado a funcion de error porque todavia no se cargo el modulo de errores
                $head= 'Configuration Error';
                $message= 'The environment is not defined in configuration.json';
                require $this->pathApp . 'errors/general_error.php';
                exit;
        } 
        //URL_APP: Url donde funciona la aplicacion
        $this->urlApp= $config['url_app'];
        //BASE_URL: Base relativa de la aplicacion - definida por el usuario en el archivo de configuracion    
        $pos= strlen($config['relative_url']) - 1;
        if($config['relative_url'][$pos] != '/'){
            $config['relative_url'] .= '/';
        }
        $this->relativeUrl= $config['relative_url'];
        //INDEX_PAGE: Pagina inicial. En blanco si se utiliza mod_rewrite
        $this->indexPage= $config['index_page'];
        //SESSION_PROFILE: Setea la clave en la que se guarda el profile del usuario
        $this->sessionProfile= "";
        if(isset($config['session-profile'])){
            $this->sessionProfile= $config['session-profile'];
        }
        
        //ENVIRONMENT: Indica el ambiente de la aplicacion
        $this->environment= $config['environment'];
        //AUTHENTICATION: Indica el metodo de autenticacion de la aplicacion
        $this->authentication= $config['authentication'];
        //SESSION_AUTOSTART: Indica si el framework inicia automaticamente la session
        $this->sessionAutostart= $config['session_autostart'];
        //AUTHORIZATION_FILE: Indica el archivo que contiene la configuracion de autorizacion
        $this->authorizationFile= $config['authorization_file'];
        //CONFIG_BD: archivo de configuracion para la base de datos
        if(isset($config['database']) && $config['database'] != ''){
            $this->databaseConfiguration= $config['database'];
        }        
        //Internacionalizacion: En caso que se defina se setea el locale por defecto y todos los locales soportados
        if(isset($config['i18n'])){
            $this->i18nDefaultLocale= $config['i18n']['default'];
            if(isset($config['i18n']['locales'])){
                $locales= str_replace(" ", "", $config['i18n']['locales']);
                $this->i18nLocales= explode(",", $locales);
            }
        }
        
        //Diferentes definiciones
        $this->librariesDefinition= isset($config['libs']) ? $config['libs'] : [];
        $this->dependenciesFile= $config['dependency_injection'];
        $this->controllersFile= $config['controllers'];
        $this->middlewaresDefinition= $config['middlewares'];
        $this->filtersBeforeDefinition= $config['filters'];
        $this->filtersAfterDefinition= $config['filters_after_processing'];
        
        if(isset($config['vars'])){
            $this->contextVars= $config['vars'];
        }
    }
    /**
     * Establece las constantes basicas del sistema
     */
    private function setBasicConstants(){
        //Algunas constantes - La idea es ir sacandolas
        //PATHROOT: direccion de la carpeta del framework - definida en index.php
        define('PATHROOT', $this->getPathRoot());
        //PATHFRA: direccion de la carpeta del framework - definida en index.php
        define('PATHFRA', $this->getPathFra());    
        //PATHAPP: direccion de la carpeta de la aplicacion - definida en index.php
        define('PATHAPP', $this->getPathApp());
        //ENOLA_MODE: Indica si la aplicacion se esta ejecutando via HTTP o CLI
        if(PHP_SAPI == 'cli' || !filter_input(INPUT_SERVER, 'REQUEST_METHOD')){
            define('ENOLA_MODE', 'CLI');
        }else{
            define('ENOLA_MODE', 'HTTP');
        }
    }
    
    /*
     * Getters
     */ 
    public function getContextVars(){
        return $this->contextVars;
    }
    public function getContextVar($name){
        if(isset($this->contextVars[$name])){
            return $this->contextVars[$name];
        }else{
            return NULL;
        }
    }
    public function setContextVar($name, $value){
        $this->contextVars[$name]= $value;
    }
    public function getPathRoot(){
        return $this->pathRoot;
    }
    public function getPathFra(){
        return $this->pathFra;
    }
    public function getPathApp(){
        return $this->pathApp;
    }
    public function getUrlApp(){
        return $this->urlApp;
    }
    public function getRelativeUrL(){
        return $this->relativeUrl;
    }
    public function getBaseUrL(){
        return $this->baseUrl;
    }
    public function setBaseUrL($baseUrl){
        $this->baseUrl= $baseUrl;
    }
    public function getIndexPage(){
        return $this->indexPage;
    }
    public function getSessionProfile(){
        return $this->sessionProfile;
    }
    public function getError(){
        return $this->error;
    }
    public function getEnvironment(){
        return $this->environment;
    }
    public function getCharset(){
        return $this->charset;
    }
    public function getTimezone(){
        return $this->timeZone;
    }
    public function getMultiDomain(){
        return $this->multiDomain;
    }
    public function getFolderDomain(){
        return $this->folderDomain;
    }
    public function getDomain(){
        return $this->domain;
    }
    public function getConfigFiles(){
        return $this->configFiles;
    }
    public function getConfigFolderDomain(){
        if($this->multiDomain){
            if(isset($this->configFiles[$this->domain])){
                return $this->folderDomain . $this->configFiles[$this->domain] . '/';
            }
            return $this->folderDomain . $this->domain . '/';
        }
        return '';
    }
    public function getConfigFile(){
        if($this->multiDomain){
            return $this->getConfigFolderDomain() . 'config';
        }
        return 'config';
    }
    public function getConfigurationType(){
        return $this->configurationType;
    }
    public function getConfigurationFolder(){
        return $this->configurationFolder;
    }
    public function getAuthentication(){
        return $this->authentication;
    }
    public function getSessionAutostart(){
        return $this->sessionAutostart;
    }
    public function getAuthorizationFile(){
        return $this->authorizationFile;
    }
    public function getCacheConfigFiles(){
        return $this->cacheConfigFiles;
    }
    public function getDatabaseConfiguration(){
        return $this->databaseConfiguration;
    }
    public function getLibrariesDefinition(){
        return $this->librariesDefinition;
    }
    public function getDependenciesFile(){
        return $this->dependenciesFile;
    }
    public function getControllersFile(){
        return $this->controllersFile;
    }
    public function getControllersDefinition(){
        if(!$this->controllersDefinition){
            $this->controllersDefinition= array();
            foreach ($this->getControllersFile() as $nameFile) {
                $this->controllersDefinition= array_merge($this->controllersDefinition, $this->readConfigurationFile($nameFile));
            }
        }
        return $this->controllersDefinition;
    }
    public function getMiddlewaresDefinition(){
        return $this->middlewaresDefinition;
    }
    public function getFiltersBeforeDefinition(){
        return $this->filtersBeforeDefinition;
    }
    public function getFiltersAfterDefinition(){
        return $this->filtersAfterDefinition;
    }
    public function getI18nDefaultLocale(){
        return $this->i18nDefaultLocale;
    }
    public function getI18nLocales(){
        return $this->i18nLocales;
    }
        
    /*
     * Metodos facilitadores
     */
    /**
     * Devuelve un array con los valores del archivo de configuracion
     * @param string $name
     * @param boolean $cache
     * @return array
     */
    public function readConfigurationFile($name, $cache = TRUE){
        //Lee archivo de configuracion principal donde se encuentra toda la configuracion de variables, filtros, controladores, etc.
        $config= NULL;
        if($this->cacheConfigFiles && $cache && $this->app->cache != NULL){
            //Si esta en produccion y se encuentra en cache lo cargo
            $config= $this->app->getAttribute('C_' . $name);
        }
        if($config == NULL){
            //Cargo la configuracion y guardo en cache si corresponde
            $config= $this->readFile($name);
            if($this->cacheConfigFiles && $cache && $this->app->cache != NULL){
                $this->app->setAttribute('C_' . $name, $config);
            }
        }
        if(! is_array($config)){
            //Arma una respuesta de error de configuracion.
            \Enola\Support\Error::general_error('Configuration Error', 'The configuration file ' . $name . ' is not available or is misspelled');
            //Cierra la aplicacion
            exit;
        }
        return $config;
    }
    /**
     * Compila archivos de configuracion. Es necesario indicar path absoluto
     * @param type $name
     * @param type $absolute Si es true hay que indicar el path absoluto del archivo
     */
    public function compileConfigurationFile($file){
        //CARGAMOS LA DEPENDENCIA POR SI ES NECESARIA
        require_once $this->pathFra . 'Support/Spyc.php';
        
        $info= new \SplFileInfo($file);
        $path= $info->getPath() . '/';
        $name= $info->getBasename('.' . $info->getExtension());
        $config= $this->readFileSpecific($path . $name . '.' . $info->getExtension(), $info->getExtension());
        file_put_contents($path . $name . '.php', '<?php $config = ' . var_export($config, true) . ';');
    }
    /**
     * Lee un archivo y lo carga en un array
     * @param string $name
     * @param string $folder Si no se indica $folder se usa la carpeta de configuracion de la app
     * @return array
     */
    private function readFile($name, $folder = null){
        $realConfig= NULL;
        $folder != null ?: $folder= $this->pathApp . $this->configurationFolder;
        if($this->configurationType == 'YAML'){
            $realConfig = \Spyc::YAMLLoad($folder . $name . '.yml');            
        }else if($this->configurationType == 'PHP'){
            include $folder . $name . '.php';
            //La variable $config la define cada archivo incluido
            $realConfig= $config;
        }else{
            $realConfig= json_decode(file_get_contents($folder . $name . '.json'), true);  
        }
        return $realConfig;
    }
    /**
     * Lee un archivo y lo carga en un array
     * A defirencia de readFile a este no le importa el tipo de configuracion ni la carpeta de este archivo. Toma un path completo y lo carga
     * @param string $path
     * @return array
     */
    public function readFileSpecific($path, $extension = 'yml'){
        $realConfig= NULL;
        if($extension == 'yml'){
            $realConfig = \Spyc::YAMLLoad($path);            
        }else if($extension == 'php'){
            include $path;
            //La variable $config la define cada archivo incluido
            $realConfig= $config;
        }else{
            $realConfig= json_decode(file_get_contents($path), true);  
        }
        return $realConfig;
    }
    /**
     * Indica si el ambiente actual es de produccion
     * @return boolean
     */
    public function isInProduction(){
        return $this->environment == 'production';
    }
    /**
     * Retorna si se definicio configuracion de base de datos
     * @return boolean
     */
    public function isDatabaseDefined(){
        if($this->databaseConfiguration != NULL){
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Retorna si se definieron los locales soportados
     * @return boolean
     */
    public function isLocalesDefined(){
        if($this->i18nLocales != NULL){
            return TRUE;
        }
        return FALSE;
    }
}