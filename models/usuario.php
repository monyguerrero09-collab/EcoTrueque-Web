<?php
/**
 * ✅ Modelo Usuario adaptado para Session (sin Auth de Kumbia)
 */
class Usuario extends ActiveRecord
{
    /**
     * ✅ Validaciones básicas
     */
    protected function initialize()
    {
        $this->validates_presence_of('nombre', 'message: El nombre es obligatorio.');
        $this->validates_presence_of('email', 'message: El correo es obligatorio.');
        $this->validates_presence_of('password', 'message: La contraseña es obligatoria.');
    }

    /**
     * ✅ Encriptar contraseña antes de guardar
     */
    protected function before_save()
    {
        if (!empty($this->password)) {
            // Evitar doble hash al editar
            if (strpos($this->password, '$2y$') !== 0) {
                $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            }
        }
    }

    /**
     * ✅ Buscar por correo
     */
    public static function findByEmail($email)
    {
        $email = addslashes(trim($email));
        return (new self())->find_first("email = '$email'");
    }

    /**
     * ✅ Relación con trueques
     */
    public $has_many = [
        'trueque' => [
            'model' => 'Trueque',
            'fk'    => 'usuario_id'
        ]
    ];
}
