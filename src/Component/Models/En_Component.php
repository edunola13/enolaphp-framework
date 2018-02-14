<?php
namespace Enola\Component\Models;
use Enola\Support\Generic\Request;
use Enola\Support\Generic\Response;
use Enola\Support\Generic\GenericLoader;

/**
 * Esta clase implementa la interface Component dejando el metodo renderin vacio para que el usuario sobrescriba. Ademas
 * agrega propiedades y comportamiento propia del modulo HTTP y Component y de los modulos de soporte mediante distintas 
 * clases para que luego los nuevos components del usuario puedan extender de esta y aprovechar toda la funcionalidad 
 * provista por el Core del Framework y el modulo Component.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Component
 */
class En_Component extends GenericLoader implements Component{ 
    use \Enola\Support\Generic\GenericBehavior;
    /** Carpeta donde se encuentran las vistas
     * @var string */
    protected $viewFolder;
    /** Errores que levanto el controlador
     * @var mixed */
    public $errors; 
    /**
     * Inicializa el component llamando al constructor de su padre y seteando el HttpRequest correspondiente
     */
    public function __construct() {        
        parent::__construct('component');
        $this->viewFolder= $this->context->getPathApp() . 'source/view/';
    }    
    /**
     * Realiza el renderizado del componente
     * @param Request $request
     * @param Response $response
     * @param type $params
     */
    public function rendering(Request $request, Response $response, $params = NULL){        
    }
}