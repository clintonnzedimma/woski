<?php

namespace Woski\Support;

class Logger {
    protected $logFile;

    public function __construct($path = null) {
        $this->logFile = $path ?? ROOT . "storage/logs/woski.log";
    }

    protected function write($level, $message, $context = []) {
        $timestamp = date("Y-m-d H:i:s");
        $formatted = "[$timestamp] [$level] $message";

        if (!empty($context)) {
            $formatted .= ' ' . json_encode($context);
        }

        $formatted .= PHP_EOL;
        error_log($formatted, 3, $this->logFile);
    }

    public function info($message, $context = []) {
        $this->write("INFO", $message, $context);
    }

    public function warning($message, $context = []) {
        $this->write("WARNING", $message, $context);
    }

    public function error($message, $context = []) {
        $this->write("ERROR", $message, $context);
    }

    public function debug($message, $context = []) {
        $this->write("DEBUG", $message, $context);
    }
}
