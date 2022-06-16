<?php
/*
 * Palīgfunkcija atsūtīt tekstam
 */

if (!function_exists('clean_text')) {
    function clean_text($text) {
        $text = trim($text);
        $text = preg_replace('/  +/', ' ', $text);
        $text = preg_replace('/(?:\r?\n|\r){2,}/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = esc($text);

        return $text;
    }
}
