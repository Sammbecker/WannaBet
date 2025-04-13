@echo off
echo Configuring Git...

set /p git_username="Sammbecker5"
set /p git_email="u21509418@tuks.co.za"
git config --global user.name "%git_username%"
git config --global user.email "%git_email%"

echo Initializing Git repository...
git init

echo Adding files...
git add .

echo Committing changes...
git commit -m "Initial commit"

echo Adding remote repository...
set /p repo_url="Enter your GitHub repository URL (e.g., https://github.com/username/repo.git): "
git remote add origin %repo_url%

echo Pushing to GitHub...
git push -u origin main

echo Done! Your code has been pushed to GitHub.
pause 