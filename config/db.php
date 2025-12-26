<?php
// Simple PDO configuration. Update credentials to match your local MySQL.

const DB_HOST = 'localhost';
const DB_NAME = 'ecommerce_db';
const DB_USER = 'root';
const DB_PASS = '';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    // In dev, show a friendly message; in prod, log this instead.
    $pdo = null;
    error_log('DB connection failed: ' . $e->getMessage());
}