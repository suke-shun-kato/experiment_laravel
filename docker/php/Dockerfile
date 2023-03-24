# 参考 https://qiita.com/ucan-lab/items/56c9dc3cf2e6762672f4
# phpのimageを取得
FROM php:8.1-fpm-alpine

# Dockerfileの作成者を設定
LABEL maintainer="KatoShunsuke<suke.shun.kato2@gmail.com>"

# Composer をinstall
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dockerfile内で使用する変数を定義、 --build-arg TZ="Asia/Tokyo" などで値を指定して上書きもできる
ARG TZ

# Dockerfile内で使用する環境変数を設定
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer

### コマンドを実行
## set -eux
# -e(errexit) ゼロ(正常)以外のステータスコードでコマンドが終了したら、即座に終了する。
# -u(nounset) 未使用の変数などがあればエラーを出力します。
# -x(xtrace) コマンドを実行した時に、そのコマンドと引数を表示する。
#
# apk add パッケージの追加
# apk dell パッケージの削除
#
## docker-compose AlpineLinux にタイムゾーンを設定する
# apk add --update-cache --no-cache --virtual=.build-dependencies tzdata # tzdata（タイムゾーンのデータのパッケージ）を追加
# --update-cache オプション --cache-max-age 1 オプションのエイリアス。1分超えたキャッシュを更新してくれます
# --no-cache 不要なゴミファイルをコンテナ内に作らない
# --virtual=.build-dependencies インストールするパッケージ群に仮の名前を付ける、ここでは .build-dependencies という名前
#
# /usr/share/zoneinfo/ にタイムゾーンのデータがある？
#
## docker-php-ext-install phpの拡張ライブラリのインストール
# bcmath 任意精度数学関数のライブラリ
# pdo_mysql pdo mysql
#
## composer の高速化
# composer config -g repos.packagist composer https://packagist.jp #packagistのミラーサーバを日本国内のサーバに変更する
RUN set -eux \
\
&& apk add --update-cache --no-cache --virtual=.build-dependencies tzdata \
&& cp /usr/share/zoneinfo/${TZ} /etc/localtime \
&& apk del .build-dependencies \
\
&& docker-php-ext-install bcmath pdo_mysql \
\
&& composer config -g repos.packagist composer https://packagist.jp