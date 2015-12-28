@echo off

del Joomla-Acumulus-4.x.zip 2> nul
cd acumulus
rem zip component.
del packages\com_acumulus.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\com_acumulus.zip com_acumulus | findstr /b /c:"Error"
"C:\Program Files\7-Zip\7z.exe" t packages\com_acumulus.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"

rem zip plugin.
del packages\plg_acumulus_vm3.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\plg_acumulus_vm3.zip plg_acumulus_vm3 | findstr /b /c:"Error"
"C:\Program Files\7-Zip\7z.exe" t packages\plg_acumulus_vm3.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"

del packages\plg_acumulus_hs.zip 2> nul
"C:\Program Files\7-Zip\7z.exe" a -tzip packages\plg_acumulus_hs.zip plg_acumulus_hs | findstr /b /c:"Error"
"C:\Program Files\7-Zip\7z.exe" t packages\plg_acumulus_hs.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"

rem zip package.
"C:\Program Files\7-Zip\7z.exe" a -tzip ..\Joomla-Acumulus-4.x.zip *.xml packages | findstr /b /c:"Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t Joomla-Acumulus-4.x.zip | findstr /b /c:"Processing" /c:"Everything" /c:"Error"
