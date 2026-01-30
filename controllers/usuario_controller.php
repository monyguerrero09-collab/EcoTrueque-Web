<?php
/**
 * Controlador: Usuario
 * Gestiona el registro, edición y administración de usuarios.
 */
class UsuarioController extends AppController
{
    /**
     * Filtro de seguridad para el área de administración.
     * Protege todas las acciones excepto 'registrar' y 'forzar_password'.
     */
    protected function before_filter()
    {
        // Acciones que son públicas o de test temporal y deben saltarse el filtro.
        $public_actions = ['registrar', 'forzar_password'];
        $current_action = Router::get('action'); // Obtiene el nombre de la acción actual

        if (in_array($current_action, $public_actions)) {
            return; // Permite el acceso directo a registrar() y forzar_password()
        }

        // --- Comprobaciones de Seguridad para el área Admin (CRUD) ---

        // 1. Verificar autenticación
        if (!Auth::check()) {
            Flash::error('Acceso denegado. Debes iniciar sesión.');
            Redirect::to('session');
            return false;
        }

        // 2. Verificar rol de administrador (asumiendo que el rol 1 es el administrador)
        // La sesión lee el rol de la clave 'rol', según tu auth.php.
        if (Auth::user()['rol'] != 1) {
            Flash::error('⚠️ Acceso denegado. Solo administradores.');
            Redirect::to('dashboard');
            return false;
        }
    }

    // =======================================================================
    // ACCIONES PÚBLICAS Y DE TEST
    // =======================================================================

    /**
     * Acción: Registro de nuevo usuario (PÚBLICA)
     */
    public function registrar()
    {
        // Si ya está logueado, redirige al dashboard.
        if (Auth::check()) {
            Redirect::to('dashboard');
        }

        $this->title = 'Registro';
        $this->subtitle = 'Crea tu cuenta de Trueque Ecológico';

        if (Input::hasPost('usuario')) {
            $data = Input::post('usuario');
            $password_confirm = $data['password_confirm'];

            // 1. Quitar el campo de confirmación
            unset($data['password_confirm']);

            $usuario = new Usuario($data);

            // 2. Validación de Contraseñas
            if (strlen($usuario->password) < 6) {
                Flash::error('❌ La contraseña debe tener al menos 6 caracteres.');
                return;
            }
            if ($usuario->password !== $password_confirm) {
                Flash::error('❌ Las contraseñas no coinciden.');
                return;
            }

            // 4. Asignar Rol por defecto (2 para usuarios normales)
            $usuario->rol = 2; // Usamos 'rol' ya que así se llama en tu base de datos y modelo

            // 5. Intentar Guardar (el modelo se encarga del Bcrypt)
            if ($usuario->save()) {
                Flash::valid('✅ ¡Registro exitoso! Ya puedes iniciar sesión.');
                Redirect::to('session');
            } else {
                Flash::error('❌ Error al registrar usuario. Revisa los datos y el email.');
            }
        }
    }

    /**
     * Acción temporal para forzar la actualización de la contraseña a un hash compatible.
     * URL: /usuario/forzar_password/ID/NUEVACONTRASENA
     */
    public function forzar_password($id, $new_password)
    {
        // Deshabilitar vistas y layouts para solo mostrar el resultado
        View::select(null, null);

        // 1. Buscar al usuario
        $usuario = (new Usuario())->find((int)$id);

        if (!$usuario) {
            die("Error: Usuario ID $id no encontrado.");
        }

        // 2. Sobrescribir la contraseña con el texto plano
        $usuario->password = $new_password;

        // 3. Guardar
        if ($usuario->save()) {
            die("✅ Contraseña del usuario ID $id ({$usuario->email}) ha sido actualizada a un hash compatible con PHP 8.2 (Bcrypt). Contraseña: $new_password");
        } else {
            // Mostrar errores si el ORM falla
            $messages = $usuario->get_messages();
            $error_str = implode('; ', array_map(fn($m) => $m->get_message(), $messages));
            die("❌ Error al guardar la nueva contraseña: $error_str");
        }
    }

    // =======================================================================
    // CRUD: READ (Mostrar Listado) - SÓLO ADMIN
    // =======================================================================
    /**
     * Acción principal: Muestra el listado de todos los usuarios.
     * URL: /usuario/index
     */
    public function index()
    {
        $this->title = 'Administración de Usuarios';
        $this->subtitle = 'Listado y gestión';

        // Carga todos los usuarios.
        $this->usuarios = (new Usuario())->find();
    }

    // =======================================================================
    // CRUD: CREATE (Crear Nuevo Usuario) - SÓLO ADMIN
    // =======================================================================
    /**
     * Muestra el formulario de creación y procesa los datos.
     * URL: /usuario/crear
     */
    public function crear()
    {
        $this->title = 'Crear Nuevo Usuario';
        $this->subtitle = 'Ingrese los datos';

        // Cargar roles para el select (si tienes tabla roles)
        // Asume que tienes un modelo llamado Roles
        $this->roles = (new Rol())->find();

        if (Input::hasPost('usuario')) {
            $data = Input::post('usuario');

            // Validar que la contraseña no venga vacía
            if (empty($data['password'])) {
                Flash::error('❌ La contraseña es requerida.');
                return;
            }

            $usuario = new Usuario($data);

            // Asignar el campo 'rol'
            $usuario->rol = $data['rol'];

            if ($usuario->save()) {
                Flash::valid('✅ Usuario creado exitosamente.');
                Redirect::to('usuario');
            } else {
                Flash::error('❌ Error al crear usuario. Verifique los datos.');
            }
        }
    }

    // =======================================================================
    // CRUD: UPDATE (Editar Usuario Existente) - SÓLO ADMIN
    // =======================================================================
    /**
     * Muestra el formulario de edición y procesa la actualización.
     * URL: /usuario/editar/ID
     */
    public function editar($id)
    {
        $this->usuario = (new Usuario())->find($id);

        if (!$this->usuario) {
            Flash::error('❌ Usuario no encontrado.');
            Redirect::to('usuario');
            return;
        }

        $this->title = "Editar Usuario: {$this->usuario->email}";
        $this->subtitle = 'Modificar datos de la cuenta';
        $this->roles = (new Roles())->find();

        if (Input::hasPost('usuario')) {
            $data = Input::post('usuario');

            // CRÍTICO: Si la contraseña viene vacía, no la actualizamos para no borrar el hash.
            if (empty($data['password'])) {
                unset($data['password']);
            }

            if ($this->usuario->update($data)) {
                Flash::valid('✅ Usuario actualizado exitosamente.');
                Redirect::to('usuario');
            } else {
                Flash::error('❌ Error al actualizar usuario.');
            }
        }
    }

    // =======================================================================
    // CRUD: DELETE (Eliminar Usuario) - SÓLO ADMIN
    // =======================================================================
    /**
     * Elimina un usuario específico.
     * URL: /usuario/eliminar/ID
     */
    public function eliminar($id)
    {
        $id = (int) $id;

        // No permitir que un administrador se elimine a sí mismo
        if (Auth::id() == $id) {
            Flash::error('❌ No puedes eliminar tu propia cuenta.');
            Redirect::to('usuario');
            return;
        }

        $usuario = (new Usuario())->find($id);

        if ($usuario && $usuario->delete()) {
            Flash::valid('✅ Usuario eliminado exitosamente.');
        } else {
            Flash::error('❌ Error al eliminar usuario.');
        }

        Redirect::to('usuario');
    }

    // =======================================================================
    // ACCIONES DE PERFIL DE USUARIO Y UTILIDAD
    // =======================================================================

    /**
     * Acción: Cambiar contraseña (Requiere seguridad interna)
     */
    public function cambiar_password($id)
    {
        // Esta acción mantiene su propia seguridad interna.
        if (!Auth::check() || (!Auth::user()['rol'] == 1 && Auth::id() != $id)) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $this->usuario = (new Usuario())->find($id);

        if (Input::hasPost('password')) {
            $data = Input::post('password');

            // Verificar contraseña actual si no es admin (usa Bcrypt)
            if (Auth::user()['rol'] != 1) {
                // CRÍTICO: Usar password_verify para BCrypt
                if (!password_verify($data['current'], $this->usuario->password)) {
                    Flash::error('❌ Contraseña actual incorrecta');
                    return;
                }
            }

            if ($data['new'] !== $data['confirm']) {
                Flash::error('❌ Las contraseñas no coinciden');
                return;
            }

            // Asignar nueva contraseña para que el before_save la hashee
            $this->usuario->password = $data['new'];

            if ($this->usuario->save()) {
                Flash::valid('✅ Contraseña cambiada exitosamente');
                Redirect::to('usuario');
            } else {
                Flash::error('❌ Error al cambiar contraseña');
            }
        }
    }

    /**
     * Acción: Cambiar estado (Activar/Desactivar) - SÓLO ADMIN
     * URL: /usuario/toggle_estado/ID
     */
    public function toggle_estado($id)
    {
        $usuario = (new Usuario())->find((int) $id);

        if ($usuario) {
            // Asume que la columna se llama 'activo'
            $usuario->activo = $usuario->activo ? 0 : 1;
            $estado = $usuario->activo ? 'activado' : 'desactivado';

            if ($usuario->save()) {
                Flash::info("✅ Usuario $estado.");
            } else {
                Flash::error('❌ Error al cambiar estado.');
            }
        }
        Redirect::to('usuario');
    }
}