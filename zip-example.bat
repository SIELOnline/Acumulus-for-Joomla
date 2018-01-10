@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1
set z7="C:\Program Files\7-Zip\7z.exe"
set archive=Joomla-Acumulus-Customise-Invoice-%version%.zip

rem delete, recreate and check zip package.
del %archive% 2> nul
%z7% a -tzip %archive% plg_acumulus_customise_invoice | findstr /i "Failed Error"
%z7% t %archive% | findstr /i "Processing Everything Failed Error"
