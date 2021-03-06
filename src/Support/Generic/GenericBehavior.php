<?php
namespace Enola\Support\Generic;

use Enola\EnolaContext;
/**
 * Esta trait contiene comportamiento comun que es utilizado por los diferentes controladores de los diferentes modulos 
 * como el controller http o el controller cron. Ademas se puede utilizar en la clase que el usuario desee
 * si necesita el comportamiento aca definido.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
trait GenericBehavior {
    /**
     * Valida las variables de un objeto o de un array en base a una definicion de configuracion de validacion
     * Se puede utilizar la libreria que se desee pere debe respetar la inerfaz de la proporcionada por el framework.
     * @param type $var
     * @param array $ruleset
     * @param string $locale
     * @param string $lib
     * @return bool
     */
    protected function validate($var, $ruleset = null, $locale = null, $lib= '\Enola\Lib\Validation\ValidationFields', $dir= null){
        $validation= new $lib($locale);
        if($dir == null){
            $dir= PATHAPP . 'src/content/messages';
        }
        $validation->dir_content= $dir;
        
        $ruleset= $ruleset != null ? $ruleset : $this->configValidation();
        
        if(is_object($var)){
            $reflection= new Reflection($var);
            foreach ($ruleset as $key => $regla) {
                $validation->add_rule($key, $reflection->getProperty($key), $regla);
            }
        }else{
            foreach ($ruleset as $key => $regla) {
                $field= isset($var[$key]) ? $var[$key] : NULL;
                $validation->add_rule($key, $field, $regla);
            }
        }
        if(! $validation->validate()){
            //Consigo los errores y retorno FALSE
            $this->errors= $validation->error_messages();
            return FALSE;
        }else{
            return TRUE;            
        }
    }     
    /**
     * Devuelve la configuracion de validacion
     * Deberia ser sobrescrita por la clase que desee validar, si no, no validara nada.
     * @return array
     * @deprecated since version 1.1.4
     */
    protected function configValidation(){
        return array();
    }
    /**
     * Carga una vista PHP pasandole parametros y teniendo la oportunidad de guardar de retornar la vista para guardar 
     * en una variable.
     * Se crea una instancia de la clase Enola\Support\View en la variable $view
     * @param string $view_template
     * @param array $params
     * @param boolean $buffer
     * @return string - void
     */
    protected function loadView($view_template, $params = NULL, $buffer = FALSE){
        if($params != NULL && is_array($params)){
            foreach ($params as $key => $value) {
                $$key= $value;
            }
        }
        //Creo var view
        //$view= new View();
        if($buffer){
            ob_start();            
        }
        include $this->viewFolder . $view_template . '.php';
        if($buffer){
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }    
    /**
     * Carga la instancia de una clase pasada como parametro en una variable del objeto actual con el nombre indicado
     * @param string $class
     * @param string $name
     */
    protected function addInstance($class, $name = ""){
        if($name == ""){
            $name= $class;
        }
        $this->$name= new $class();
    }
    /**
     * Inyecta las dependencias que tienen seteado el tipo en load_in
     * @param string $type
     */
    protected function injectDependencyOfType($type){
        EnolaContext::getInstance()->app->dependenciesEngine->injectDependencyOfType($this,$type);
    }
    /**
     * Carga las dependencias indicadas en la instancia actual en las propiedades correspondientes
     * @param array $dependencies / property => dependency
     */
    protected function injectDependencies(array $dependencies){
        EnolaContext::getInstance()->app->dependenciesEngine->injectDependencies($this,$dependencies);
    }
    /**
     * Carga la dependencias indicada en la instancia actual en la propiedad indicada
     * @param string $propertyName
     * @param string $dependencyName
     */
    protected function injectDependency($propertyName, $dependencyName){
        EnolaContext::getInstance()->app->dependenciesEngine->injectDependency($this,$propertyName,$dependencyName);
    }

    /**
     * Devuelve la instancia creada automaticamente por el framework en su carga
     * @param string $var
     * @return EnolaContext
     */
    protected function vars($var = null){
        if(is_null($var)){
            return EnolaContext::getInstance()->getContextVars();
        }
        return EnolaContext::getInstance()->getContextVar($var);
    }
}
