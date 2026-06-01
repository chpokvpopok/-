@echo off
echo PHP executable locations:
where /r c:\ php.exe 2>nul
echo ---
echo MySQL executable locations:
where /r c:\ mysql.exe 2>nul
echo ---
echo MySQL services:
sc query type= service state= all | findstr /I MySQL
