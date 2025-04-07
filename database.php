<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    // Hàm thực hiện truy vấn SELECT
    public function select($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Hàm thực hiện truy vấn SELECT và lấy 1 bản ghi
    public function selectOne($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Hàm thực hiện truy vấn INSERT
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    // Hàm thực hiện truy vấn UPDATE
    public function update($table, $data, $where, $whereParams = []) {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "$column = ?";
        }
        
        $query = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $where";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_merge(array_values($data), $whereParams));
        
        return $stmt->rowCount();
    }
    
    // Hàm thực hiện truy vấn DELETE
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM $table WHERE $where";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
}
?>
