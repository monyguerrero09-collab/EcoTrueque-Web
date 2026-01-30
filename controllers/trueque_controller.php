<?php

class TruequeController extends AppController
{
    /********************************
     * ✅ PUBLICAR TRUEQUE
     ********************************/
    public function publicar()
    {
        View::template('admintle');

        $this->title    = 'Publicar Trueque';
        $this->subtitle = 'Comparte lo que deseas intercambiar';

        if (!Input::hasPost('trueque')) return;

        $datos      = Input::post('trueque');
        $usuario_id = (int) Session::get('id');

        if (!$usuario_id) {
            Flash::error('Debes iniciar sesión.');
            return Redirect::to('session/index');
        }

        if (empty($datos['latitud']) || empty($datos['longitud'])) {
            Flash::error('Selecciona la ubicación en el mapa.');
            return;
        }

        // 📸 Imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $mime   = mime_content_type($_FILES['imagen']['tmp_name']);
            $bin    = file_get_contents($_FILES['imagen']['tmp_name']);
            $base64 = base64_encode($bin);

            $datos['imagen_mime']   = $mime;
            $datos['imagen_base64'] = $base64;
            $datos['imagen']        = $_FILES['imagen']['name'];
        } else {
            Flash::error('Debes subir una imagen.');
            return;
        }

        // Datos finales
        $datos['usuario_id'] = $usuario_id;
        $datos['latitud']    = floatval($datos['latitud']);
        $datos['longitud']   = floatval($datos['longitud']);
        $datos['direccion']  = $datos['direccion'] ?? null;
        $datos['fecha']      = date('Y-m-d');
        $datos['estado']     = 'Activo';

        $trueque = new Trueque();

        if ($trueque->save($datos)) {
            Flash::valid('✅ Trueque publicado');
            return Redirect::to('trueque/index');
        }

        Flash::error('❌ Error al guardar');
    }

    /********************************
     * ✅ LISTADO DE TRUEQUES
     ********************************/
    public function index()
    {
        View::template('admintle');

        $this->title    = 'Lista de Trueques';
        $this->subtitle = 'Explora intercambios';

        $this->trueques = (new Trueque())->find("order: id DESC");
    }

    /********************************
     * ✅ VER DETALLE
     ********************************/
    public function ver($id = null)
    {
        View::template('admintle');

        if (!$id) {
            Flash::error('ID inválido');
            return Redirect::to('trueque/index');
        }

        $this->trueque = (new Trueque())->find_first((int)$id);

        if (!$this->trueque) {
            Flash::error('Trueque no encontrado');
            return Redirect::to('trueque/index');
        }

        $this->title    = 'Detalles del Trueque';
        $this->subtitle = 'Información del intercambio';
    }

    /********************************
     * ✅ ELIMINAR TRUEQUE
     ********************************/
    public function eliminar($id = null)
    {
        View::template('admintle');

        $id = (int)$id;

        if (!$id) {
            Flash::error('ID inválido');
            return Redirect::to('trueque/index');
        }

        $trueque = (new Trueque())->find_first($id);

        if (!$trueque) {
            Flash::error('No existe el trueque');
            return Redirect::to('trueque/index');
        }

        if ($trueque->usuario_id != Session::get('id')) {
            Flash::error('No tienes permisos');
            return Redirect::to('trueque/index');
        }

        $trueque->delete();
        Flash::valid('✅ Trueque eliminado');

        return Redirect::to('trueque/index');
    }

    /********************************
     * ✅ BANDEJA DE MENSAJES
     ********************************/
    public function mensajes()
    {
        View::template('admintle');

        $usuario_id = (int) Session::get('id');
        if (!$usuario_id) return Redirect::to('session/index');

        $this->title    = 'Mis Mensajes';
        $this->subtitle = 'Bandeja de entrada';

        $m = new Mensaje();
        $this->conversaciones = $m->get_conversaciones_summary($usuario_id);
        $this->total_no_leidos = array_sum(array_column($this->conversaciones, 'no_leidos'));

        $this->recibidos = $m->find(
            "conditions: destinatario_id = {$usuario_id}",
            "order: fecha_envio DESC"
        );

        $this->enviados = $m->find(
            "conditions: remitente_id = {$usuario_id}",
            "order: fecha_envio DESC"
        );
    }

    /********************************
     * ✅ ENVIAR MENSAJE
     ********************************/
    public function enviar_mensaje()
    {
        if (!Input::hasPost('mensaje')) {
            return Redirect::to('trueque/index');
        }

        $datos        = Input::post('mensaje');
        $remitente_id = (int) Session::get('id');

        if (!$remitente_id) {
            return Redirect::to('session/index');
        }

        if (empty($datos['mensaje'])) {
            Flash::error("Mensaje vacío.");
            return Redirect::to("trueque/conversacion/" . (int)$datos['trueque_id']);
        }

        // ✅ Destinatario automático
        if (empty($datos['destinatario_id'])) {
            $trueque = (new Trueque())->find_first((int)$datos['trueque_id']);
            if ($trueque) {
                $datos['destinatario_id'] = $trueque->usuario_id;
            }
        }

        if ($datos['destinatario_id'] == $remitente_id) {
            Flash::error("No puedes enviarte mensajes a ti mismo.");
            return Redirect::to("trueque/conversacion/" . (int)$datos['trueque_id']);
        }

        $datos['remitente_id'] = $remitente_id;
        $datos['leido']        = 0;
        $datos['fecha_envio']  = date('Y-m-d H:i:s');

        $msg = new Mensaje();

        if (!$msg->save($datos)) {
            Flash::error("❌ Error al enviar");
        }

        return Redirect::to("trueque/conversacion/" . $datos['trueque_id']);
    }

    /********************************
     * ✅ CONVERSACIÓN (CHAT)
     ********************************/
    public function conversacion($trueque_id = null)
    {
        View::template('admintle');

        $usuario_id = (int) Session::get('id');
        $trueque_id = (int) $trueque_id;

        if (!$usuario_id) return Redirect::to('session/index');
        if (!$trueque_id) return Redirect::to('trueque/index');

        $trueque = (new Trueque())->find_first($trueque_id);
        if (!$trueque) return Redirect::to('trueque/index');

        $this->trueque        = $trueque;
        $this->usuario_actual = $usuario_id;

        // ✅ Definir "otro usuario"
        if ($usuario_id == $trueque->usuario_id) {
            $last_msg = (new Mensaje())->find_first(
                "conditions: trueque_id = $trueque_id AND destinatario_id = $usuario_id",
                "order: id DESC"
            );
            $otro_id = $last_msg ? $last_msg->remitente_id : 0;
        } else {
            $otro_id = $trueque->usuario_id;
        }

        $this->otro_usuario = (new Usuario())->find_first($otro_id);

        if (!$this->otro_usuario) {
            $this->otro_usuario = new stdClass();
            $this->otro_usuario->id     = 0;
            $this->otro_usuario->nombre = "Desconocido";
        }

        $this->title    = "Chat del trueque #{$trueque_id}";
        $this->subtitle = $trueque->ofrezco ?? 'Trueque';

        $m = new Mensaje();

        $this->ultimos = $m->find(
            "conditions: trueque_id = {$trueque_id}
             AND (remitente_id = {$usuario_id} OR destinatario_id = {$usuario_id})",
            "order: fecha_envio DESC",
            "limit: 40"
        );
    }

    public function notificaciones() {
        $this->notificaciones = (new Notificaciones())->listar($this->usuario_id);
    }

    /********************************
     * ✅ HISTORIAL
     ********************************/
    public function historial()
    {
        View::template('admintle');

        $this->title    = 'Historial de Trueques';
        $this->subtitle = 'Registros';

        $this->trueques = (new Trueque())->find("order: id DESC");
    }

    /********************************
     * ✅ EDITAR TRUEQUE
     ********************************/
    public function editar($id = null)
    {
        View::template('admintle');

        $this->title    = 'Editar Trueque';
        $this->subtitle = 'Modifica la información';

        $id = (int)$id;
        $trueque = new Trueque();

        if (!$trueque->find_first($id)) {
            Flash::error('Trueque no encontrado');
            return Redirect::to('trueque/historial');
        }

        if (Input::hasPost('trueque')) {
            $data = Input::post('trueque');

            if ($trueque->usuario_id != Session::get('id')) {
                Flash::error('Sin permisos');
                return Redirect::to('trueque/historial');
            }

            if ($trueque->update($data)) {
                Flash::valid('✅ Actualizado');
                return Redirect::to("trueque/ver/{$id}");
            }

            Flash::error('❌ Error al actualizar');
        }

        $this->trueque = $trueque;
    }
}


