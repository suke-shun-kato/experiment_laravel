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
sudo systemctl status nginx

# インスタンス起動時に自動起動するように設定
sudo systemctl enable nginx


#### PHPをインストール

# インストール可能なPHPのバージョンを確認
#amazon-linux-extras list | grep php

# amazon-linux-extrasリポジトリからPHPをインストール
sudo amazon-linux-extras install -y php8.2

# PHPのバージョンの確認
php -v

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
sudo curl -o /etc/php-fpm.d/www.conf https://raw.githubusercontent.com/suke-shun-kato/experiment_laravel/master/aws/app/www.conf

# php-fpm を再起動
sudo systemctl restart php-fpm.service

# nginxの設定をする
sudo mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bk
sudo curl -o /etc/nginx/nginx.conf https://raw.githubusercontent.com/suke-shun-kato/experiment_laravel/master/aws/app/nginx.conf

# 設定が正しいかテスト
sudo nginx -t

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

# 確認
mkdir /var/www/public
echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title></title></head><body>nginx is alive.</body></html>' > /var/www/public/health_check.html
# ブラウザでアクセスして確認

# 削除
# rm -rf /var/www/public/

#### Composer をインストール
# Composer をインストール
export HOME="/root" # シェルスクリプトから実行する場合は環境変数HOMEがないとエラーが出るので、rootユーザのHOMEの場所を設定
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# 実行ファイルをbinに移動。Composerはrootユーザーで実行しないようにと公式に記載があるので /bin ではなく /usr/local/bin に移動
sudo mv composer.phar /usr/local/bin/composer

# インストールを確認
composer -v

#### Gitをインストール
# Gitをインストール
sudo yum install -y git

# 確認
git -v

# .sshへ移動
#cd ~/.ssh
#ssh-keygen
#sudo cat id_rsa.pub # 確認
#ssh -T git@github.com # 確認
#cd /var/www
#git clone git@github.com:[アカウント名]/[リポジトリ名].git .


#### MySQLのクライアントをインストール
# MariaDBが入っているか確認
#sudo yum list installed | grep mariadb
# MariaDBアンインストール
sudo yum remove -y mariadb-libs

# MySQLの公式リポジトリの追加
sudo yum localinstall -y https://dev.mysql.com/get/mysql80-community-release-el7-3.noarch.rpm
# 追加の確認
#sudo yum repolist all | grep mysql
# mysql8.0 のリポジトリを無効化
sudo yum-config-manager --disable mysql80-community
# mysql5.7 リポジトリを有効化
sudo yum-config-manager --enable mysql57-community
# GPGキーをインストール 参考：https://blog.katsubemakito.net/mysql/mysql-update-error-gpg
sudo rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2022
#  mysql-community-clientをインストールする
sudo yum install -y mysql-community-client
# インストールを確認
mysql --version
# DBに接続
#mysql -h wwwww-databaseinstance1-0yfydcfafk0n.cy6bnrwkczia.ap-northeast-1.rds.amazonaws.com -P 3306 -u admin -p


#### CodeDeployAgentのインストール。参考URL https://qiita.com/nasuB7373/items/081f5974e31419a1a844
sudo yum install -y ruby
cd /home/ec2-user
# 指定URLのファイルを取得。-O: ファイル名はそのままの install。
# CodeDeployエージェントのダウンロードリンクはリージョンによって異なるので注意（現在は東京リージョンの）
sudo curl -O https://aws-codedeploy-ap-northeast-1.s3.amazonaws.com/latest/install
sudo chmod +x install
# インストール
sudo ./install auto
# インストール確認
sudo service codedeploy-agent status
# インストール用のファイルを削除
sudo rm -f install