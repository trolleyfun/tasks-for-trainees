<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Test\Multiprop\IblockMultiProperty;

class test_multiprop extends CModule
{
    public $MODULE_ID = 'test.multiprop';

    public function __construct()
    {
        $this->MODULE_ID = 'test.multiprop';
        $arModuleVersion = [];
        include_once(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'] ?? '';
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'] ?? '';
        $this->MODULE_NAME = Loc::getMessage('MULTIPROP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MULTIPROP_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('MULTIPROP_MODULE_PARTNER_NAME');
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
            IblockMultiProperty::class,
            'GetUserTypeDescription'
        );
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            IblockMultiProperty::class,
            'GetUserTypeDescription'
        );
    }
}
