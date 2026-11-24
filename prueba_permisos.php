<?php
$carpeta = __DIR__ . '/bootstrap/cache';
var_dump(is_dir($carpeta));
var_dump(is_writable($carpeta));
$archivo = $carpeta . '/prueba.txt';
var_dump(file_put_contents($archivo, 'PRUEBA'));
if (file_exists($archivo)) {
    unlink($archivo);
}