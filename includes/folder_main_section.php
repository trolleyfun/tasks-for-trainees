<!-- Верхнее меню -->
<section id="top-menu" class="col-menu">
    <form id="create-folder" action="" method="post">
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="text" name="folder_name" placeholder="Название папки" class="form-input"
                value="<?=htmlspecialchars($arFolder['newFolderName'])?>">
            <button type="submit" name="create_folder_button" class="form-button">Создать</button>
        </div>
    </form>
    <form id="upload-file" action="" method="post" enctype="multipart/form-data">
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="file" name="file" class="form-input">
            <button type="submit" name="upload_file_button" class="form-button">Загрузить</button>
        </div>
    </form>
</section>

<!-- Список файлов и папок -->
<section id="resources">
<?php if ($arFolder['isRoot']): ?>
    <h1>Папка: <?=htmlspecialchars($arFolder['name'])?></h1>
<?php else: ?>
    <form id="update-folder" action="" method="post">
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <label for="folder_name">Папка:</label>
            <input type="text" name="folder_name" id="folder_name"
                value="<?=htmlspecialchars($arFolder['name'])?>" class="form-input">
            <button type="submit" name="update_folder_button" class="form-button">Переименовать</button>
        </div>
    </form>
<?php endif; ?>

    <form id="delete-items" action="" method="post">
        <div class="resource-container">
            <?=$arFolder['itemsListHtml']?>
        </div>
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <button id="select-all" class="form-button">Выбрать все</button>
            <button id="unselect-all" class="form-button">Снять выделение</button>
            <button type="submit" name="delete_item_button"
                class="form-button">Удалить выбранные</button>
        </div>
    </form>
</section>
