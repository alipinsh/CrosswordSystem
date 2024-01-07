<?php
/*
 * Palīgfunkcija atsūtīt e-pasta vēstuli, uzcelto no kāda šāblona.
 */

if (!function_exists('send_mail')) {
    function send_mail($to, $subject, $viewName, $viewVars) {
        $locale = service('request')->getLocale();
        $htmlMessage = view('email/' . $locale . '/' . $viewName, $viewVars);

        $email = service('mail');
        $email->initialize([
            'mailType' => 'html'
        ]);

        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($htmlMessage);

        return $email->send();
    }
}
