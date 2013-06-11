<?php

function set_flash($text) {
    $_SESSION['sprava'] = $text;
}

function get_flash_and_clear() {
    if (empty($_SESSION['sprava'])) {
        return null;
    }
    $flash = $_SESSION['sprava'];
    $_SESSION['sprava'] = '';
    return $flash;
}
