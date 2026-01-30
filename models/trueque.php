<?php

class Trueque extends ActiveRecord
{
    // ✅ FORZAR nombre de tabla correcto
    protected $source = 'trueque';

    /**
     * Validaciones y Configuración
     */
    protected function initialize()
    {
        $this->validates_presence_of('titulo', 'message: El título es obligatorio.');
        $this->validates_presence_of('descripcion', 'message: La descripción es obligatoria.');
        $this->validates_presence_of('ofrezco', 'message: El campo "Ofrezco" es obligatorio.');
        $this->validates_presence_of('busco', 'message: El campo "Busco" es obligatorio.');
        $this->validates_presence_of('usuario_id', 'message: Debes iniciar sesión.');

        $this->validates_numericality_of('latitud', [
            'message' => 'La latitud no es válida.',
            'allow_null' => true
        ]);

        $this->validates_numericality_of('longitud', [
            'message' => 'La longitud no es válida.',
            'allow_null' => true
        ]);

        $this->validates_length_of('titulo', [
            'maximum' => 120,
            'message' => 'El título es demasiado largo.'
        ]);

        $this->validates_length_of('descripcion', [
            'maximum' => 5000,
            'message' => 'La descripción es demasiado larga.'
        ]);
    }

    /**
     * ✅ Método para obtener la imagen (Base64 o placeholder)
     */
    public function getImagenSrc()
    {
        if (!empty($this->imagen_mime) && !empty($this->imagen_base64)) {
            return "data:{$this->imagen_mime};base64,{$this->imagen_base64}";
        }

        return "https://placehold.co/400x200";
    }

    /**
     * Guardar imagen física (opcional)
     */
    public function before_save()
    {
        if ($file = Upload::factory('imagen')) {
            $file->setExtensions(['jpg','jpeg','png','webp']);
            $file->setPath("img/trueques/");

            if ($file->isUploaded()) {
                if ($file->save()) {
                    $this->imagen = $file->getFileName();
                } else {
                    Flash::error("Error al guardar la imagen física.");
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Log después de crear
     */
    public function after_create()
    {
        Logger::info("Nuevo trueque publicado por usuario ID {$this->usuario_id}");
    }

    /**
     * Relación con Usuario
     */
    public $belongs_to = [
        'usuario' => [
            'model' => 'Usuario',
            'fk' => 'usuario_id'
        ]
    ];
}

