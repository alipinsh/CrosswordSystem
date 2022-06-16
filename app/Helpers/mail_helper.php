<?php
/*
 * Palīgfunkcija atsūtīt e-pasta vēstuli, uzcelto no kāda šāblona.
 */

use Config\Services;

if (!function_exists('send_mail')) {
    function send_mail($to, $subject, $viewName, $viewVars) {
        $htmlMessage = view($viewName, $viewVars);

        $email = Services::email(null, true);
        $email->initialize([
            'mailType' => 'html'
        ]);

        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($htmlMessage);

        return $email->send();
    }
}
