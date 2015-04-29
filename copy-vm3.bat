@echo off
rem link Common library to here.
mklink /J D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\admin\libraries\Siel D:\Projecten\Acumulus\Webkoppelingen\Library\Siel

rem link acumulus.xml and com_acumulusInstallerScript.php to root install folder.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\acumulus.xml D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\admin\acumulus.xml
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulusInstallerScript.php D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\admin\com_acumulusInstallerScript.php

rem Link all files and directories for the VirtueMart module to the VirtueMart development installation.
mklink /J D:\Projecten\Acumulus\VirtueMart\www\administrator\components\com_acumulus D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\admin
