# Public Backend Module

### 專案建置流程
#### - composer install
#### - 請參考[RSA key 生產流程]把 public_key.pem, private_key.pem 放置 ./app/Crypto/key<br>
#### - 請參考[產生jwt:secret]並在.env 加入參數 JWT_SECRET<br>



### 路由架構
#### 分為兩個 '/api', '/api/auth'<br>
#### auth均走middleware->Authenticate做token驗證<br>

<br>

### 檔案說明
#### - Crypto => 加解密共用class
#### - Util => 共用class

<br>

### RSA key 生產流程
#### openssl genrsa -out rsa_private_key.pem 2048<br>
#### openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem<br>
#### openssl rsa -in rsa_private_key.pem -pubout -out public_key.pem

<br>

### 產生 jwt:secret
#### php artisan jwt:secret

