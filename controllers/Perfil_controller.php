<?php
/**
 * Controlador de Perfil
 * Maneja la visualización y edición del perfil del usuario.
 */
class PerfilController extends AppController
{
    /**
     * Método ejecutado antes de cada acción.
     * Se usa para obligar a iniciar sesión antes de ver el perfil.
     */
    protected function before_filter()
    {
        // Si el usuario NO está logueado, redirigir a la página de inicio de sesión.
        if (!Auth::check()) {
            Flash::info('Debes iniciar sesión para ver tu perfil.');
            Redirect::to('session'); // Redirigir al controlador 'Session'
            return false; // Detener la ejecución del controlador
        }
    }

    public function index()
    {
        // Definición de variables para el layout (soluciona warnings)
        $this->title = '👤 Mi Perfil';
        $this->subtitle = 'Información Personal';

        // Carga el modelo del usuario logueado
        $this->usuario = (new Usuario())->find(Auth::user()["id"]);

        // 🛑 MODIFICACIÓN: Eliminar el hash de la contraseña para evitar que se precargue en la vista.
        // Esto previene los puntos (******) en el formulario de edición.
        unset($this->usuario->password);
    }

    public function editar()
    {
        // Definición de variables para el layout (soluciona warnings)
        $this->title = '✏️ Editar Perfil';
        $this->subtitle = 'Modificar Datos';

        // Carga el modelo del usuario logueado
        $this->usuario = (new Usuario())->find(Auth::user()["id"]);

        // 🛑 MODIFICACIÓN: Eliminar el hash de la contraseña para evitar que se precargue en la vista.
        // Se hace aquí también para la recarga del formulario en caso de error de validación.
        unset($this->usuario->password);

        if (Input::hasPost('usuario')) {
            $data = Input::post('usuario');

            // 1. Manejo del email: Ya está bien.
            if ($data['email'] !== $this->usuario->email) {
                $existe = (new Usuario())->find_first("email = '{$data['email']}'");
                if ($existe) {
                    Flash::error('❌ El email ya está en uso');
                    return;
                }
            }

            // 2. Manejo de la contraseña
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    Flash::error('❌ La contraseña debe tener al menos 6 caracteres');
                    return;
                }
                if ($data['password'] !== $data['password_confirm']) {
                    Flash::error('❌ Las contraseñas no coinciden');
                    return;
                }
                // Si la contraseña es válida, el before_save() la hasheará
            } else {
                // Si el campo está vacío, la clave 'password' se elimina de $data
                // para que el ORM no la actualice y before_save() no se ejecute.
                unset($data['password']);
            }

            // Limpiar la confirmación de contraseña antes de pasar a la DB
            unset($data['password_confirm']);

            // 3. Actualización
            if ($this->usuario->update($data)) {

                Flash::info('✅ Perfil actualizado correctamente');

                Redirect::to('perfil');
            } else {
                Flash::error('❌ Error al actualizar el perfil');
            }
        }
    }
}