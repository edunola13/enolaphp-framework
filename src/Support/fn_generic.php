<?php
namespace Enola\Support;
use Enola\EnolaContext;
/*
 * Este modulo incluye funciones utiles
 */    
/**
 * Retorna la instancia de la aplicacion
 * @return \Enola\Application
 */
function app() {
    return EnolaContext::getInstance()->app;
}
/**
 * Retorna la isntancia del contexto
 * @return EnolaContext
 */
function context() {
    return EnolaContext::getInstance();
}
/**
 * Retorna el manejador de dependencias
 * @return DependencyEngine
 */
function dependenciesEngine() {
    return EnolaContext::getInstance()->app->dependenciesEngine;
}
/** 
 * @param mixed
 */
function dd($data) {
    var_dump($data);
    exit;
}