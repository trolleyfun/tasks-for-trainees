<!-- Информационное сообщение -->
<section id="success">
    <p class="info success">Изменения в файле успешно сохранены.</p>
    <div class="form-container">
        <a href="index.php" class="form-button">Главная</a>
        <a href="file.php?path=<?=htmlspecialchars(urlencode($arFile['path']))?>"
            class="form-button">Назад</a>
    </div>
</section>
