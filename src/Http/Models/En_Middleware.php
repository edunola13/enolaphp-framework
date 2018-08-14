<?php
namespace Enola\Http\Models;
use Enola\Support\Generic\GenericLoader;

/**
 * Esta clase implementa la interface Middleware dejando el metodo handle vacio para que el usuario sobrescriba. Ademas agrega 
 * propiedades y comportamiento propia del modulo HTTP y de los modulos de soporte mediante distintas clases para que luego
 * los nuevos controllers del usuario puedan extender de esta y aprovechar toda la funcionalidad provista por el Core 
 * del Framework y el modulo Http. 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 */
class En_Middleware extends GenericLoader implements Middleware{
    use \Enola\Support\Generic\GenericBehavior;
    /** Errores que levanto el controlador
     * @var mixed */
    public $errors;  
    /**
     * Inicializa el controlador llamando al constructor de su padre y seteando el HttpRequest correspondiente
     */
    function __construct() {
        parent::__construct('middleware');
    }
    /**
     * Realiza la ejecucion del middleware
     * @param En_HttpRequest $request
     * @param En_HttpResponse $response
     */
    public function handle(En_HttpRequest $request, En_HttpResponse $response){}    
}