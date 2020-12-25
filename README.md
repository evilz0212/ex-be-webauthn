# Setting

## Step.1 主機環境設定

1. 安裝 docker & docker-compose

## Step.2 載入專案

1. git clone
2. add .env
3. 修改 storage 資料夾權限

## Step.3 Docker 設定

1. add .env
2. add .crt & .key
3. docker-composer up

## Step.4 專案設定(進入 docker bash)

1. composer install
2. php artisan key:generate
3. php artisan jwt:secret
4. 建立 laravel 資料庫
5. php artisan migrate
