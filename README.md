# はじめに

- このリポジトリはlaravelの実験用のリポジトリ
- ベタですが「レシピ作成」のRestAPIを作成しています

# 技術概要

- laravelを使用
- Dockerでローカル環境を構築
- CloudFormation でAWSでリソースを定義
- CodeDeployはCloudFormationで定義しているが、CodePipelineはまだ定義していない

# 作成中のAPI

## エンドポイント

`{{各環境でのURL}}/api/`

## リクエストヘッダー

下記ヘッダーをリクエストに含めること

- `Accept:application/json`
- `Content-Type:application/json`
- `X-Content-Type-Options:nosniff`
- `X-Requested-With:XMLHttpRequest`


## ログイン関連API
| メソッド |     URL      | Request Body                | 説明              | 
|------|:------------:|-----------------------------|-----------------|
| GET  |  /users/me   | -                           | 現在ログイン中のユーザーを取得 |
| POST |    /users    | `name`, `email`, `password` | 新規ユーザーを作成       |
| POST | /users/login | `email`, `password`         | ログイン処理を実行       |

## レシピ関連API

| メソッド   |         URL          | Request Body           | 説明                |
|--------|:--------------------:|------------------------|-------------------|
| GET    |       /recipes       | -                      | レシピ一覧を取得          |
| GET    | /recipes/{recipe_id} | -                      | recipe_id のレシピを取得 |
| POST   |       /recipes       | `title`, `description` | レシピを新規に作成         |
| PATCH  | /recipes/{recipe_id} | `title`, `description` | recipe_id のレシピを更新            |
| DELETE | /recipes/{recipe_id} | -                      | recipe_id のレシピを削除            |

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

|     名前     | バージョン  | 記載箇所                                                         |
|:----------:|--------|--------------------------------------------------------------|
|  laravel   | 10.3.3 | src/composer.json                                            |
|    php     | 8.2    | docker/php/Dockerfile, src/composer.json, app_server_init.sh |
|  composer  | 2.5.4  | docker/php/Dockerfile                                        |


# Docker&laravel コマンドメモ
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

## 踏み台サーバーにSSHログイン

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
