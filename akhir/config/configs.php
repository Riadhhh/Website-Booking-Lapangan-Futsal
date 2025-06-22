<?php
// Mengaktifkan output buffering dan timezone default
ob_start();
date_default_timezone_set("Asia/Jakarta");

/**
 * Fungsi sql()
 * Menjalankan query SQL menggunakan PDO.
 *
 * @param string $query_string  Query SQL
 * @param array  $params        Parameter query (optional)
 * @param bool   $single        Ambil satu data saja (optional)
 *
 * @return array [row => jumlah baris, data => hasil data atau null]
 */
function sql(string $query_string, array $params = [], bool $single = false): array {
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "futsal";

    try {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = "mysql:host=$hostname;dbname=$database;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        $stmt = $pdo->prepare($query_string);
        $stmt->execute($params);

        if (stripos(trim($query_string), 'select') === 0) {
            if ($single) {
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    "row" => $fetch ? 1 : 0,
                    "data" => $fetch
                ];
            } else {
                $fetchAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return [
                    "row" => count($fetchAll),
                    "data" => $fetchAll
                ];
            }
        }

        return [
            "row" => $stmt->rowCount(),
            "data" => null
        ];
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
