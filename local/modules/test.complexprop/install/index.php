<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Test\Complexprop\IblockComplexProperty;

class test_complexprop extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'test.complexprop';
        $arModuleVersion = [];
        include_once(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'] ?? '';
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'] ?? '';
        $this->MODULE_NAME = Loc::getMessage('COMPLEXPROP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('COMPLEXPROP_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('COMPLEXPROP_MODULE_PARTNER_NAME');
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallEvents();
    }

    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            IblockComplexProperty::class,
            'GetUserTypeDescription'
        );
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            IblockComplexProperty::class,
            'GetUserTypeDescription'
        );
    }
}
