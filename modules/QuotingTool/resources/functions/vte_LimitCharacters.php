<?php

if (!function_exists('limitCharacters')) {
    /**
     * @param $value
     * @param int $decimal
     * @return float
     */
    function limitCharacters($value, $length = 0)
    {
        return substr(html_entity_decode($value,ENT_QUOTES), 0, $length);
    }
}