<?php
/**
 * 🌿 Modelo: Contacto
 * Guarda los mensajes enviados desde el formulario de contacto.
 */
class Contact extends ActiveRecord
{
    /**
     * Guarda un mensaje en la base de datos
     */
    public function guardarMensaje($nombre, $email, $asunto, $mensaje)
    {
        $this->nombre      = $nombre;
        $this->email       = $email;
        $this->asunto      = $asunto;
        $this->mensaje     = $mensaje;
        $this->fecha_envio = date('Y-m-d H:i:s');

        return $this->save();
    }
}