<?php
spl_autoload_register(function ($name) {
    $pathParts = explode("\\", $name);
    $path = implode("\\", array_slice($pathParts, 1));
    include "{$path}.php";
});