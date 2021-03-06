<?php
namespace Enola\Cron;
use Enola\Support\Generic\Response;
use Enola\Support\Error;
use Enola\Cron\Models\En_CronRequest;

/**
 * Esta clase representa el Nucleo del modulo Cron y es donde se encuentra toda la funcionalidad del mismo.
 * Este provee un unico metodo para ejecutar el cron correspondiente en base a los parametros pasados por la linea de comandos
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cron
 * @internal
 */
class CronCore{
    /** Referencia al nucleo de la aplicacion 
     * @var \Enola\Application */
    public $app;
    /** Referencia al CronRequest actual 
     * @var En_CronRequest */
    public $cronRequest;
    /** Referencia al Response actual 
     * @var Response */
    public $cronResponse;
    /** Contiene los datos de entrada que son importante para el framework
     * @var mixed */
    public $data= array('domain' => null, 'class' => null, 'method' => null);
    /** 
     * Se instancia el nucleo.
     * Se definen los parametros y se define el Cron Request actual
     * @param \Enola\Application $app
     */
    public function __construct($app, $params) {
        $this->app= $app;
        $config= $this->analyzeParameters($params);
        $this->cronRequest= new En_CronRequest($config);
        $this->cronResponse= new Response();
    }    
    
    /**
     * Ejecuta el cron correspondiente en base a los parametros pasados por la linea de comandos
     * Utilizado solo por el framework
     * Este es la funcion principal y define el flujo que va a seguir la peticion: CronManagement, CronDesdeComandos, ShellScript
     */
    public function executeCronController(){
        $class= $this->data['class'];//$this->cronRequest->getParamAll(1);
        //Analizo si llamo a los controladores del usuario o si llamo al manejador de tareas o shell script del framework
        if($class == 'CronManagement'){
            //Ejecuto el CronManagement
            $this->executeCronManagement();
        }
        if($class != 'CronManagement'){
            $method= "index";
            //Si la diferencia es mayor a 2 entre ambos arreglos de parametros quiere decir que se indico el nombre del metodo
            //if(count($this->cronRequest->getAllParams()) - count($this->cronRequest->getParams()) > 2){
            if($this->data['method'] != null){
                $method= $this->data['method'];
            }
            //Ejecuto Cron de Usuario o Shell Script del Framework
            if(strpos($class, 'Eno-') === 0){
                //Ejecuto el cron
                $this->executeShellScript(substr($class, 4), $method);
            }else{
                //Ejecuto el cron
                $this->executeCron($class, $method);
            }
        }
    }
    /**
     * Ejecuta el cron management del framework el cual analiza las tareas definidas en el archivo de configuracion
     * y ejecuta las que corresponda.
     */
    public function executeCronManagement(){
        $dateTime= new \DateTime();
        $actualFrequency= array(
            'i' => $dateTime->format('i'),
            'H' => $dateTime->format('H'),
            'd' => $dateTime->format('d'),
            'm' => $dateTime->format('m'),
            'w' => $dateTime->format('w')
        );
        $file= $this->app->context->getConfigFolderDomain() . 'cronJobs';
        $definedCrons= $this->app->context->readConfigurationFile($file)['crons'];
        $cronsToExeture= array();
        foreach ($definedCrons as $cronEsp) {
            $frecuenciaActiva= $this->activeFrequency($cronEsp['frequency'], $actualFrequency);
            if($frecuenciaActiva){
                $cronsToExeture[]= $cronEsp;
            }
        }
        //Ejecuto las tareas en la que su frecuencia sea activa
        foreach ($cronsToExeture as $cronEsp) {
            $propertiesEsp= isset($cronEsp['properties']) ? $cronEsp['properties'] : NULL;
            $this->executeCron($cronEsp['cronController'], $cronEsp['method'], $propertiesEsp);
        }        
    }
    /**
     * Ejecuta el Cron mediante el metodo indicado
     * Este es utilizado por el metodo forward del CronController y de uso interno al modulo
     * @param string $cron
     * @param string $method
     * @param array $propertiesEsp
     */
    public function executeCron($cron, $method= "index", $propertiesEsp= NULL){
        $dir= PATHAPP . 'src/crons/' . $cron . '.php';
        //Analiza si existe el archivo
        if(file_exists($dir)){
            $cronIns= $this->instanceClass($dir, $cron, $propertiesEsp);
            //Analiza si existe el metodo indicado
            if(method_exists($cronIns, $method)){
                $cronIns->$method($this->cronRequest, $this->cronResponse);                
            }else{
                Error::general_error('Cron Controller Error', 'The Cron Controller ' . $cron . ' dont implement the method ' . $method . '()');
            }
        }else{
            Error::general_error('Cron Controller Error', 'The Cron Controller ' . $cron . ' dont exist');
        }
    }
    /**
     * Ejecuta el Shell Script mediante el metodo indicado
     * Este es de uso interno al modulo
     * @param string $shell
     * @param string $method
     * @param array $propertiesEsp
     */
    public function executeShellScript($shell, $method= "index", $propertiesEsp= NULL){
        $dir= PATHFRA . 'Commands/' . $shell . '.php';
        //Analiza si existe el archivo
        if(file_exists($dir)){
            $class= "Enola\\Commands\\" . $shell;
            $shellIns= new $class();
            if($propertiesEsp != NULL){
                $this->app->dependenciesEngine->injectProperties($shellIns, $propertiesEsp);
            }
            //Analiza si existe el metodo indicado
            if(method_exists($shellIns, $method)){
                $shellIns->$method($this->cronRequest, $this->cronResponse);                
            }else{
                Error::general_error('Shell Script Error', 'The Shell Script ' . $shell . ' dont implement the method ' . $method . '()');
            }
        }else{
            Error::general_error('Shell Script Error', 'The Shell Script ' . $shell . ' dont exist');
        }
    }
    
    /**
     * Analiza los parametros y devuelvo los parametros ordenados para su posterior uso
     * El primer parametros es el index.php, el segundo es el nombre (clase) del cron y despues puede ser el metodo a ejecutar
     * y luego son todos parametros de entrada al Cron correspondiente. 
     * @param type $params
     * @return array[array]
     */
    private function analyzeParameters($params){
        //Va a contener todos los parametros sin los guiones "-" iniciales
        $realParams= array();
        //Va a contener los parametros reales. Los no usados por el framework
        $cleanParams= array();
        //CONSIGO LOS PARAMETROS REALES
        $indActual= 0;
        foreach ($params as $value) {
            //Le quito los guiones iniciales a todos
            $value= ltrim($value, '-');
            if(($indActual == 2 || $indActual == 3) && substr($value, 0, 1) == '?'){
                //Le quito el "?"
                $value= substr($value, 1);
                $this->data['method']= $value;
            }
            //Guardo a todos en realParams
            $realParams[]= $value;
            //Aumento el indice
            $indActual++;
        }
        //CONSIGO LOS PARAMETROS LIMPIOS, QUE NO USA EL FRAMEWORK
        $posSlice= strpos($params[1], 'domain=') ? 3 : 2;
        $indActual= 0;
        foreach (array_slice($params, $posSlice) as $value) {
            //Le quito los guiones iniciales a todos
            $value= ltrim($value, '-');
            //Analizo si debo guardar en cleanParams
            if($indActual == 0 && substr($value, 0, 1) == '?'){
                //Le quito el "?"
                $value= substr($value, 1);
            }else{
                //Guardo los parametros que no usa el framework
                $cleanParams[]= $value;
            }
            //Aumento el indice
            $indActual++;
        }        
        
        //ANALIZO DATOS PARA EL CORE
        if(strpos($params[1], 'domain=') !== false){
            $pos= strpos($params[1], 'domain=');
            $this->data['domain']= substr($params[1], $pos + 7);
        }
        if(strpos($params[1], 'domain=') === false){
            $this->data['class']= ltrim($params[1], '-');
        }else{
            $this->data['class']= ltrim($params[2], '-');
        }
        
        return array("real" => $realParams, "clean" => $cleanParams);
    }
    private function validInterval($frequencyMin, $min){
        $contain= strpos($frequencyMin, '*/');
        if($contain !== FALSE){            
            $interval= intval(substr($frequencyMin, 2));
            return ($min % $interval) == 0;
        }else if($frequencyMin == '*'){
            return TRUE;
        }else{
            return $frequencyMin == $min;
        }
    }
    /**
     * Retorna si una frecuencia esta activa en determinado dateTime
     * @param string $frequency
     * @param array $actualFrequency
     * @return boolean
     */
    public function activeFrequency($frequency, $actualFrequency){
        $frequency= explode(' ', trim($frequency, ' '));        
        $condition= $this->validInterval($frequency[0], $actualFrequency['i']) && $this->validInterval($frequency[1], $actualFrequency['H']) 
                && $this->validInterval($frequency[2], $actualFrequency['d']) && $this->validInterval($frequency[3], $actualFrequency['m']) 
                && $this->validInterval($frequency[4], $actualFrequency['w']);
        return $condition;
    }
    /**
     * En base a una direccion y un nombre instancia la clase correspondiente e injecta las propiedades correspondientes
     * @param string $dir
     * @param string $name
     * @param array $propertiesEsp
     * @return \Enola\Cron\class
     */
    protected function instanceClass($dir, $name, $propertiesEsp= NULL){
        require_once $dir;
        $dir= explode("/", $name);
        $class= $dir[count($dir) - 1];
        $instance= new $class();
        if($propertiesEsp != NULL){
            $this->app->dependenciesEngine->injectProperties($instance, $propertiesEsp);
        }
        return $instance;
    }
}