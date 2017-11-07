@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

del Joomla-Acumulus-Customise-Invoice-%version%.zip 2> nul
rem zip plugin.
"C:\Program Files\7-Zip\7z.exe" a -tzip Joomla-Acumulus-Customise-Invoice-%version%.zip plg_acumulus_customise_invoice | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t Joomla-Acumulus-Customise-Invoice-%version%.zip | findstr /i "Processing Everything Failed Error"
