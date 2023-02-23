<?php

// Clase que controla el enrutamiento y dirige a los controladores requeridos
class Router
{
    private $controller;
    private $route;

    public function __construct($route = "")
    {
        $this->route = $route;
        $this->matchRoute();
    }

    public function matchRoute()
    {
        // Dividimos la ruta
        $url = explode("/", $this->route);
        // Seleccionamos la parte que define el controlador
        $this->controller = $url[2] . "Controller";
        // Seleccionamos la ruta del controlador
        $route_controller = __DIR__ . "/../Controllers/" . $this->controller . ".php";
        // Cargamos el controlador
        if (file_exists($route_controller)) {
            require_once($route_controller);
        } else {
            // Si no existe devolvemos error 404
            echo "Error 404: page not found";
            die();
        }
    }

}