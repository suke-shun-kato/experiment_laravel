#!/bin/bash -ex

#### パッケージを更新
# パッケージを最新にする
sudo yum update -y

#### nginx をインストール
# NGINXをインストール
sudo amazon-linux-extras install -y nginx1

# サーバ起動
sudo systemctl start nginx

# 起動しているか確認
#sudo systemctl status nginx

# インスタンス起動時に自動起動するように設定
sudo systemctl enable nginx


#### PHPをインストール

# インストール可能なPHPのバージョンを確認
#amazon-linux-extras list | grep php

# amazon-linux-extrasリポジトリからPHPをインストール
sudo amazon-linux-extras install -y php8.2

# PHPのバージョンの確認
#php -v

# laravelに必要な拡張パッケージ（php-bcmath、php-mbstring、php-xml）一度にインストール
sudo yum install -y php-bcmath php-mbstring php-xml

# fpm起動
sudo systemctl start php-fpm.service

# インスタンス起動時に自動起動するように設定
sudo systemctl enable php-fpm

# インストールしたPHPのパッケージを確認
# yum list installed | grep php

#### nginxとphp-fpmの設定をする
# php-fpmの設定をする
sudo mv /etc/php-fpm.d/www.conf /etc/php-fpm.d/www.conf.bk
sudo curl -o /etc/php-fpm.d/www.conf https://raw.githubusercontent.com/suke-shun-kato/experiment_laravel/feature/cloud_formation/aws/www.conf

# php-fpm を再起動
sudo systemctl restart php-fpm.service

# nginxの設定をする
sudo mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bk
sudo curl -o /etc/nginx/nginx.conf https://raw.githubusercontent.com/suke-shun-kato/experiment_laravel/feature/cloud_formation/aws/nginx.conf

## 設定が正しいかテスト
#sudo nginx -t

# nginxを再起動
sudo systemctl restart nginx

# ルートディレクトリを作る
sudo mkdir /var/www

# /var/www の所有者とグループを変更
sudo chown ec2-user:nginx /var/www

# /var/www のパーミッションを設定（今後追加されたコンテンツにも適用）
sudo chmod 2775 /var/www

# ec2-userを nginx グループに追加
sudo usermod -a -G nginx ec2-user

# テスト
# 確認
# mkdir /var/www/public
# echo "<?php phpinfo(); " > /var/www/public/index.php
# ブラウザでアクセスして確認

# 削除
# rm -rf /var/www/public/

#### Composer をインストール
# Composer をインストール
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# 実行ファイルをbinに移動
sudo mv composer.phar /usr/local/bin/composer

# インストールを確認
#composer -v

#### Gitをインストール
# Gitをインストール
sudo yum install git

# 確認
#git -v