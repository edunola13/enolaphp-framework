<?php
namespace Enola\Http\Models;
use Exception;
/**
 * Esta interface establece los metodos que debe proveer un Handler de excepciones para que el framework lo pueda administrar correctamente.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 */
interface Handler {
    /**
     * Realiza la ejecucion del handler
     * Si retorna true indica que el error fue manejado, en caso contrario no
     * @param En_HttpRequest $request
     * @param En_HttpResponse $response
     * @param Exception $e
     */
    public function handle(En_HttpRequest $request, En_HttpResponse $response, Exception $e);
}