<?php

class ErrorHandler
{
    /**
     * @param string $message
     * @param string $message_details
     */
    static function error_log_and_exception($message, $message_details = '') {
        self::error_log($message, $message_details);
        ?><div class="alert alert-danger">Technical error</div><?php
    }

    /**
     * @param string $message
     * @param string $message_details
     */
    static function error_log($message, $message_details = '') {
        $simple_backtrace = array_map(function($trace_step) {
            return $trace_step['file'].':'.$trace_step['line'];
        }, debug_backtrace());

        error_log("$message - $message_details in ".implode(" - ", $simple_backtrace), 0, '/var/log/apache2/error.log');
    }
}