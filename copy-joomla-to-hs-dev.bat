@echo off
rem Component: link to the HikaShop development installation.
rmdir /s /q  D:\Projecten\Acumulus\HikaShop\www\administrator\components\com_acumulus 2> nul
mklink /J D:\Projecten\Acumulus\HikaShop\www\administrator\components\com_acumulus D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin

rem Plugins: link to the HikaShop development installation.
rmdir /s /q  D:\Projecten\Acumulus\HikaShop\www\plugins\vmcoupon\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\HikaShop\www\plugins\vmcoupon\acumulus D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\plg_acumulus_vm3
rmdir /s /q  D:\Projecten\Acumulus\HikaShop\www\plugins\hikashop\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\HikaShop\www\plugins\hikashop\acumulus D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\plg_acumulus_hs
