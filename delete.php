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

// Pastikan ada ID dalam URL (?id=3)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil informasi gambar berdasarkan ID
    $stmt = $conn->prepare("SELECT file_path FROM images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($file_path);
        $stmt->fetch();
        $stmt->close();

        // Hapus file dari folder uploads/
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                // Hapus data dari database
                $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
                } else {
                    echo json_encode(['error' => 'Failed to delete image from database.']);
                }
            } else {
                echo json_encode(['error' => 'Failed to delete file from server.']);
            }
        } else {
            echo json_encode(['error' => 'File not found.']);
        }
    } else {
        echo json_encode(['error' => 'Image not found in database.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request. Image ID is required in URL.']);
}

$conn->close();
