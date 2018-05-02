<?php
namespace Enola\Http\Models;
use Enola\Support\Generic\Response;
use Enola\Http\UrlUri;

/**
 * Esta clase representa una respuesta HTTP y por lo tanto provee todas las propiedades basicas de una respuesta HTTP como
 * asi tambien propiedades de respuesta propias del framework.
 * Ademas provee comportamiento basico para redireccionar solicitudes. 
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 */
class En_HttpResponse extends Response{

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585
    /** Referencia al HttpRequest actual 
     * @var En_HttpRequest */
    protected $httpRequest;
    /**
     * Constructor
     */
    public function __construct($request) {
        $this->httpRequest= $request;
        self::$instance= $this;
    }
    /**
     * Devuelve el codigo de respuesta actual
     * @return int
     */
    public function getStatusCode(){
        return http_response_code();
    }
    /**
     * Setea el codigo de respuesta
     * @param int $code
     */
    public function setStatusCode($code){
        http_response_code($code);
    }
    /*
     * Retorna si la respuesta esta todo bien. digamos si el codigo de respuesta es 200
     * @return boolean
     */
    public function isOk(){
        return (http_response_code() == 200);
    }
    /*
     * Devuelve los headers de la peticion
     * @return array
     */
    public function getHeaders(){
        //getallheaders() ver esto
        return headers_list();
    }
    /**
     * Setea los headers de la respuesta
     * @param string $header
     */
    public function setHeaders($header){
        header($header);
    }
    /**
     * Setea un parametro del header de la respuesta
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value){
        header($name . ': ' . $value);
    }
    /**
     * Remueve el parametro indicado o todos los parametros del header de la respuesta
     * @param string $name
     */
    public function removeHeader($name = NULL){
        header_remove($name);
    }
    /**
     * Devuelve todas las cookies
     * @return array
     */
    public function getCookies(){
        return filter_input_array(INPUT_COOKIE);        
    }
    /**
     * Devuelve la cookie asociado con un nombre.
     * @param string $name
     * @return array - null
     */
    public function getCookie($name){
        return filter_input(INPUT_COOKIE, $name);
    }
    /**
     * Setea parametros de cookie
     * @param int $lifetime
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setCookieParams($lifetime, $path=NULL, $domain=NULL, $secure=FALSE, $httponly= FALSE){
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    }
    /**
     * Setea una cookie
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setCookie($name, $value, $expire=0, $path="/", $domain="", $secure=FALSE, $httponly= FALSE){
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public function setExpires($expire=0){
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + ($expire)).'GMT');
    }
    /**
     * Setea el tipo de contenido a enviar
     * @param string $contentType
     * @param string $charset
     */
    public function setContentType($contentType, $charset=NULL){
        $this->setHeader("Content-Type", $contentType);
        if($charset != NULL){
            $this->setHeader("charset", $charset);
        }
    }
    /**
     * Retorna si el header de la respuesta ya fue enviada
     * @return boolean
     */
    public function isSent(){
        return headers_sent();
    }
    /**
     * Envia un archivo como respuesta. Se indican distintos parametros del header
     * @param string $file
     * @param string $name
     * @param string $contentType
     * @param string $contentDisposition
     */
    public function sendFile($file, $name=NULL, $contentType='application/octet-stream', $contentDisposition='attachment'){
        if($name == NULL){
            $name= basename($file);
        }
        header('Content-Description: File Transfer');
        header('Content-Type: '.$contentType);
        header('Content-Disposition: '.$contentDisposition.'; filename="'.$name.'"');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);        
    }
    /**
     * Metodo para API REST.
     * Envia una respuesta json con un codigo de respuesta codificando los datos
     * @param int $code
     * @param string $data
     * @param int $options
     * @param string $contentType
     */
    public function sendApiRestEncode($code=200, $data = NULL, $options= 0, $contentType='application/json'){        
        $this->sendApiRest($code, json_encode($data, $options), $contentType);
    }
    /**
     * Metodo para API REST.
     * Envia una respuesta json con un codigo de respuesta
     * @param int $code
     * @param string $jsonString
     * @param string $contentType
     */
    public function sendApiRest($code=200, $jsonString= '', $contentType='application/json'){
        $this->setStatusCode($code);
        $this->setContentType($contentType);
        $this->setContent($jsonString);
        $this->sendContent();
    }
    /**
     * Redirecciona a otra pagina pasando una uri relativa a la aplicacion
     * @param string $uri
     */
    public function redirect($uri){
        UrlUri::redirect($this->httpRequest, $uri);
    }
    /**
     * Redirecciona a una pagina externa a la aplicacion actual
     * @param string $url
     */
    public function external_redirect($url){
        UrlUri::externalRedirect($url);
    }
}