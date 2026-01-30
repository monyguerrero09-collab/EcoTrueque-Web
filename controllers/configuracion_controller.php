<?php

class ConfiguracionController extends AppController
{
    public function index()
    {
        View::template('admintle');
        $this->title = 'Configuración';
    }
}
