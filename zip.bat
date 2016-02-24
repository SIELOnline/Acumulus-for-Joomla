@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

del Joomla-Acumulus-%version%.zip 2> nul
cd acumulus
rem zip component.
del packages\com_acumulus.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\com_acumulus.zip com_acumulus | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t packages\com_acumulus.zip | findstr /i "Processing Everything Failed Error"

rem zip plugin.
del packages\plg_acumulus_vm3.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\plg_acumulus_vm3.zip plg_acumulus_vm3 | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t packages\plg_acumulus_vm3.zip | findstr /i "Processing Everything Failed Error"

del packages\plg_acumulus_hs.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\plg_acumulus_hs.zip plg_acumulus_hs | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t packages\plg_acumulus_hs.zip | findstr /i "Processing Everything Failed Error"

rem zip package.
"C:\Program Files\7-Zip\7z.exe" a -tzip ..\Joomla-Acumulus-%version%.zip *.xml packages | findstr /i "Failed Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t Joomla-Acumulus-%version%.zip | findstr /i "Processing Everything Failed Error" 
