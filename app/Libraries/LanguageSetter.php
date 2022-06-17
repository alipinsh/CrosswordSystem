<?php

namespace App\Libraries;

class LanguageSetter {

    protected $session;

    protected $request;

    public function __construct() {
        $this->session = service('session');
        $this->request = service('request');
    }

    public function changeLanguage() {
        $this->request->setLocale($this->session->get('userData.language') ?: $this->request->getLocale());
    }
}
