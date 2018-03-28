<?php

function debugger($message = '')
{
    $fireBug = Fire\Bug::get();
    $trace = debug_backtrace();
    $debug = (object) [
        'trace' => $trace[0],
        'message' => $message
    ];
    $fireBug->addDebugger($debug);
}
