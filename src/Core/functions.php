<?php
declare(strict_types=1);

if (!function_exists('array_get')) {

    /**
     * @param array $data data
     * @param mixed $key key
     * @param mixed $default default
     * @return mixed
     */
    function array_get(array $data, $key, $default = null)
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;
    }

}
