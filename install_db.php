<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

// Connect without selecting a database
$mysqli = connect_db(false);
$dbName = DB_NAME;

if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    echo "Failed to create database: " . $mysqli->error;
    exit(1);
}

// Select the database
if (!$mysqli->select_db($dbName)) {
    echo "Failed to select database: " . $mysqli->error;
    exit(1);
}

$sqlFile = __DIR__ . '/database.sql';
if (!is_readable($sqlFile)) {
    echo "SQL file not found at {$sqlFile}\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Unable to read SQL file.\n";
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    // flush multi queries
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Database '{$dbName}' created/imported successfully.\n";
} else {
    echo "Import failed: " . $mysqli->error . "\n";
}

$mysqli->close();
