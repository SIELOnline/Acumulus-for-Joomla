@echo off
rem Link Common library to here.
mkdir D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\lib\siel 2> nul
rmdir /s /q D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\lib\siel\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\lib\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus

rem Link license files to here.
del D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\license.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
del D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\licentie-nl.pdf 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
del D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\leesmij.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt

rem Component: link acumulus.xml and com_acumulusInstallerScript.php to component install root folder.
del D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\acumulus.xml 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\acumulus.xml D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\acumulus.xml
del D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\com_acumulusInstallerScript.php 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\com_acumulusInstallerScript.php D:\Projecten\Acumulus\Webkoppelingen\Joomla\acumulus\com_acumulus\admin\com_acumulusInstallerScript.php
