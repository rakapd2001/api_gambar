<?php
header('Content-Type: application/json');

// Konfigurasi koneksi database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'image_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Ambil parameter ID
if (isset($_GET['name'])) {
    $fileName = $_GET['name'];

    // Ambil informasi gambar dari database berdasarkan nama file
    $stmt = $conn->prepare("SELECT file_name, file_path FROM images WHERE file_name = ?");
    $stmt->bind_param("s", $fileName);  // Ganti dengan 's' untuk string
    $stmt->execute();
    $stmt->bind_result($fileName, $filePath);

    if ($stmt->fetch()) {
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo json_encode(['error' => 'File does not exist.']);
        }
    } else {
        echo json_encode(['error' => 'Image not found.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No file name provided.']);
}


$conn->close();
