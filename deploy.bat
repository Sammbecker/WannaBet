@echo off
echo Installing Heroku CLI...
npm install -g heroku

echo Installing Composer...
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

echo Installing dependencies...
php composer.phar install

echo Configuring Git...
set /p git_username="Enter your Git username: "
set /p git_email="Enter your Git email: "
git config --global user.name "%git_username%"
git config --global user.email "%git_email%"

echo Logging in to Heroku...
heroku login

echo Creating Heroku app...
heroku create

echo Setting up buildpacks...
heroku buildpacks:set heroku/php

echo Deploying to Heroku...
git add .
git commit -m "Deploy to Heroku"
git push heroku main

echo Deployment complete! Your site should be live at the URL provided above.
pause 