@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1
set z7="C:\Program Files\7-Zip\7z.exe"
set archive=Joomla-Acumulus-%version%.zip
set package1=packages\com_acumulus.zip
set package2=packages\plg_acumulus_vm3.zip
set package3=packages\plg_acumulus_hs.zip

rem delete, recreate and check zip package.
del %archive% 2> nul
cd acumulus
rem zip component.
del %package1% 2> nul
%z7% a -xr!.git -tzip %package1% com_acumulus | findstr /i "Failed Error"
%z7% t %package1% | findstr /i "Processing Everything Failed Error"

rem zip plugins.
del %package2% 2> nul
%z7% a -tzip %package2% plg_acumulus_vm3 | findstr /i "Failed Error"
%z7% t %package2% | findstr /i "Processing Everything Failed Error"

del %package3% 2> nul
%z7% a -tzip %package3% plg_acumulus_hs | findstr /i "Failed Error"
%z7% t %package3% | findstr /i "Processing Everything Failed Error"

rem zip package.
%z7% a -tzip ..\%archive% *.xml packages | findstr /i "Failed Error"
cd ..
%z7% t %archive% | findstr /i "Processing Everything Failed Error" 
