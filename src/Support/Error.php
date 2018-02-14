<?php
namespace Enola\Support;
use Enola\Http\UrlUri;

/**
 * Esta clase prove funciones para registrar los errores
 * Como no es necesario mantener ningun estado los metodos se pueden acceder estaticamente
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class Error{
    /**
     * Crea una respuesta de error php - usada por el manejador de errores definido por el framework
     * Escribe en log
     * @param string $type
     * @param $level
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public static function error_php($type, $level, $message, $file, $line){
        self::write_log($message, $type, $file, $line);
        if(error_reporting()){
            include PATHAPP . 'errors-info/error_php.php';
        }
    }    
    /**
     * Crea una respuesta de error 404
     * Usada por el framework y/o el usuario
     */
    public static function error_404(){
        $head= '404 Pagina no Encontrada';
        $message= 'La pagina que solicitaste no existe';
        Http\UrlUri::setEstadoHeader(404);
        include PATHAPP . 'errors-info/error_404.php';
        exit;
    }    
    /**
     * Crea una respuesta de error general
     * Usada por el framework y/o el usuario
     * Escribe en log
     * @param string $head
     * @param string $message
     * @param string $template
     * @param int $code_error Solo aplica si esta en modo HTTP
     */
    public static function general_error($head, $message, $template = 'general_error', $code_error = 500){
        self::write_log($message, 'General Error');
        if(ENOLA_MODE == 'HTTP' && class_exists('\Enola\Http\UrlUri')){UrlUri::setEstadoHeader($code_error);}
        if(error_reporting()){
            include PATHAPP . 'errors-info/' . $template . '.php'; 
        }        
    }
    /**
     * Crea o abre un archivo de log y escribe el error correspondiente
     * Escribe en log
     * @param String $chain
     * @param String $type
     * @param string $file
     * @param string $line
     */
    public static function write_log($chain, $type, $file="", $line=""){
        /*if(filesize(PATHAPP . 'logs/log.txt') > 100000){           
            $arch= fopen(PATHAPP . 'logs/log.txt', "w");
            fclose($arch); 
        }*/
        $arch = fopen(PATHAPP . 'logs/log-' . date('Y-m-d') . '.txt', "a+"); 
        if(ENOLA_MODE == 'HTTP'){
            fwrite($arch, "[".date("Y-m-d H:i:s.u")." ".filter_input(INPUT_SERVER, 'REMOTE_ADDR')." "." - $type ] ".$chain." - $file - $line \n");
        }else{
            fwrite($arch, "[".date("Y-m-d H:i:s.u")." MODE CLI "." - $type ] ".$chain." - $file - $line \n");
        }
        fwrite($arch, "---------- \n");
        fclose($arch);
    }    
    /**
     * Analiza si se envia a traves de un parametro get un error HTTP
     * Escribe en log
     */
    public static function catch_server_error(){
        $enolaError= filter_input(INPUT_GET, 'error_apache_enola');
        if($enolaError){
            //Cargo el archivo con los errores
            $errores= UrlUri::httpStates();
            $errores= parse_properties($errores);
            //Escribo el Log
            self::write_log('error_http', $errores[$enolaError]);
            //Muestro el error correspondiente
            self::general_error('Error ' . $enolaError, $errores[$enolaError] , 'general_error', $enolaError);
            //No continuo la ejecucion
            exit;
        }
    }

    /*
     * Sector Informacion
     * Este modulo contiene funciones utilizadas por el framework para mostrar informacion al usuario
     */
    /**
     * Muestra un mensaje al usuario
     * @param string $title
     * @param string $message
     */ 
    public static function display_information($title, $message){
        include PATHAPP . 'errors-info/information.php'; 
    }
}