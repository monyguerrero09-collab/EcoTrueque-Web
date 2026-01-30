<?php

class ContactController extends AppController
{
    public function index()
    {
        $this->title = 'Formulario de Contacto';
        $this->subtitle = 'Envíanos tus dudas o sugerencias';
    }

    public function enviar()
    {
        View::select('index'); // Muestra la misma vista después del envío

        if (Input::hasPost('nombre', 'email', 'asunto', 'mensaje')) {
            $nombre  = Input::post('nombre');
            $email   = Input::post('email');
            $asunto  = Input::post('asunto');
            $mensaje = Input::post('mensaje');

            // Cargar el modelo
            $Contact = Load::model('contact');

            if ($Contact->guardarMensaje($nombre, $email, $asunto, $mensaje)) {
                Flash::valid('💌 Tu mensaje ha sido enviado exitosamente. ¡Gracias por contactarnos!');
            } else {
                Flash::error('❌ Ocurrió un error al enviar tu mensaje. Por favor, intenta nuevamente.');
            }
        }
    }
}

