<?php
class SessionController extends AppController
{
    public function index()
    {
        if (Session::get('id')) {
            return Redirect::to('dashboard');
        }

        $this->title = 'Inicio de Sesión';
        $this->subtitle = 'Accede a tu cuenta';
    }

    public function login()
    {
        if (!Input::hasPost('login')) {
            return Redirect::to('session');
        }

        $login = Input::post('login');
        $email = trim($login['email'] ?? '');
        $password = trim($login['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::error("❌ Completa todos los campos.");
            return Redirect::to('session');
        }

        $usuario = (new Usuario())->find_first("email = '$email'");

        if (!$usuario) {
            Flash::error("❌ Usuario no encontrado");
            return Redirect::to('session');
        }

        // Si usas password_hash
        if (!password_verify($password, $usuario->password)) {
            Flash::error("❌ Contraseña incorrecta");
            return Redirect::to('session');
        }

        // Crear sesión manual
        Session::set('id', $usuario->id);
        Session::set('nombre', $usuario->nombre);
        Session::set('email', $usuario->email);

        Flash::valid("✅ Bienvenido {$usuario->nombre}");
        return Redirect::to('dashboard');
    }

    public function logout()
    {
        Session::delete('id');
        Session::delete('nombre');
        Session::delete('email');

        Flash::info("👋 Sesión cerrada");
        return Redirect::to('session');
    }
}
