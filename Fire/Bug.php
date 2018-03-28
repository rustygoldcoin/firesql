<?php
namespace Fire;

class Bug
{

    static private $_instance;

    private $_debuggers;

    private $_panels;

    private function __construct()
    {
        $this->_debuggers = [];
        $this->_panels = [];
        $this->addPanel(__DIR__ . '/../view/template/debug/panel/debuggers.phtml');
    }

    static function get()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function addDebugger($debugger)
    {
        $this->_debuggers[] = $debugger;
    }

    public function addPanel($panel)
    {
        $this->_panels[] = $panel;
    }

    public function render()
    {
        $this->panel(__DIR__ . '/../view/template/debug/debug-panel.phtml');
    }

    public function panel($panel)
    {
        if (!file_exists($panel)) {

        }

        ob_start();
        include $panel;
        ob_end_flush();
    }

}
