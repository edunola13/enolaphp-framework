<?php
namespace Enola\Http\Models;

/**
 * Esta interface establece los metodos que debe proveer un Middleware para que el framework lo pueda administrar correctamente.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Http
 */
interface Middleware {
    /**
     * Realiza la ejecucion del middleware
     * @param En_HttpRequest $request
     * @param En_HttpResponse $response
     */
    public function handle(En_HttpRequest $request, En_HttpResponse $response);
}