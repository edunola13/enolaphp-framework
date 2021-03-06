<?php
namespace Enola\Cron\Models;
use Enola\Support\Generic\GenericLoader;

/**
 * Esta clase representa a un Cron Job. Agrega propiedades y comportamiento propia del modulo Cron y de los modulos
 * de soporte mediante distintas clases para que luego los nuevos crons del usuario puedan extender de esta y aprovechar 
 * toda la funcionalidad provista por el Core del Framework y el modulo Cron. 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cron
 */
class En_CronController extends GenericLoader{
    use \Enola\Support\Generic\GenericBehavior;
    /** Carpeta donde se encuentran las vistas
     * @var string */
    protected $viewFolder;
    /** Errores que levanto el controlador
     * @var mixed */
    public $errors; 
    /**
     * Inicializa el controlador llamando al constructor de su padre
     */
    function __construct(){
        parent::__construct('cron');
        $this->viewFolder= $this->context->getPathApp() . 'src/view/';
    }
    /**
     * Funcion que actua cuando acurre un error en la validacion
     */
    protected function error(){        
    }    
    /**
     * Funcion que carga los datos usados por la vista
     */
    protected function loadData(){        
    }    
    /**
     * Funcion que carga los datos usados por la vista de 
     */
    protected function loadDataError(){        
    }
}