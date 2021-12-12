# Public Backend Module
| 日期        | 項目                      | 狀態    |
| ---------- | ------------------------- | ------ |
| 2021-08-12 | 建置JWT 框架               | 開發完成 |
| 2021-08-25 | 新增UtilResponse 共用      | 開發完成 |
| 2021-09-17 | 新增財政部 api 共用         | 開發完成 |
| 2021-09-17 | 新增AccuNix api 共用       | 開發完成 |
| 2021-09-22 | 新增env.json 共用          | 開發完成 |
| 2021-10-04 | 新增Line Push Message 共用 | 開發完成 |
| 2021-10-26 | 新增vue frontend           | 開發完成 |
| 2021-12-10 | 加入Swagger機制使用DarkaOnLine/L5-Swagger             | 開發完成 |
| 2021-12-10 | 修正response參數            | 開發完成 |

<br>

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
#### 版本號預設:v1
#### 分為兩個 '/api/{版本號}', '/api/{版本號}/auth'<br>
#### auth均走middleware->Authenticate做token驗證<br>
### - web
#### 使用上注意不能與api的router重疊

<br>

### Repository Design 說明
#### Controller 負責接收client端傳送的值
#### Service 處理商業邏輯
#### Repository 處理Model事項

<br>

### 檔案說明
#### - Crypto => 加解密共用class
#### - Util => 共用class

<br>

### Swagger套件文件撰寫請洽[官方github](https://github.com/DarkaOnLine/L5-Swagger)
#### 相關config請參考 "./config/l5-swagger.php"
#### 專案標題以及相關models撰寫在 .app/Docs
#### 公版預設路徑：{domain}/api/docs

### 產生 swagger文件
#### 相關controller撰寫完執行以下指令會自動產生文件
#### php artisan l5-swagger:generate

<br>

### 共用函數使用說明
#### - AccuNixApi
#### 需新增環境變數 ACCUNIX_LINE_BOT_ID, ACCUNIX_AUTH_TOKEN
#### - Inv(財政部)
#### 需新增環境變數 INV_APP_ID, INV_API_KEY
#### - Line Push Message
#### 需新增環境變數 LINE_BOT_BEAR_TOKEN
#### - Validate
#### 驗證前端資料共用function
#### - UtilResponse
#### api回傳共用function - 使用範例如下：
#### 回傳http status code 客製化
<pre>
<code>

UtilResponse::toJson(200, "response message", {{object}});
</code>
</pre>
#### 固定回傳http status code 200
<pre>
<code>
UtilResponse::successResponse('success', {{object}});
</code>
</pre>
#### 固定回傳http status code 400
<pre>
<code>
UtilResponse::errorResponse('Token is Invalid');
</code>
</pre>

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

<br>

