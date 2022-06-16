<?php
/*
 * Kontrollera klase, kura atbild par lietotāju uzstadītu valodu
 */

namespace App\Controllers;

class LanguageController extends BaseController {

    protected $saveModel;

    public function languagePage() {
        return view('language', ['locale' => service('request')->getLocale()]);
    }

    public function changeLanguage() {
        $locale = $this->request->getPost('language');
        service('request')->setLocale($locale);

        return redirect()->to('/');
    }
}
