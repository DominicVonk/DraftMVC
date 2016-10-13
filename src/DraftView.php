<?php
namespace DraftMVC;
if (!defined('DRAFT_VIEWS')) {
    define('DRAFT_VIEWS', __DIR__ . '/views');
}
class DraftView {
    private $file;
    private $html;
    public function __construct($file) {
        $this->file = $file;
    }
    public function escape($string) {
        return htmlentities($string);
    }
    public function show() {
        ob_start();
        require(DRAFT_VIEWS . '/' . $this->file . '.php');
        $this->html = ob_get_contents();
        ob_end_clean();
        return $this->html;
    }
}