<?php

use Arhitector\Yandex\Client\Exception\NotFoundException;
use Arhitector\Yandex\Client\Exception\UnauthorizedException;
use Arhitector\Yandex\Disk\Exception\AlreadyExistsException;
use Trolleyfun\Yandex\Exception\FileCreationFailureException;
use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;
use Trolleyfun\Yandex\FolderManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$arFolder = [];
$arErrors = [];
$displayMainSection = true;
$arFolder['path']= $_GET['path'] ?? 'disk:/';

try {
    $folder = new FolderManager(OAUTH_TOKEN, urldecode($arFolder['path']));
    $arFolder['newFolderName'] = '';
    $arFolder['isRoot'] = $folder->isRoot();
    $arFolder['name'] = $folder->getName();
    $arFolder['itemsListHtml'] = $folder->displayItems();

    if (isset($_POST['create_folder_button'])) {
        $arFolder['newFolderName'] = $_POST['folder_name'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $folder->createFolder($arFolder['newFolderName']);
            header('Location: ' . $_SERVER['REQUEST_URI']);
        } else {
            $arErrors[] = 'Неверный CSRF-токен';
            $displayMainSection = true;
        }
    }

    if (isset($_POST['upload_file_button'])) {
        $file = $_FILES['file'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $folder->uploadFile($file);
            header('Location: ' . $_SERVER['REQUEST_URI']);
        } else {
            $arErrors[] = 'Неверный CSRF-токен';
            $displayMainSection = true;
        }
    }

    if (isset($_POST['update_folder_button'])) {
        $arFolder['name'] = $_POST['folder_name'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $folder->changeFolderName($arFolder['name']);
            header('Location: index.php?path=' . urlencode($folder->getPath()));
        } else {
            $arErrors[] = 'Неверный CSRF-токен';
            $displayMainSection = true;
        }
    }

    if (isset($_POST['delete_item_button'])) {
        $arItemPath = $_POST['item_path'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (hash_equals($_SESSION['csrf_token'], $csrfToken) && is_array($arItemPath)) {
            foreach ($arItemPath as $path) {
                $folder->deleteResource($path);
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
        } else {
            $arErrors[] = 'Неверный CSRF-токен';
            $displayMainSection = true;
        }
    }
} catch (UnauthorizedException | FileCreationFailureException $e) {
    $arErrors[] = $e->getMessage();
    $displayMainSection = false;
} catch (AlreadyExistsException | UnexpectedValueException $e) {
    $arErrors[] = $e->getMessage();
    $displayMainSection = true;
} catch (NotFoundException | ResourceTypeNotValidException $e) {
    header('Location: error.php');
}

include('includes/header.php');
include('includes/errors.php');
if ($displayMainSection) {
    include('includes/folder_main_section.php');
}
include('includes/footer.php');
