<?php

use Trolleyfun\Yandex\FolderManager;

require_once(__DIR__.'/vendor/autoload.php');

session_start();

if (empty($_SESSION['oauth_token'])) {
    header('Location: login.php');
}

if (!empty($_POST['dir_path'])) {
    $folder = new FolderManager($_SESSION['oauth_token'], $_POST['dir_path']);
    $folderName = $_POST['folder_name'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionCsrf = $_SESSION['csrf_token'] ?? '';
    if (hash_equals($csrfToken, $sessionCsrf)) {
        $folder->createFolder($folderName);
    }
}

$response = ['message' => 'Hello'];

echo json_encode($response);
