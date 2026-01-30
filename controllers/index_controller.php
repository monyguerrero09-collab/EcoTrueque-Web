<?php

/**
 * Controller por defecto si no se usa el routes
 *
 */
class IndexController extends AppController
{

    public function index()
    {
        $this->title = 'Inicio'; // O cualquier título que desees para la página principal
        $this->subtitle = '¡Bienvenido a Trueque Ecológico!'; // O un subtítulo
    }

    /**
     * Acción para la página de Contacto
     * URL: /index/contact
     */
    public function contact()
    {
        // Define los títulos para la plantilla principal
        $this->title = 'Contacto';
        $this->subtitle = 'Ponte en contacto con nosotros';

        // (Aquí puedes añadir lógica si quieres procesar un formulario de contacto)
        // Ejemplo:
        // if (Input::hasPost('nombre_del_formulario')) {
        //     // ... enviar email ...
        //     Flash::valid('¡Mensaje enviado!');
        // }
    }
}
