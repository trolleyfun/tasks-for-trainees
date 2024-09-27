<?php 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/* Array of Form Answers */
$ans_array = $arResult["arAnswers"];
?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult['FORM_TITLE']?></div>
        <?php if ($arResult["isFormDescription"]) { ?>
            <div class="contact-form__head-text"><?=$arResult['FORM_DESCRIPTION']?></div>
        <?php } ?>
    </div>
    <form class="contact-form__form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
        <input type="hidden" name="WEB_FORM_ID" value="<?=$arParams['WEB_FORM_ID']?>">
        <input type="hidden" name="web_form_apply" value="Y">
        <?=bitrix_sessid_post()?>

        <div class="contact-form__form-inputs">
            <?php 
            if (isset($ans_array["name"]) && is_array($ans_array["name"])) {
                /* ID of Name Answer */
                $ans_id = $ans_array["name"][0]["ID"];
                $inp_name = "form_text_" . $ans_id;
            ?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_name">
                    <div class="input__label-text">Ваше имя*</div>
                    <input class="input__input" type="text" id="medicine_name" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                </label></div>
            <?php } ?>

            <?php 
            if (isset($ans_array["company"]) && is_array($ans_array["name"])) {
                /* ID of Company Answer */
                $ans_id = $ans_array["company"][0]["ID"];
                $inp_name = "form_text_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_company">
                    <div class="input__label-text">Компания/Должность*</div>
                    <input class="input__input" type="text" id="medicine_company" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                </label></div>
            <?php } ?>

            <?php 
            if (isset($ans_array["email"]) && is_array($ans_array["email"])) {
                /* ID of E-mail Answer */
                $ans_id = $ans_array["email"][0]["ID"];
                $inp_name = "form_email_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_email">
                    <div class="input__label-text">Email*</div>
                    <input class="input__input" type="email" id="medicine_email" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification">Неверный формат почты</div>
                </label></div>
            <?php } ?>

            <?php 
            if (isset($ans_array["phone"]) && is_array($ans_array["phone"])) {
                /* ID of E-mail Answer */
                $ans_id = $ans_array["phone"][0]["ID"];
                $inp_name = "form_text_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_phone">
                    <div class="input__label-text">Номер телефона*</div>
                    <input class="input__input" type="tel" id="medicine_phone" data-inputmask="'mask': '+79999999999',
                    'clearIncomplete': 'true'" maxlength="12" x-autocompletetype="phone-full" name="<?=$inp_name?>"
                    value="" required="">
                </label></div>
            <?php } ?>
        </div>
        <!-- /.contact-form__form-inputs -->

        <?php 
        if (isset($ans_array["message"]) && is_array($ans_array["message"])) {
            /* ID of E-mail Answer */
            $ans_id = $ans_array["message"][0]["ID"];
            $inp_name = "form_textarea_" . $ans_id;
        ?>
            <div class="contact-form__form-message">
                <div class="input"><label class="input__label" for="medicine_message">
                    <div class="input__label-text">Сообщение</div>
                    <textarea class="input__input" type="text" id="medicine_message" name="<?=$inp_name?>" value="">
                    </textarea>
                    <div class="input__notification"></div>
                </label></div>
            </div>
            <!-- /.contact-form__form-message -->
        <?php } ?>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;
                принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.
            </div>
            <button type="submit" name="web_form_submit" class="form-button contact-form__bottom-button" 
            data-success="Отправлено" data-error="Ошибка отправки">
                <div class="form-button__title">Оставить заявку</div>
            </button>
        </div>
    </form>
</div>
