<?php
header('Content-Type: application/json');

// Konfigurasi koneksi database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'image_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Query untuk mendapatkan daftar file
$sql = "SELECT * FROM images ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path']
        ];
    }
    echo json_encode(['success' => true, 'files' => $files]);
} else {
    echo json_encode(['success' => false, 'message' => 'No files found.']);
}

$conn->close();
