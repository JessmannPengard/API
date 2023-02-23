<?php

require_once('routing/router.php');

// Obtener ruta y redirigir
$route = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
$router = new Router($route);
