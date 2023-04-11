# はじめに

このリポジトリはlaravelの実験用のリポジトリです
API

# 初期設定（ローカル）

```shell
# ソースDL
git clone git@bitbucket.org:suke-shun-kato/laravel-exp.git

# Docker立ち上げ
cd laravel-exp
docker-compose up -d --build

# laravelなどをインストール
docker-compose exec app-php composer install
docker-compose exec app-php cp .env.local.example .env
docker-compose exec app-php php artisan key:generate

# laravelでマイグレーション実行
docker-compose exec app-php php artisan migrate
# laravelでシーダー実行
docker-compose exec app-php php artisan db:seed
```

# 初期設定（本番）

1. AWSのCloudFormationで `cloud_formaition_prod.yaml` ファイルを実行してサーバーを作成する
1. AWSのCodeDeployを実行してソースコードをデプロイする
1. `cp .env.prod.example .env` を実行した後、手動で `.env` ファイルを編集する
1. `php artisan key:generate` を実行する
2. `php artisan migrate` を実行する

# バージョン

|     名前     |   バージョン  | 記載箇所                    |
|:----------:|-----|-------------------------|
|  laravel   | 10.3.3    | src/composer.json       |
|    php     |  8.1   | docker/php/Dockerfile, src/composer.json |
|  composer  |  2.5.4   | docker/php/Dockerfile   |


# Dockerコマンドメモ
## コンテナに入る

```shell
docker-compose exec app-php ash
```


## マイグレーション

### マイグレーションファイルの作成

```shell
docker-compose exec app-php php artisan make:migration xxxxxx
```

※日付は自動で付く

### マイグレーションの実行

```shell
docker-compose exec app-php php artisan migrate
```

### マイグレーション ダウン

```shell
docker-compose exec app-php php artisan migrate:rollback
```

### DB削除（マイグレーションダウンではない）→マイグレーション実行→シーダー実行

```shell
docker-compose exec app-php php artisan migrate:fresh --seed
```

## シーダー

### シーダーファイルの作成

```shell
docker-compose exec app-php php artisan make:seeder CareerSeeder
```

### シーダーの実行

```shell
docker-compose exec app-php php artisan db:seed
```

## ORM

### モデルの作成

```shell
docker-compose exec app-php php artisan make:model Models/Career
```

※モデル名は命名規則があるのでデータベースのテーブル名から 's' を抜いたものにしないといけない


# AWS用コマンドメモ

## 踏み台サーバー

```shell
ssh -o ProxyCommand='ssh -W %h:%p -i ~/.ssh/bastion_id_rsa.pem ec2-user@bastion_server' \
-i ~/.ssh/target_id_rsa.pem ec2-user@target_server
```

`-W %h:%p` 中継サーバーを通してターゲットサーバーに接続するためのオプション

# 参考リンク

## laravel

- [ReaDouble - 日本語の公式みたいなもの](https://readouble.com/)
- [laravel公式（英語）](https://laravel.com/)
- [laravel公式のドキュメント（英語）](https://laravel.com/docs/10.x)

## Docker環境構築

- [Qiita - 【初心者向け】20分でLaravel開発環境を爆速構築するDockerハンズオン](https://qiita.com/ucan-lab/items/56c9dc3cf2e6762672f4)
