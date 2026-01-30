<?php
/**
 * Helper Bhtml (Bootstrap HTML)
 * Funciones auxiliares para generar código HTML optimizado para Bootstrap.
 */
class Bhtml
{
    /**
     * Genera una etiqueta <img>
     *
     * @param string $src Fuente de la imagen (ruta o data URI)
     * @param string $alt Texto alternativo
     * @param array $attrs Array asociativo de atributos HTML (clase, estilo, id, etc.)
     * @return string
     */
    public static function img($src, $alt = '', $attrs = [])
    {
        $html_attrs = '';
        foreach ($attrs as $key => $value) {
            $html_attrs .= " {$key}=\"{$value}\"";
        }

        // Se usa htmlspecialchars para asegurar que el texto alt y src sean seguros
        return '<img src="' . htmlspecialchars($src, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '"' . $html_attrs . '>';
    }

    // Puedes añadir otras funciones aquí, como Bhtml::link(), Bhtml::button(), etc.

}
