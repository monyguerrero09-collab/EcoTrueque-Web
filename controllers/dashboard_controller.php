<?php
/**
 * Controlador del Dashboard
 * Muestra la página principal después de iniciar sesión.
 */
class DashboardController extends AppController
{
    /**
     * Método que se ejecuta antes de cada acción.
     * Verifica si el usuario ha iniciado sesión.
     */
    protected function before_filter()
    {
        // Si el usuario NO está logueado, redirigir a la página de inicio de sesión.
        if (!Auth::check()) {
            Flash::info('Debes iniciar sesión para acceder al dashboard.');
            Redirect::to('session');
            return false; // Detener la ejecución
        }
    }

    /**
     * Acción principal del Dashboard.
     * Se ejecuta por defecto al acceder a /dashboard
     */
    public function index()
    {
        // Define las variables que el layout necesite (ej. title, subtitle)
        $this->title = 'Inicio';
        $this->subtitle = 'Bienvenido al sistema';

        // El resto del código de la vista (dashboard/index.phtml) se carga automáticamente.
    }
}
