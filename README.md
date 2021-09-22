# Public Backend Module

### 專案建置流程
#### - composer install

<br>

### config
#### - app/config/app.php
除了資料庫連線帳密，其餘共用環境變數皆在此處，
資料庫的連線帳密應應安全性需將env.json提交給MIS匯入，
格式如sample_env.json。

<br>

### 路由架構
### - api
#### 分為兩個 '/api', '/api/auth'<br>
#### auth均走middleware->Authenticate做token驗證<br>
### - web
#### 使用上注意不能與api的router重疊

<br>

### Repository Design 說明
#### Controller 負責接收client端傳送的值
#### Service 處理流程邏輯
#### Repository 處理Model事項

<br>

### 檔案說明
#### - Crypto => 加解密共用class
#### - Util => 共用class

<br>

### 如果專案需走JWT可以參考以下流程<br>
#### - 請參考[RSA key 生產流程]把 public_key.pem, private_key.pem 放置 ./app/Crypto/key<br>
#### - 請參考[產生jwt:secret]並在.env 加入參數 JWT_SECRET

<br>

### RSA key 生產流程
#### openssl genrsa -out rsa_private_key.pem 2048<br>
#### openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem<br>
#### openssl rsa -in rsa_private_key.pem -pubout -out public_key.pem

<br>

### 產生 jwt:secret
#### php artisan jwt:secret
