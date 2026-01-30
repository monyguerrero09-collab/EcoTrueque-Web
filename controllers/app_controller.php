<?php
/**
 * Controlador principal de la aplicación.
 * Compatible con KumbiaPHP 1.x
 *
 * ESTA VERSIÓN INCLUYE EL FILTRO DE AUTORIZACIÓN (ACL) CORRECTO.
 */
class AppController extends Controller
{
    /**
     * El constructor de KumbiaPHP 1.x
     */
    final public function __construct($router)
    {
        parent::__construct($router);

        // Inclusión de la biblioteca de autenticación (esto está bien)
        include_once CORE_PATH . 'libs/auth/auth.php';

        // Carga del modelo Usuario (esto está bien)
        $this->load_model('usuario');
    }

    /**
     * Método ejecutado antes de cada acción del controlador.
     * Aquí es donde va la Lógica de Control de Acceso (ACL).
     */
    protected function before_filter()
    {
        // --- 1. Obtener la ruta actual y el rol ---

        // $controller nos da el controlador actual (ej. 'trueque', 'dashboard', 'roles')
        $controller = $this->router->controller;

        // $action nos da la acción (ej. 'publicar', 'index')
        $action = $this->router->action;

        // Definimos las áreas que SÓLO el admin puede ver
        // CORREGIDO: Se usa solo 'roles' como me indicaste.
        $admin_controllers = ['roles'];

        // --- 2. Lógica de Autenticación (¿Está logueado?) ---

        // Primero, verificamos si el usuario está intentando acceder a una página protegida
        // (ni 'session' ni 'index' requieren login)
        if ($controller != 'session' && $controller != 'index') {

            if (!Auth::check()) {
                Flash::error('Debes iniciar sesión para acceder a esta área.');
                return Redirect::to('session/index'); // Redirigir al login
            }
        }

        // --- 3. Lógica de Autorización (¿Tiene el ROL correcto?) ---

        // Si el usuario SÍ está logueado (Auth::check() es true)
        if (Auth::check()) {

            // Verificamos el rol (1 = admin, cualquier otra cosa = Usuario Estándar)
            $rol = Auth::user()['rol'];
            $es_admin = ($rol == 1);

            // SI el controlador al que intenta acceder SÓLO es para admins
            if (in_array($controller, $admin_controllers)) {

                // Y el usuario NO es admin
                if (!$es_admin) {
                    // ¡AQUÍ ESTÁ EL ERROR QUE VEÍAS!
                    // Lo bloqueamos y lo mandamos al dashboard.
                    Flash::error('Acceso denegado. Solo administradores.');
                    return Redirect::to('dashboard/index');
                }
            }

            // ¡IMPORTANTE! Si es un 'Usuario Estándar' y el controlador NO es 'roles'
            // (ej. es 'trueque/publicar'), el código NO entra en el if de arriba
            // y KumbiaPHP lo deja pasar.
        }
    }

    /**
     * Método que se ejecuta después de la acción y antes de renderizar la vista.
     */
    protected function after_filter()
    {
        // ...
    }
}