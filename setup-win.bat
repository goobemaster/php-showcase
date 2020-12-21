@echo off

set PWD=%cd%
set PHPDIR=%TEMP%\..\PHP74
CALL %PHPDIR%\php.exe -v >NUL

if %ERRORLEVEL%==1 (
	echo PHP needs to be installed.
	echo If you see any errors, you have to re-run the setup as administrator
	timeout /t 5 /nobreak
	IF not EXIST %PHPDIR% (
		mkdir %PHPDIR%
	)
	IF not EXIST %PHPDIR%\php.zip (
		bitsadmin /transfer php74 /download /priority normal https://windows.php.net/downloads/releases/php-7.4.13-Win32-vc15-x64.zip %PHPDIR%\php.zip
		bitsadmin /transfer unzip /download /priority normal http://stahlworks.com/dev/unzip.exe %PHPDIR%\unzip.exe
		bitsadmin /transfer vcruntime2015 /download /priority normal https://aka.ms/vs/16/release/VC_redist.x64.exe %TEMP%\vc_redist.x64.exe
	)
	cd %PHPDIR%
	unzip php.zip
	cd %TEMP%
	echo Please wait until Microsoft Visual C++ Runtime 2015 is installed...
	echo Please accept the administrative elevation popup!
	vc_redist.x64.exe /install /q /norestart
	cd %PWD%
	echo If PHP does not start, please apply all OS updates and run the setup again.
	echo All done.
) else (
	echo PHP is already installed. Good!
)
exit /b
