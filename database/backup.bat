@echo off
REM ============================================================================
REM InternFlow: Daily MySQL Database Backup Script (NFR-REL-02)
REM Universiti Tenaga Nasional (UNITEN) - College of Computing & Informatics
REM ============================================================================

set BACKUP_DIR=%~dp0..\storage\backups
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=*" %%a in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set TIMESTAMP=%%a
if "%TIMESTAMP%"=="" set TIMESTAMP=%date:~-4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_FILE=%BACKUP_DIR%\internflow_backup_%TIMESTAMP%.sql

echo [INFO] Starting InternFlow MySQL backup at %TIMESTAMP%...

REM Check common mysqldump paths (XAMPP / default)
set MYSQLDUMP=mysqldump
if exist "C:\xampp\mysql\bin\mysqldump.exe" set MYSQLDUMP=C:\xampp\mysql\bin\mysqldump.exe
if exist "C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe" set MYSQLDUMP=C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe

"%MYSQLDUMP%" --host=127.0.0.1 --port=3306 --user=root --databases internflow --routines --triggers --single-transaction > "%BACKUP_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Backup completed successfully!
    echo [FILE] %BACKUP_FILE%
) else (
    echo [ERROR] Backup failed with exit code %ERRORLEVEL%. Please check your MySQL service and credentials.
)

REM Retention policy: Keep backups for 30 days
forfiles /p "%BACKUP_DIR%" /s /m *.sql /d -30 /c "cmd /c del @path" 2>nul
echo [INFO] Retained backups up to 30 days.
