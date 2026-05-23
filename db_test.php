<?php
require_once __DIR__ . '/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "DB connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}

echo "DB connected successfully to database '" . DB_NAME . "'.";

$mysqli->close();
