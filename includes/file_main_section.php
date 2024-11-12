<!-- Просмотр файла -->
<section id="file">
    <h1>Тип файла: <?=htmlspecialchars($arFile['type'])?></h1>
    <form id="update-file" action="" method="post">
    <?php if ($arFile['isText']): ?>
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <button type="submit" name="update_file_button"
                class="form-button">Сохранить</button>
            <a href="index.php?path=<?=htmlspecialchars(urlencode($arFile['parentPath']))?>"
                class="form-button">Назад</a>
        </div>
    <?php endif; ?>
        <div class="input-container">
            <label for="file_name">Название:</label>
            <input type="text" name="file_name" id="file_name" class="form-input"
                value="<?=htmlspecialchars($arFile['name'])?>">
        </div>
    <?php if ($arFile['isText']): ?>
        <div class="input-container">
            <textarea name="file_content"
                class="file-textarea"><?=htmlspecialchars($arFile['content'])?></textarea>
        </div>
    <?php endif; ?>
        <div class="form-container">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <button type="submit" name="update_file_button"
                class="form-button">Сохранить</button>
            <a href="index.php?path=<?=htmlspecialchars(urlencode($arFile['parentPath']))?>"
                class="form-button">Назад</a>
        </div>
    </form>
</section>
