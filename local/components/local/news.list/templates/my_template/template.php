<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="barba-wrapper">
    <div class="article-list">
    <?php foreach ($arResult["ITEMS"] as $arItems): ?>
        <?php foreach ($arItems as $item): ?>
            <?php if ($item["DETAIL_TEXT"]): ?>
                <a class="article-item article-list__item" href="<?=$item["DETAIL_PAGE_URL"]?>" data-anim="anim-3">
            <?php else: ?>
                <div class="article-item article-list__item" data-anim="anim-3">
            <?php endif; ?>

            <?php if (is_array($item["PREVIEW_PICTURE"])): ?>
                <div class="article-item__background"><img src="<?=$item["PREVIEW_PICTURE"]["SRC"]?>" alt=""/></div>
            <?php endif; ?>

                <div class="article-item__wrapper">
                <?php if ($item["NAME"]): ?>
                    <div class="article-item__title"><?=$item["NAME"]?></div>
                <? endif; ?>

                <?php if ($item["PREVIEW_TEXT"]): ?>
                    <div class="article-item__content"><?=$item["PREVIEW_TEXT"]?></div>
                <? endif; ?>
                </div>
                <!-- /.article-item__wrapper -->

            <?php if ($item["DETAIL_TEXT"]): ?>
                </a>
            <?php else: ?>
                </div>
            <?php endif; ?>
            <!-- /.article-item.article-list__item -->

        <?php endforeach; ?>
    <?php endforeach; ?>
    </div>
    <!-- /.article-list -->
</div>
<!-- /#barba-wrapper -->
