<?php
/*
 * Kontrollera klase, kura atbild par lietotāju uzstadītu valodu
 */

namespace App\Controllers;

class LanguageController extends BaseController {

    const LANGUAGES = ['en', 'ru', 'lv'];

    protected $saveModel;

    public function languagePage() {
        return view('language', ['locale' => $this->session->get('userData.language') ?: $this->request->getLocale()]);
    }

    public function changeLanguage() {
        $locale = $this->request->getPost('language');
        if (!in_array($locale, self::LANGUAGES)) {
            $locale = 'en';
        }
        $this->session->push('userData', ['language' => $locale]);

        return redirect()->to('/');
    }
}
