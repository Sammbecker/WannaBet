@echo off
echo Configuring Git credentials...

set /p git_username="Enter your Git username: "
set /p git_email="Enter your Git email: "
set /p git_password="Enter your Git password or personal access token: "

git config --global user.name "%git_username%"
git config --global user.email "%git_email%"
git config --global credential.helper store

echo https://%git_username%:%git_password%@github.com > %USERPROFILE%\.git-credentials

echo Git configuration complete!
pause 