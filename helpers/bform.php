<?php

class Bform
{
    /**
     * Asigna valores por defecto a los atributos.
     */
    protected static function attrsdefault($attrs, $defaults)
    {
        foreach ($defaults as $k => $v) {
            if (isset($attrs[$k])) {
                if (strpos($attrs[$k], $v) === false) {
                    $attrs[$k] .= ' ' . $v;
                }
            } else {
                $attrs[$k] = $v;
            }
        }
        return $attrs;
    }

    /**
     * Genera un botón de aceptar con estilo Bootstrap.
     * Ejemplo: Bform::btn_aceptar("Aceptar")
     */
    public static function btn_aceptar($text = "Aceptar", $attrs = [])
    {
        $text = "💾 " . $text;
        $attrs = self::attrsdefault($attrs, ["class" => "btn btn-primary"]);
        return Form::submit($text, $attrs);
    }

    /**
     * Genera un input de tipo texto con estilos Bootstrap.
     */
    public static function text($field, $attrs = [], $value = null)
    {
        $attrs = self::attrsdefault($attrs, ["class" => "form-control"]);
        return Form::input('text', $field, $attrs, $value);
    }
}