@echo off
rem Link Common library to here.
mklink /J D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\libraries\Siel D:\Projecten\Acumulus\Webkoppelingen\Library\Siel

rem Link license files to here.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\changelog.txt   D:\Projecten\Acumulus\Webkoppelingen\changelog-4.x.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\license.txt     D:\Projecten\Acumulus\Webkoppelingen\license.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\licentie-nl.pdf
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\leesmij.txt     D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt

rem Component: link acumulus.xml and com_acumulusInstallerScript.php to component install root folder.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\acumulus.xml D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\acumulus.xml
mklink /H D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\com_acumulusInstallerScript.php D:\Projecten\Acumulus\Webkoppelingen\VirtueMart3\acumulus\com_acumulus\admin\com_acumulusInstallerScript.php
