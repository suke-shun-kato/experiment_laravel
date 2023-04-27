#!/bin/bash -ex
# デプロイ前のインスタンス作成時に作成したファイルを削除（重複したファイルがあるエラーになるので）
sudo rm -f /var/www/public/health_check.html

