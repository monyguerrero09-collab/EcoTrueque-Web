<?php

class Mensaje extends ActiveRecord
{
    /**
     * initialize: Configuración y Relaciones
     */
    public function initialize()
    {
        // 1. Aseguramos que use la tabla 'mensaje' (singular) tal cual está en tu BD
        // Kumbia suele detectarlo automático, pero si falla, esto lo fuerza:
        $this->set_source('mensaje');

        // 2. Relación: Un mensaje pertenece a un Trueque
        $this->belongs_to('trueque');

        // 3. Relación: El remitente es un Usuario
        // Usamos 'fk: remitente_id' para indicar qué columna conecta
        // Usamos 'alias: Remitente' para poder llamar $mensaje->getRemitente()->nombre
        $this->belongs_to('usuario', 'fk: remitente_id', 'alias: Remitente');

        // 4. Relación: El destinatario es un Usuario
        $this->belongs_to('usuario', 'fk: destinatario_id', 'alias: Destinatario');
    }
}
?>


