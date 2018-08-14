<?php
namespace Enola\Http\Models;
use Enola\Support\Generic\GenericLoader;
use Exception;

/**
 * Esta clase implementa la interface Middleware dejando el metodo handle vacio para que el usuario sobrescriba. Ademas agrega 
 * propiedades y comportamiento propia del modulo HTTP y de los modulos de soporte mediante distintas clases para que luego
 * los nuevos controllers del usuario puedan extender de esta y aprovechar toda la funcionalidad provista por el Core 
 * del Framework y el modulo Http. 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 */
class En_Handler extends GenericLoader implements Handler{
    use \Enola\Support\Generic\GenericBehavior;
    /**
     * Inicializa el controlador llamando al constructor de su padre
     */
    function __construct() {
        parent::__construct('handler');
    }
    /**
     * Realiza la ejecucion del handler
     * Si retorna true indica que el error fue manejado, en caso contrario no
     * @param En_HttpRequest $request
     * @param En_HttpResponse $response
     * @param Exception $e
     */
    public function handle(En_HttpRequest $request, En_HttpResponse $response, Exception $e){}    
}