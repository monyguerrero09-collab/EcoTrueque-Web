<?php

class RolesController extends AppController
{
    public function index()
    {
        // VALIDACIÓN: El rol del usuario (ID) debe ser 1
        if (!Auth::check() || Auth::user()['rol'] != 1) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $this->title = 'Administración';
        $this->subtitle = 'Gestión de Roles';

        $this->roles = (new Rol())->find();
    }

    public function crear()
    {
        // VALIDACIÓN: El rol del usuario (ID) debe ser 1
        if (!Auth::check() || Auth::user()['rol'] != 1) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $this->title = 'Crear Nuevo Rol';
        $this->subtitle = 'Formulario para añadir un nuevo rol';

        if (Input::hasPost('rol')) {
            $data = Input::post('rol');
            $rol = new Rol();
            $rol->nombre = $data['nombre'];
            $rol->descripcion = $data['descripcion'];
            $rol->icono = $data['icono'];
            $rol->activo = 1;

            if ($rol->save()) {
                Flash::valid('✅ Rol creado exitosamente');
                Redirect::to('roles');
            } else {
                Flash::error('❌ Error al crear rol');
            }
        }
    }

    public function editar($id)
    {
        // VALIDACIÓN: El rol del usuario (ID) debe ser 1
        if (!Auth::check() || Auth::user()['rol'] != 1) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $this->rol = (new Rol())->find($id);

        if (!$this->rol) {
            Flash::error('❌ Rol no encontrado');
            Redirect::to('roles');
            // Es importante usar 'return' aquí también si hay una redirección
            return;
        }

        // === DEFINICIÓN DE TÍTULOS PARA LA VISTA ===
        $this->title = 'Administración de Roles';
        $this->subtitle = 'Editar Rol: ' . $this->rol->nombre;
        // ==========================================

        if (Input::hasPost('rol')) {
            $data = Input::post('rol');
            $this->rol->nombre = $data['nombre'];
            $this->rol->descripcion = $data['descripcion'];
            $this->rol->icono = $data['icono'];

            if ($this->rol->save()) {
                Flash::valid('✅ Rol actualizado exitosamente');
                Redirect::to('roles');
            } else {
                Flash::error('❌ Error al actualizar rol');
            }
        }
    }

    public function toggle_estado($id)
    {
        // VALIDACIÓN: El rol del usuario (ID) debe ser 1
        if (!Auth::check() || Auth::user()['rol'] != 1) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $rol = (new Rol())->find($id);

        if ($rol) {
            // Lógica interna: No permitir desactivar el rol con ID 1
            if ($rol->id == 1 && $rol->activo) {
                Flash::error('❌ No se puede desactivar el rol de administrador');
                Redirect::to('roles');
                return;
            }

            $rol->activo = $rol->activo ? 0 : 1;
            $estado = $rol->activo ? 'activado' : 'desactivado';

            if ($rol->save()) {
                Flash::valid("✅ Rol $estado exitosamente");
            } else {
                Flash::error('❌ Error al cambiar estado');
            }
        }

        Redirect::to('roles');
    }

    public function eliminar($id)
    {
        // VALIDACIÓN: El rol del usuario (ID) debe ser 1
        if (!Auth::check() || Auth::user()['rol'] != 1) {
            Flash::warning('⚠️ Acceso denegado');
            Redirect::to('dashboard');
            return;
        }

        $rol = (new Rol())->find($id);

        if ($rol) {
            // Lógica interna: No permitir eliminar el rol con ID 1
            if ($rol->id == 1) {
                Flash::error('❌ No se puede eliminar el rol de administrador');
                Redirect::to('roles');
                return;
            }

            // ... (resto de la lógica de eliminación)
            $usuarios = (new Usuario())->count("rol = '{$rol->nombre}'");
            if ($usuarios > 0) {
                Flash::error("❌ No se puede eliminar el rol. Hay $usuarios usuarios asignados");
                Redirect::to('roles');
                return;
            }

            if ($rol->delete()) {
                Flash::valid('✅ Rol eliminado exitosamente');
            } else {
                Flash::error('❌ Error al eliminar rol');
            }
        }

        Redirect::to('roles');
    }
}