@echo off
echo Iniciando servidor PHP em http://localhost:8000
echo Pressione Ctrl+C para parar.
"C:\xampp\php\php.exe" -d extension=gd -S localhost:8000 -t public
pause
