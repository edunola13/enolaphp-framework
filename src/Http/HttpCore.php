<?php
namespace Enola\Http;
use Enola\Support\Error;

/**
 * Esta clase representa el Nucleo del modulo HTTP y es donde se encuentra toda la funcionalidad del mismo
 * En su instanciacion definira la URI actual y el HttpRequest.
 * Luego proveera metodos para saber que controlador mapea segun determinada URI y ejecutar un controlador aplicando o no
 * los filtros correspondientes. Luego estas delegaran trabajo a los diferentes metodos privados.
 * Esta clase tiene una dependencia de la clase UrlUri para resolver cuestiones de URLs y URIs * 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 * @internal
 */
class HttpCore{
    /** Referencia al nucleo de la aplicacion 
     * @var \Enola\Application */
    public $app;
    /** Referencia al HttpRequest actual 
     * @var En_HttpRequest */
    public $httpRequest;
    /** Referencia al HttpResponse actual 
     * @var En_HttpResponse */
    public $httpResponse;
    /**
     * Se instancia el nucleo.
     * Se define todo lo respectivo a la URI y se define el Http Request actual
     * @param Application $app
     */
    public function __construct($app) {
        //Defino la aplicacion URI y otros valores
        $config= UrlUri::defineApplicationUri($app->context);
        $config['SESSION_AUTOSTART']= $app->context->getSessionAutostart();
        //Creo el Http request
        $this->httpRequest= new Models\En_HttpRequest($config);
        $this->httpResponse= new Models\En_HttpResponse($this->httpRequest);
        $this->app= $app;
    }
    /**
     * Retorna la especificacion del controlador que mapea con la URI actual
     * Levanta error 404 si ningun controlador mapea
     * @param string $uriapp
     * @return array 
     */
    public function mappingController($uriapp = NULL, $method = NULL){
        $controllers= $this->app->context->getControllersDefinition();
        $maps= FALSE;
        //Recorre todos los controladores principales hasta que uno coincida con la URI actual
        foreach ($controllers as $url => $controller_esp) {            
            //Analiza si el controlador mapea con la uri actual
            $controller_esp['url']= strpos($url, '@') ? substr($url, 0, strpos($url, '@')) : $url;
            
            $mapController= $this->mapsController($controller_esp, $uriapp, $method);
            if($mapController == NULL && isset($controller_esp['routes'])){
                foreach ($controller_esp['routes'] as $url => $controller_esp_2) {
                    $controller_esp_2['url']= strpos($url, '@') ? substr($url, 0, strpos($url, '@')) : $url;
                    $mapController= $this->mapsController($controller_esp_2, $uriapp, $method, $controller_esp);
                    if($mapController != NULL){
                        break;
                    }
                }
            }
            if($mapController != NULL){
                return $mapController;
            }           
        }
        //si ningun controlador mapeo avisa el problema
        if(! $maps){
            Error::error_404();
        }
    }
    /**
     * Controla si el controlador pasado mapea con la url y el metodo actual. 
     * En caso de mapear arma la especificacion del controlador.
     * @param mixed $controller
     * @param string $uriapp
     * @param string $method
     * @param mixed $parentController
     * @return mixed
     */
    private function mapsController($controller, $uriapp = NULL, $method = NULL, $parentController = NULL){
        $httpMethod= isset($controller['httpMethod']) ? $controller['httpMethod'] : '*';
        if($parentController != NULL){
            $controller['url']= rtrim($parentController['url'], '/') . '/' . ltrim($controller['url'], '/');
        }
        $maps= UrlUri::mapsActualUrl($controller['url'], $uriapp) && UrlUri::mapsActualMethod($httpMethod, $method);
        if($maps){
            $mapController= array(
                'url' => $controller['url'],
                'httpMethod' => $httpMethod,
                'location' => isset($controller['location']) ? $controller['location'] : NULL,
                'namespace' => isset($controller['namespace']) ? $controller['namespace'] : NULL,
                'class' => isset($controller['class']) ? $controller['class'] : $parentController['class'],
                'method' => isset($controller['method']) ? $controller['method'] : NULL,
                'properties' => isset($controller['properties']) ? $controller['properties'] : array(),
                'middlewares' => isset($controller['middlewares']) ? $controller['middlewares'] : []
            );
            if($parentController != NULL){
                if(! isset($controller['class'])){
                    $mapController['location']= isset($parentController['location']) ? $parentController['location'] : NULL;
                    $mapController['namespace']= isset($parentController['namespace']) ? $parentController['namespace'] : NULL;
                }
                if(isset($parentController['properties'])){
                    $mapController['properties']= array_merge($parentController['properties'], $mapController['properties']);
                }
                if(isset($parentController['middlewares'])){
                    $mapController['middlewares'] = array_merge($parentController['middlewares'], $mapController['middlewares']);
                }
            }
            
            return $mapController;
        }else{
            return NULL;
        }
    }
    /**
     * Ejecuta la especificacion de controlador pasada como parametro en base a una URI ejecutando o no filtros. En caso de 
     * que no se le pase el controlador lo consigue en base a la URI y en caso de que no se pase la URI especifica se usa 
     * la de la peticion actual.  
     * @param array $actualController
     * @param string $uriapp
     * @param boolean $filter
     */
    public function executeHttpRequest($actualController = NULL, $uriapp = NULL, $filter = TRUE){
        //Si no se paso controlador, se busca el correspondiente
        if($actualController == NULL){
            $actualController= $this->mappingController($uriapp);
        }
        //Ejecuto los filtros pre-procesamiento
        $rtaFilters= true;
        if($filter){
            $rtaFilters= $this->executeFilters($this->app->context->getFiltersBeforeDefinition());
        }
        //Controlo que desde un filtro no se haya parado la ejecucion
        if($rtaFilters !== false){
            //Ejecuto el controlador
            $this->executeController($actualController, $uriapp);
            //Ejecuto los filtros post-procesamiento
            if($filter){
                $this->executeFilters($this->app->context->getFiltersAfterDefinition());
            }
        }        
    }
    /**
     * Analiza los filtros que mapean con la URI pasada y ejecuta los que correspondan. En caso de no pasar URI se utiliza
     * la de la peticion actual.
     * @param array[array] $filters
     * @param string $uriapp
     */
    protected function executeFilters($filters, $uriapp = NULL){
        //Analizo los filtros y los aplico en caso de que corresponda
        foreach ($filters as $filter_esp) {
            $filter= UrlUri::mapsActualUrl($filter_esp['filtered'], $uriapp);
            //Si debe filtrar carga el filtro correspondiente y realiza el llamo al metodo filtrar()
            if($filter){
                $dir= $this->buildDir($filter_esp,'filters');
                $class= $this->buildClass($filter_esp);
                if(!class_exists($class)){
                    //Si la clase no existe intento cargarla
                    if(file_exists($dir)){
                        require_once $dir;
                    }else{
                        //Avisa que el archivo no existe
                        Error::general_error('Filter Error', 'The filter ' . $filter_esp['class'] . ' dont exists');
                    } 
                }
                $filterIns= new $class();
                //Analizo si hay parametros en la configuracion
                if(isset($filter_esp['properties'])){
                    $this->app->dependenciesEngine->injectProperties($filterIns, $filter_esp['properties']);
                }
                //Analiza si existe el metodo filtrar
                if(method_exists($filterIns, 'filter')){
                    $rta= $filterIns->filter($this->httpRequest, $this->httpResponse);
                    if($rta === false){
                        return false;
                    }
                }
                else{
                    Error::general_error('Filter Error', 'The filter ' . $filter_esp['class'] . ' dont implement the method filter()');
                }
            }
        }
    }
    /**
     * Se ejecutan los middlewares que se pasan como parametro
     * @param array[string] $filters
     * @param string $uriapp
     */
    protected function executeMiddlewares($middlewares){
        //Analizo los filtros y los aplico en caso de que corresponda
        $middlewaresDefinition = $this->app->context->getMiddlewaresDefinition();
        foreach ($middlewares as $middlewareName) {
            if (! isset($middlewaresDefinition[$middlewareName])) {
                Error::general_error('Middleware Error', 'The middleware ' . $middlewareName . ' dont exists');
            }
            
            $middleware = $middlewaresDefinition[$middlewareName];
            
            $dir = $this->buildDir($middleware, 'middlewares');
            $class = $this->buildClass($middleware);
            if(!class_exists($class)){
                //Si la clase no existe intento cargarla
                if(file_exists($dir)){
                    require_once $dir;
                }else{
                    //Avisa que el archivo no existe
                    Error::general_error('Middleware Error', 'The middleware ' . $middleware['class'] . ' dont exists');
                } 
            }
            $middlewareIns= new $class();
            //Analizo si hay parametros en la configuracion
            if(isset($middleware['properties'])){
                $this->app->dependenciesEngine->injectProperties($middlewareIns, $middleware['properties']);
            }
            //Analiza si existe el metodo filtrar
            if(method_exists($middlewareIns, 'handle')){
                $rta= $middlewareIns->handle($this->httpRequest, $this->httpResponse);
                if($rta === false){
                    return false;
                }
            }
            else{
                Error::general_error('Middleware Error', 'The middleware ' . $middleware['class'] . ' dont implement the method handle()');
            }
        }
    }
    /**
     * Ejecuta el controlador que mapeo anteriormente. Segun su definicion en la configuracion se ejecutara al estilo REST
     * o mediante nombre de funciones
     * @param array $controller_esp 
     * @param string $uriapp
     */
    protected function executeController($controller_esp, $uriapp = NULL) {
        if (count($controller_esp['middlewares']) > 0) {
            if ($this->executeMiddlewares($controller_esp['middlewares']) === false) {
                return;
            }
        }
        
        $dir= $this->buildDir($controller_esp);
        $class= $this->buildClass($controller_esp);
        if(!class_exists($class)){
            //Si la clase no existe intento cargarla
            if(file_exists($dir)){
                require_once $dir;
            }else{
                //Avisa que el archivo no existe
                Error::general_error('Controller Error', 'The controller ' . $controller_esp['class'] . ' dont exists');
            } 
        }
        $controller= new $class();
        //Agrego los parametros URI
        $uri_params= UrlUri::uriParams($controller_esp['url'], $uriapp);
        $dinamic_method= $uri_params['dinamic'];
        $method= $uri_params['method']; 
        $parameters= $uri_params['params'] ? $uri_params['params'] : array();
        //Analizo si hay parametros en la configuracion
        if(isset($controller_esp['properties'])){
            $this->app->dependenciesEngine->injectProperties($controller, $controller_esp['properties']);
        }       
        //Saca el metodo HTPP y en base a eso hace una llamada al metodo correspondiente
        $methodHttp= filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        if($dinamic_method){
            if($method != 'index' && !(method_exists($controller, $methodHttp . '_' . $method) || method_exists($controller, $method))){
                $parameters= array_merge(array('0' => $method), $parameters);
                $method= 'index';                
            }
            if(method_exists($controller, $methodHttp . '_' . $method)){
                $method= $methodHttp . '_' . $method;
            }
        }else if(isset($controller_esp['method'])){
            $method= $controller_esp['method'];
        }else{
            $method= "do" . ucfirst(strtolower($methodHttp));
        }
        $controller->setUriParams($parameters);
        if(method_exists($controller, $method)){
            $controller->$method($this->httpRequest, $this->httpResponse);
        }else{
            Error::general_error('HTTP Method Error', "The HTTP method $method is not supported");
        }
    }    
    /**
     * Retorna el path de la carpeta donde se encuentra el controlador/filtro en base a su definicion
     * @param type $definition
     * @param type $folder
     * @return string
     */
    protected function buildDir($definition, $folder="controllers"){
        $dir= "";
        if(! isset($definition['location'])){
            $dir= $this->app->context->getPathApp() . 'src/' . $folder . '/' . $definition['class'] . '.php';
        }else{
            $dir= $this->app->context->getPathRoot() . $definition['location'] . '/' . $definition['class'] . '.php';
        }
        return $dir;
    }
    /**
     * Retorna el nombre de la clase completo (con namespace) del controlador/filtro en base a su definicion
     * @param array $definition
     * @return string
     */
    protected function buildClass($definition){
        $namespace= (isset($definition['namespace']) ? $definition['namespace'] : '');
        //Empiezo la carga del controlador
        $dirExplode= explode("/", $definition['class']);
        $class= $dirExplode[count($dirExplode) - 1];
        if($namespace != '') $class= "\\" . $namespace . "\\" . $class;
        return $class;
    }
}