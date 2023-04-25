# はじめに

- このリポジトリはlaravelの実験用のリポジトリ
- ベタですが「レシピ作成」のRestAPIを作成しています

# 技術概要

- laravelを使用
- Dockerでローカル環境を構築
- CloudFormation でAWSで本番環境構築


# 本番環境

## 構成
- アプリケーションサーバーはALBとオートスケーリングで複数台構成（現在CloudFormationではALBとオートスケーリングの機能は作成中なので、CloudFormation上では1台構成になってます）
- 踏み台サーバー経由でアプリケーションサーバーにSSH接続
- DBはAurora（MySQL）でDBクラスター使用（マルチAZ構成でレプリケーション）
- CodeDeployとCodePipelineでGithubからデプロイ（CloudFormationでCodeDeployは定義しているが、CodePipelineはまだ定義していない）

## 初期設定

1. AWSのCloudFormationで `cloud_formaition_prod.yaml` ファイルを実行してサーバーを作成する
1. AWSのCodeDeployを実行してソースコードをデプロイする
2. `php artisan migrate` を実行する

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

| メソッド   |         URL          | Request Body                        | 説明                |
|--------|:--------------------:|-------------------------------------|-------------------|
| GET    |       /recipes       | -                                   | レシピ一覧を取得          |
| GET    | /recipes/{recipe_id} | -                                   | recipe_id のレシピを取得 |
| POST   |       /recipes       | `title`, `description`, `image_ids` | レシピを新規に作成         |
| PATCH  | /recipes/{recipe_id} | `title`, `description`, `image_ids` | recipe_id のレシピを更新 |
| DELETE | /recipes/{recipe_id} | -                                   | recipe_id のレシピを削除 |

## 画像関連
| メソッド |   URL   | Request Body | 説明        |
|------|:-------:|--------------|-----------|
| GET  | /images | `image`      | 画像をアップロード |

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

# バージョン

|     名前     | バージョン  | 記載箇所                                                         |
|:----------:|--------|--------------------------------------------------------------|
|  laravel   | 10.3.3 | src/composer.json                                            |
|    php     | 8.2    | docker/php/Dockerfile, src/composer.json, app_server_init.sh |
|  composer  | 2.5.4  | docker/php/Dockerfile                                        |


# Docker コマンドメモ

## コンテナ立ち上げる

```shell
docker compose up -d
```

## コンテナに入る

```shell
docker compose exec app-php ash
```

## コンテナ削除

### ボリュームは削除しない
```shell
docker compose down
```

### ボリュームは削除する（DBデータやS3データごと削除）
```shell
docker compose down -v
```

## ローカルでMinIO（S3）にアップロードしたファイルを見る

http://localhost:9090/ にアクセスしてコンソールから見る

ユーザー名は `.env` の `MINIO_USER` と `MINIO_PASS` の値
# laravel コマンドメモ

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
