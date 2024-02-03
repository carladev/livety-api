<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();