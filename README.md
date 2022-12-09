# IrohaBoard(魔改造バージョン)のコンテナ起動手順

## 資材の配置

- 下記ファイルを任意のディレクトリ配下に配置する
  - docker-compose.yml
  - Dockerfile
  - httpd.conf
  - database.php

## ビルド

```bash
$ docker-compose build
```

## 起動

```bash
$ docker-compose up -d
```

## IrohaBoard のセットアップ

- ブラウザから下記 URL にアクセスする
  - http://localhost:7654/install
- 管理者アカウントを作成する
