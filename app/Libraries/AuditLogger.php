<?php

namespace App\Libraries;

class AuditLogger {

    protected $request;

    public function __construct() {
        $this->request = service('request');
    }

    public function log() {
        log_message('debug', $this->request->fetchGlobal('server', 'REQUEST_URI'));
    }
}
