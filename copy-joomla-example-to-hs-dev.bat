@echo off
rem Plugin: link to the HikaShop development installation.
mkdir D:\Projecten\Acumulus\HikaShop\www\plugins\acumulus 2> nul
rmdir /s /q D:\Projecten\Acumulus\HikaShop\www\plugins\acumulus\AcumulusCustomiseInvoice 2> nul
mklink /J D:\Projecten\Acumulus\HikaShop\www\plugins\acumulus\AcumulusCustomiseInvoice D:\Projecten\Acumulus\Webkoppelingen\Joomla\plg_acumulus_customise_invoice
