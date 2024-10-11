<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult['FORM_TITLE']?></div>
        <?php if ($arResult["isFormDescription"]): ?>
            <div class="contact-form__head-text"><?=$arResult['FORM_DESCRIPTION']?></div>
        <?php endif; ?>
    </div>
    <form name="<?=$arResult['WEB_FORM_NAME']?>" class="contact-form__form" action="<?=POST_FORM_ACTION_URI?>"
    method="POST">
        <input type="hidden" name="WEB_FORM_ID" value="<?=$arParams['WEB_FORM_ID']?>">
        <input type="hidden" name="web_form_submit" value="Y">
        <?=bitrix_sessid_post()?>

        <div class="contact-form__form-inputs">
            <?php 
            if (isset($arResult['arAnswers']['name']) && is_array($arResult['arAnswers']['name'])):
                $ans_id = $arResult['arAnswers']['name'][0]['ID'];
                $inp_name = "form_text_" . $ans_id;
            ?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_name">
                    <div class="input__label-text"><?=GetMessage('NAME_LABEL')?></div>
                    <input class="input__input" type="text" id="medicine_name" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification"><?=GetMessage('NAME_ERROR_TEXT')?></div>
                </label></div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['company']) && is_array($arResult['arAnswers']['company'])):
                $ans_id = $arResult['arAnswers']['company'][0]['ID'];
                $inp_name = "form_text_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_company">
                    <div class="input__label-text"><?=GetMessage('COMPANY_LABEL')?></div>
                    <input class="input__input" type="text" id="medicine_company" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification"><?=GetMessage('COMPANY_ERROR_TEXT')?></div>
                </label></div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['email']) && is_array($arResult['arAnswers']['email'])):
                $ans_id = $arResult['arAnswers']['email'][0]['ID'];
                $inp_name = "form_email_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_email">
                    <div class="input__label-text"><?=GetMessage('EMAIL_LABEL')?></div>
                    <input class="input__input" type="email" id="medicine_email" name="<?=$inp_name?>" value=""
                    required="">
                    <div class="input__notification"><?=GetMessage('EMAIL_ERROR_TEXT')?></div>
                </label></div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['phone']) && is_array($arResult['arAnswers']['phone'])):
                $ans_id = $arResult['arAnswers']['phone'][0]['ID'];
                $inp_name = "form_text_" . $ans_id;
			?>
                <div class="input contact-form__input"><label class="input__label" for="medicine_phone">
                    <div class="input__label-text"><?=GetMessage('PHONE_LABEL')?></div>
                    <input class="input__input" type="tel" id="medicine_phone" data-inputmask="'mask': '+79999999999',
                    'clearIncomplete': 'true'" maxlength="12" x-autocompletetype="phone-full" name="<?=$inp_name?>"
                    value="" required="">
                </label></div>
            <?php endif; ?>
        </div>
        <!-- /.contact-form__form-inputs -->

        <?php 
        if (isset($arResult['arAnswers']['message']) && is_array($arResult['arAnswers']['message'])):
            $ans_id = $arResult['arAnswers']['message'][0]['ID'];
            $inp_name = "form_textarea_" . $ans_id;
        ?>
            <div class="contact-form__form-message">
                <div class="input"><label class="input__label" for="medicine_message">
                    <div class="input__label-text"><?=GetMessage('MESSAGE_LABEL')?></div>
                    <textarea class="input__input" type="text" id="medicine_message" name="<?=$inp_name?>" value=""></textarea>
                    <div class="input__notification"></div>
                </label></div>
            </div>
            <!-- /.contact-form__form-message -->
        <?php endif; ?>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                <?=GetMessage('AGREEMENT_TEXT')?>
            </div>
            <button type="submit" name="web_form_send" class="form-button contact-form__bottom-button" 
            data-success="<?=GetMessage('BUTTON_SUCCESS_MESSAGE')?>"
            data-error="<?=GetMessage('BUTTON_ERROR_MESSAGE')?>">
                <div class="form-button__title"><?=GetMessage('BUTTON_TEXT')?></div>
            </button>
        </div>
        <!-- /.contact-form__bottom -->
    </form>
</div>
