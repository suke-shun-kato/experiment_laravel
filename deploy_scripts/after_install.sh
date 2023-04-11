#!/bin/bash -ex
# /var/www 以下全てのファイルの所有者とグループを ec2-user:nginx に変更（デプロイ後は全てのファイルが root:root になるので）
sudo chown -R ec2-user:nginx /var/www
# /var/www 以下全てのディレクトリのパーミッションを2775に変更する
sudo find /var/www -type d -exec chmod 2775 {} \;
# /var/www 以下全てのファイルのパーミッションを664に変更する
sudo find /var/www -type f -exec chmod 664 {} \;

cd /var/www
composer install

# envファイルの設定、初回だけ
#if [ -f .env ]; then
#  php artisan migrate
#fi

