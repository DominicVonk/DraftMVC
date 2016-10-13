<?php
namespace DraftMVC;
class DraftController {
    protected $view = null;
    protected $layout = null;
    public function escape($string) {
        return htmlentities($string);
    }
    public function __construct() {
    }
    public function __destruct() {
        if ($this->hasView()) {
            $view = $this->view->show();
        }
        if ($this->hasLayout()) {
            $this->layout->contents = $view;
            echo $this->layout->show();
        } else if ($this->hasView()) {
            echo $view;
        }
    }
    public function redirect($path) {
        header('Location: ' . $path);
    }
    public function setView($view) {
        $this->view = $view;
    }
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    public function unsetView() {
        $this->view = null;
    }
    public function unsetLayout() {
        $this->layout = null;
    }
    public function hasView() {
        return $this->view !== null;
    }
    public function hasLayout() {
        return $this->layout !== null;
    }
}