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

// Cek apakah ada file yang diunggah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['name'])) {
    $uploadDir = './uploads/';
    $fileName = basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $name = trim($_POST['name']); // Ambil input name

    // Validasi dan upload file
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($fileType), $allowedTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            // Simpan informasi gambar ke database
            $stmt = $conn->prepare("INSERT INTO images (name, file_name, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $fileName, $targetFilePath);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully.',
                    'image_id' => $conn->insert_id
                ]);
            } else {
                echo json_encode(['error' => 'Failed to save image data to database.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Failed to upload image.']);
        }
    } else {
        echo json_encode(['error' => 'Invalid file type.']);
    }
} else {
    echo json_encode(['error' => 'No file uploaded or name missing.']);
}

$conn->close();
