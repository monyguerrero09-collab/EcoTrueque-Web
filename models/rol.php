<?php
/**
 * Modelo Rol
 */
class Rol extends ActiveRecord
{
    protected $source = "roles";
    /**
     * Obtiene todos los roles activos
     */
    public static function getActivos()
    {
        return (new self())->find("activo = 1");
    }

    /**
     * Obtiene roles para select
     */
    public static function getForSelect()
    {
        $roles = self::getActivos();
        $options = [];

        foreach ($roles as $rol) {
            $options[$rol->nombre] = $rol->icono . ' ' . $rol->nombre;
        }

        return $options;
    }
}