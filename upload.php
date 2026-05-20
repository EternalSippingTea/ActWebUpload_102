<?php
$target_dir = "uploads/";
$uploadOk = 1;

// Make sure a file was actually uploaded without errors
if (!isset($_FILES["fileToUpload"]) || $_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_OK) {
    echo "Sorry, no valid file was uploaded.";
    exit;
}

$originalName = $_FILES["fileToUpload"]["name"];
$tmpName      = $_FILES["fileToUpload"]["tmp_name"];
$fileSize     = $_FILES["fileToUpload"]["size"];
$fileType     = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// 1) Extension allowlist: only images are allowed
$allowedExt = ["jpg", "jpeg", "png", "gif"];
if (!in_array($fileType, $allowedExt, true)) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// 2) Validate the real file content (MIME), not just the extension
$allowedMime = [
    "image/jpeg" => ["jpg", "jpeg"],
    "image/png"  => ["png"],
    "image/gif"  => ["gif"],
];
if ($uploadOk === 1) {
    $check = @getimagesize($tmpName);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if ($check === false || !isset($allowedMime[$realMime])) {
        echo "Sorry, the file is not a valid image.";
        $uploadOk = 0;
    } elseif (!in_array($fileType, $allowedMime[$realMime], true)) {
        // Extension does not match the actual content (anti double-extension)
        echo "Sorry, the file extension does not match its content.";
        $uploadOk = 0;
    }
}

// 3) Check file size (500000 bytes = 500KB)
if ($fileSize > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

if ($uploadOk === 0) {
    echo " Your file was not uploaded.";
    exit;
}

// 4) Generate a safe random filename; never trust the user-supplied name
try {
    $safeName = bin2hex(random_bytes(16)) . "." . $fileType;
} catch (Exception $e) {
    $safeName = uniqid("upload_", true) . "." . $fileType;
}
$target_file = $target_dir . $safeName;

if (move_uploaded_file($tmpName, $target_file)) {
    echo "File has been uploaded safely as "
        . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . ".";
} else {
    echo "Sorry, there was an error uploading your file.";
}
?>
