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

// Pastikan ada ID yang dikirim
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$new_name = isset($_POST['new_name']) ? trim($_POST['new_name']) : null;
$file_uploaded = isset($_FILES['image']) && $_FILES['image']['error'] == 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    // Ambil informasi gambar lama berdasarkan ID
    $stmt = $conn->prepare("SELECT file_path FROM images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($old_file_path);
        $stmt->fetch();
        $stmt->close();

        $uploadDir = './uploads/';
        $old_extension = pathinfo($old_file_path, PATHINFO_EXTENSION);
        $new_file_path = $old_file_path; // Default pakai path lama
        $new_filename = basename($old_file_path); // Nama file tetap sama jika tidak ada perubahan

        // Jika ada file baru yang diupload
        if ($file_uploaded) {
            $fileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF are allowed.']);
                exit;
            }

            // Hapus file lama jika ada
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }

            // Gunakan nama asli file yang diunggah
            $new_filename = basename($_FILES['image']['name']);
            $new_file_path = $uploadDir . $new_filename;

            // Tambahkan angka jika nama sudah ada
            $counter = 1;
            while (file_exists($new_file_path)) {
                $new_filename = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME) . " ($counter)." . $fileType;
                $new_file_path = $uploadDir . $new_filename;
                $counter++;
            }

            // Upload file baru
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $new_file_path)) {
                echo json_encode(['error' => 'Failed to upload new image.']);
                exit;
            }
        }

        // Update database
        $stmt = $conn->prepare("UPDATE images SET file_name = ?, file_path = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_filename, $new_file_path, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Image updated successfully.',
                'new_file_name' => $new_filename
            ]);
        } else {
            echo json_encode(['error' => 'Failed to update database.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Image not found.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request. ID is required.']);
}

$conn->close();
