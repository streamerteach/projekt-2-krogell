<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/handy_methods.php';
require_once __DIR__ . '/../functions/security.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload failed with error code: ' . $file['error'];
        echo json_encode($response);
        exit;
    }
    
    // validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $response['message'] = 'Only JPG, PNG and GIF files are allowed';
        echo json_encode($response);
        exit;
    }
    
    // validate file size (max 5mb)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File too large (max 5MB)';
        echo json_encode($response);
        exit;
    }
    
    // create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . date('YmdHis') . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // save to database
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO user_images (user_id, image_path, is_profile, uploaded_at) 
                  VALUES (:user_id, :path, :is_profile, NOW())";
        
        $stmt = $db->prepare($query);
        $success = $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':path' => '/nottinder/uploads/' . $filename,
            ':is_profile' => isset($_POST['set_as_profile']) ? 1 : 0
        ]);
        
        if ($success) {
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully';
            $response['filename'] = $filename;
            $response['path'] = '/nottinder/uploads/' . $filename;
        } else {
            $response['message'] = 'Failed to save to database';
            // delete the file if database insert failed
            unlink($filepath);
        }
    } else {
        $response['message'] = 'Failed to move uploaded file';
    }
} else {
    $response['message'] = 'No file uploaded';
}

echo json_encode($response);
?>