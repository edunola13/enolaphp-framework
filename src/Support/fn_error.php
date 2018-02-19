<?php
namespace Enola\Support;
use Enola\Support\Error;
use Enola\Http\UrlUri;
/*
 * Este modulo Maneja los errores de la aplicacion
 * Contiene tambien la seccion de Informacion del Framework hacia el usuario
 */    
/**
 * Funcion para manejar los errores php. 
 * Esta se superpone a la propia de php cuando es seteada en el nucleo.php
 * @param $level
 * @param string $message
 * @param string $file
 * @param int $line
 * @return boolean
 */
function _error_handler($level, $message, $file, $line){
    if (!(error_reporting() & $level)) {
        //Agrega el Log
        Error::write_log($message, 'Level Error: '. $level, $file, $line);
        // Segun el nivel de error veo si agarro o no la excepcion. si entra aca no hago nada
        return;
    }
    //Analizo el error que se produjo y aviso del mismo.
    //Segun el error termino el flujo de ejecucion o continua
    switch ($level) {
        case E_USER_ERROR:
            Error::error_php('Error', $level, $message, $file, $line);
            if(ENOLA_MODE == 'HTTP'){UrlUri::setEstadoHeader(500);}
            exit(1);
            break;

        case E_USER_WARNING:
            Error::error_php('Warning', $level, $message, $file, $line);
            break;

        case E_USER_NOTICE:
            Error::error_php('Notice', $level, $message, $file, $line);
            break;

        default:
            Error::error_php('Unknown', $level, $message, $file, $line);
            break;
    }
    // No ejecutar el gestor de errores interno de PHP
    return true;      
}    
/**
 * Funcion que se va a ejecutar en el cierre de ejecucion de la aplicacion.
 * La vamos a utilizar para manejar los errores fatales
 */
function _shutdown(){
    if(!is_null($e = error_get_last())){
        //Se podria agregar mas errores en el IF, ver set error handler en PHP para ver cuales no son manejados con esa funcion
        //Si no son manejados con esa funcion todos cierran el programa directamente
        if($e['type'] == E_ERROR || $e['type'] == E_PARSE || $e['type'] == E_STRICT){
            if(!(error_reporting() & $e['type'])){
                Error::write_log($e['message'], $e['type'], $e['file'], $e['line']);
            }
            else{
                Error::error_php('Error Fatal - Parse - Strict', $e['type'], $e['message'], $e['file'], $e['line']);
            }
            if(ENOLA_MODE == 'HTTP'){UrlUri::setEstadoHeader(500);}
        }
    }
}

//Define un manejador de excepciones - definido en el modulo errores
set_error_handler('\Enola\Support\_error_handler');
//Define un manejador de fin de cierre - definido en el modulo de errores
register_shutdown_function('\Enola\Support\_shutdown'); 