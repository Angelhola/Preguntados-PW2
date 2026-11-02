<?php

namespace app;

use app\controller\EventoController;
use app\controller\LoginController;
use app\model\EventoModel;
use app\model\LoginModel;
use MustacheRender;
use MyDatabase;
use app\controller\RankingController;
use app\model\RankingModel;
use Router;
use app\controller\JuegoController;
use app\model\JuegoModel;
use app\controller\AdminController;
use app\model\AdminModel;
use app\controller\EditorController;
use app\model\EditorModel;

require_once("helper/Autoloader.php");

class Configuration
{

    public function __construct()
    {
    }

    public function getEventoController()
    {
        return new EventoController(
            $this->getEventoModel(),
            $this->gerRender()
        );
    }

    public function getLoginController()
    {
        return new LoginController(
            $this->getLoginModel(),
            $this->gerRender()
        );
    }

    public function getRankingController() {
        return new RankingController(
            $this->getRankingModel(),
            $this->gerRender()
        );
    }

    private function getDatabase()
    {
        $config = parse_ini_file("config/config.ini");

        return new MyDatabase($config["db_host"],
            $config["db_user"],
            $config["db_pass"],
            $config["db_name"]
        );
    }

    private function gerRender()
    {
        return new MustacheRender();
    }

    private function getEventoModel()
    {
        return new EventoModel(
            $this->getDatabase()
        );
    }

    private function getLoginModel()
    {
        return new LoginModel(
            $this->getDatabase()
        );
    }

    private function getRankingModel() {
        return new RankingModel(
            $this->getDatabase()
        );
    }

    public function getRouter()
    {
        return new Router($this, "login", "inicio");
    }

    public function getJuegoController()
    {
        return new JuegoController($this->getJuegoModel(), $this->gerRender());
    }
    public function getJuegoModel()
    {
        return new JuegoModel(
            $this->getDatabase()
        );
    }
    public function getAdminController() {
        return new AdminController(
            $this->getAdminModel(),
            $this->gerRender()
        );
    }

    private function getAdminModel() {
        return new AdminModel(
            $this->getDatabase()
        );
    }

    public function getEditorController() {
        return new EditorController(
            $this->getEditorModel(),
            $this->gerRender()
        );
    }

    private function getEditorModel() {
        return new EditorModel(
            $this->getDatabase()
        );
    }
}
