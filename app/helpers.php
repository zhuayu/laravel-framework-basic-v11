<?php

if (! function_exists('fileLogger')) {
    /**
     * 日志文件.
     *
     * @param        $path
     * @param string $name
     *
     * @return \Monolog\Logger
     */
    function fileLogger($path, $name = null)
    {
        $path = 'logs/' . trim($path) . '/';
        $name = empty($name) ? date('Y-m-d') : $name . '-' . date('Y-m-d');
        $logger = new Monolog\Logger(config('app.env'));
        $logger->pushProcessor(function($record) {
            return $record;
        });
        $logger->pushHandler(new Monolog\Handler\StreamHandler(storage_path($path . $name . '.log')));

        return $logger;
    }
}
