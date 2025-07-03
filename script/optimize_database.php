<?php
// Database Optimization Script
// Run this script to apply all database optimizations (indexes, etc.)

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Config\Database;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Get the database connection
$pdo = Database::getInstance()->getConnection();

echo "==================================\n";
echo "Database Optimization Script\n";
echo "==================================\n\n";

echo "Loading optimization SQL file...\n";
$optimizationFile = __DIR__ . '/../config/db_optimizations.sql';

if (!file_exists($optimizationFile)) {
    die("Error: Optimization file not found at {$optimizationFile}\n");
}

$sql = file_get_contents($optimizationFile);

if (empty($sql)) {
    die("Error: Optimization file is empty\n");
}

echo "Parsing SQL statements...\n";
// Split the SQL into separate statements
$statements = preg_split('/;\s*$/m', $sql);
$statements = array_filter($statements, 'trim'); // Remove empty statements

echo "Found " . count($statements) . " SQL statements to execute\n\n";

$success = 0;
$errors = 0;

// Begin transaction
echo "Starting transaction...\n";
$pdo->beginTransaction();

try {
    foreach ($statements as $i => $statement) {
        $statement = trim($statement);
        
        // Skip comments and empty statements
        if (empty($statement) || preg_match('/^--/', $statement)) {
            continue;
        }
        
        echo "Executing statement " . ($i + 1) . "... ";
        try {
            $pdo->exec($statement);
            echo "SUCCESS\n";
            $success++;
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $errors++;
            
            // Check if this is just a "index already exists" warning, which we can ignore
            if (stripos($e->getMessage(), 'already exists') === false) {
                throw $e; // Re-throw if it's a serious error
            }
        }
    }
    
    // Commit the transaction if all went well
    $pdo->commit();
    echo "\nTransaction committed successfully\n";
    
} catch (Exception $e) {
    // Rollback the transaction if there was a serious error
    $pdo->rollBack();
    echo "\nTransaction rolled back due to error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n==================================\n";
echo "Optimization completed:\n";
echo "  - Statements executed successfully: {$success}\n";
echo "  - Statements with errors: {$errors}\n";
echo "==================================\n";

if ($errors > 0) {
    echo "\nWarning: Some statements failed. Check the output above for details.\n";
    echo "Note: 'already exists' errors are expected if running this script multiple times.\n";
}

echo "\nDone!\n"; 