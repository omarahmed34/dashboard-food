<?php
include 'db.php';

function exportDatabase($pdo, $dbname) {
    try {
        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sql .= "\n\n-- Table structure for table `$table` --\n\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $sql .= "-- Dumping data for table `$table` --\n\n";
                foreach ($rows as $row) {
                    $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                    $values = array_map(function($v) use ($pdo) { 
                        return $v === null ? "NULL" : $pdo->quote($v); 
                    }, array_values($row));
                    $sql .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }
        
        $sql .= "\n\nSET FOREIGN_KEY_CHECKS=1;";
        return $sql;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

$sqlDump = exportDatabase($pdo, $dbname);

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="bitesight_db_backup.sql"');
echo $sqlDump;
exit;
?>
