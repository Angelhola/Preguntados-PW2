<?php
namespace app\controller;
use Redirect;
use Request;
class EditorController{
    private  $model;
    private $render;

    public function __construct($model, $render){
        $this->model=$model;
        $this->render= $render;
    }

    public function panel() {
        if(!isset($_SESSION['rol'])||($_SESSION['rol']!="administrador")&&($_SESSION['rol']!="editor")){
            \Redirect::to('login/show');
return;
        }
        $data["usuario"] = $_SESSION['usuario'];
        $data["categorias"] = $this->model->getCategorias();
        $data["preguntas"] = $this->model->getTodasLasPreguntas();
        $this->render->renderizaArcade("panelEditor", $data);
    }
    public function guardar() {
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'editor' && $_SESSION['rol'] !== 'administrador')) {
            Redirect::to('login/show');
            return;
        }

        $texto = Request::post('texto');
        $opcion_1 = Request::post('opcion_1');
        $opcion_2 = Request::post('opcion_2');
        $opcion_3 = Request::post('opcion_3');
        $opcion_correcta = Request::post('opcion_correcta');
        $creador_id = $_SESSION['usuario_id'] ?? 1; // ID del usuario actual
        $categoria_id = (int) Request::post('categoria_id')?: null;
        $this->model->guardarPregunta($texto, $opcion_1, $opcion_2, $opcion_3, $opcion_correcta, $creador_id, $categoria_id);

        Redirect::to('editor/panel');
    }

    public function cambiarEstado() {
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'editor' && $_SESSION['rol'] !== 'administrador')) {
            Redirect::to('login/show');
            return;
        }

        $preguntaId = Request::post('pregunta_id');
        $nuevoEstado = Request::post('estado'); // 'activa', 'dada_de_baja'

        $this->model->cambiarEstadoPregunta($preguntaId, $nuevoEstado);

        Redirect::to('editor/panel');
    }

    public function ver() {
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'editor' && $_SESSION['rol'] !== 'administrador')) {
            Redirect::to('login/show');
            return;
        }

        $pregunta = $this->model->getPregunta(Request::get('id', 0));
        if ($pregunta === null) {
            Redirect::to('editor/panel');
            return;
        }

        $opciones = [];
        foreach (['opcion_1', 'opcion_2', 'opcion_3'] as $campo) {
            $opciones[] = ['texto' => $pregunta[$campo], 'correcta' => false];
        }
        $opciones[] = ['texto' => $pregunta['opcion_correcta'], 'correcta' => true];

        $data = $pregunta;
        $data["opciones"] = $opciones;
        $data["usuario"] = $_SESSION['usuario'];
        $this->render->renderizaArcade("verPregunta", $data);
    }

    private const MINIMO_REPORTES = 1;

    private function esModerador(): bool
    {
        return in_array($_SESSION['rol'] ?? '', ['administrador', 'editor']);
    }

    public function reportes()
    {
        if (!$this->esModerador()) { Redirect::to('login/show'); return; }
        $data["usuario"] = $_SESSION['usuario'];
        $data["preguntas"] = $this->model->getPreguntasReportadas(self::MINIMO_REPORTES);
        $data["total"] = count($data["preguntas"]);
        $data["hayPreguntas"] = $data["total"] > 0;
        $this->render->renderizaArcade("reportes", $data);
    }

    public function cantidadReportes()
    {
        header("Content-Type: application/json");
        if (!$this->esModerador()) { http_response_code(403); echo json_encode(["cantidad" => 0]); exit; }
        echo json_encode(["cantidad" => count($this->model->getPreguntasReportadas(self::MINIMO_REPORTES))]);
        exit;
    }

    public function resolverReporte()
    {
        header("Content-Type: application/json");
        if (!$this->esModerador()) { http_response_code(403); echo json_encode(["ok" => false]); exit; }

        $id = (int) Request::post('id', 0);
        $accion = Request::post('accion', '');
        if ($id <= 0 || !in_array($accion, ['bloquear', 'descartar'], true)) {
            http_response_code(400); echo json_encode(["ok" => false]); exit;
        }
        echo json_encode(["ok" => $this->model->resolverReportes($id, $accion === 'bloquear')]);
        exit;
    }

    public function editar()
    {
        if (!$this->esModerador()) { Redirect::to('login/show'); return; }

        $pregunta = $this->model->getPregunta(Request::get('id', 0));
        if ($pregunta === null) { Redirect::to('editor/panel'); return; }

        $categorias = $this->model->getCategorias();
        foreach ($categorias as &$cat) {
            $cat['seleccionada'] = ($cat['id'] == $pregunta['categoria_id']);
        }
        unset($cat);

        $data = $pregunta;
        $data["categorias"] = $categorias;
        $data["usuario"] = $_SESSION['usuario'];
        $data["error"] = Request::get('error');
        $this->render->renderizaArcade("editarPregunta", $data);
    }

    public function actualizar()
    {
        if (!$this->esModerador()) { Redirect::to('login/show'); return; }

        $id       = (int) Request::post('id', 0);
        $texto    = trim((string) Request::post('texto', ''));
        $op1      = trim((string) Request::post('opcion_1', ''));
        $op2      = trim((string) Request::post('opcion_2', ''));
        $op3      = trim((string) Request::post('opcion_3', ''));
        $correcta = trim((string) Request::post('opcion_correcta', ''));
        $categoriaId = (int) Request::post('categoria_id', 0) ?: null;

        $actual = $this->model->getPregunta($id);
        if ($actual === null) { Redirect::to('editor/panel'); return; }

        // Si la nueva correcta coincide con otra opción, se intercambian
        $otras = [$op1, $op2, $op3];
        foreach ($otras as $i => $opcion) {
            if ($correcta !== '' && mb_strtolower($opcion) === mb_strtolower($correcta)) {
                $otras[$i] = $actual['opcion_correcta'];
                break;
            }
        }
        [$op1, $op2, $op3] = $otras;

        $todas = [$op1, $op2, $op3, $correcta];
        $hayRepetidas = count(array_unique(array_map('mb_strtolower', $todas))) < 4;

        if ($texto === '' || in_array('', $todas, true) || $hayRepetidas) {
            Redirect::to('editor/editar?id=' . $id . '&error=1');
            return;
        }

        $this->model->actualizarPregunta($id, $texto, $op1, $op2, $op3, $correcta, $categoriaId);
        Redirect::to('editor/panel');
    }
}
