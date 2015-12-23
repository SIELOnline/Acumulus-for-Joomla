@echo off

del VirtueMart-3.x-Acumulus-4.x.zip 2> nul
cd acumulus
rem zip component.
del packages\com_acumulus.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\com_acumulus.zip com_acumulus | findstr /b /c:"Error"
"C:\Program Files\7-Zip\7z.exe" t packages\com_acumulus.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"

rem zip plugin.
del packages\plg_acumulus.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\plg_acumulus.zip plg_acumulus | findstr /b /c:"Error"
"C:\Program Files\7-Zip\7z.exe" t packages\plg_acumulus.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"

rem zip package.
"C:\Program Files\7-Zip\7z.exe" a -tzip ..\VirtueMart-3.x-Acumulus-4.x.zip *.xml packages | findstr /b /c:"Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t VirtueMart-3.x-Acumulus-4.x.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"
