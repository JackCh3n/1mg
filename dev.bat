@echo off
title 1mg 图床 - 本地测试
setlocal EnableExtensions

rem ============================================================
rem  1mg 图床本地测试脚本 (GBK编码,适配中文Windows cmd)
rem  用法: 双击运行,或命令行 dev.bat [--no-browser]
rem ============================================================

rem 项目目录 = 本脚本所在目录(去掉结尾反斜杠,避免 /D 参数转义问题)
set "ROOT=%~dp0"
if "%ROOT:~-1%"=="\" set "ROOT=%ROOT:~0,-1%"
set "PORT=18938"
set "URL=http://127.0.0.1:%PORT%/"

rem ---- 定位 php: 优先 PATH,其次常见安装位置 ----
set "PHP=php"
where php >nul 2>nul
if errorlevel 1 (
    if exist "D:\xiao\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe" (
        set "PHP=D:\xiao\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe"
    ) else (
        echo [错误] 未找到 php 命令。
        echo 请把 php.exe 加入 PATH,或编辑本脚本顶部的 PHP 变量指定完整路径。
        pause
        exit /b 1
    )
)

rem ---- 端口已占用 = 服务已在运行,直接打开浏览器 ----
netstat -ano | findstr /C:":%PORT%" | findstr /C:"LISTENING" >nul 2>nul
if not errorlevel 1 (
    echo [提示] 端口 %PORT% 已有 1mg 服务在运行,直接打开浏览器。
    if /i not "%~1"=="--no-browser" start "" "%URL%"
    exit /b 0
)

echo ==================================================
echo   1mg 图床 - 本地测试服务
echo.
echo   首页:       %URL%
echo   管理后台:   %URL%admin/login.php
echo   默认账号:   admin / admin123456
echo   API 文档:   %URL%api.php
echo.
echo   关闭服务窗口或按 Ctrl+C 即可停止服务
echo ==================================================
echo.

rem ---- 新窗口启动 PHP 内置服务器(工作目录=项目目录) ----
start "1mg-dev-server" /D "%ROOT%" cmd /k ""%PHP%" -S 127.0.0.1:%PORT%"

rem ---- 等待服务就绪后打开浏览器 ----
ping -n 3 127.0.0.1 >nul
if /i not "%~1"=="--no-browser" (
    start "" "%URL%"
    echo 浏览器已打开,服务在新窗口运行,请勿关闭该窗口
) else (
    echo 服务已启动: %URL%
)
echo.
pause
