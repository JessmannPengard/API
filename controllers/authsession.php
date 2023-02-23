<?php

class SessionController
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function isAuthenticated()
    {
        // Verificar si el usuario está autenticado
        
    }
}
