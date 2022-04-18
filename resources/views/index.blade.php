<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body id="body">
    <div style="width: 90vw;height:90vh;border: slategray solid 0px;margin: 0 auto;display: block;"></div>

    <script type="text/javascript">
        body.ondragover = function (e) {
            e.preventDefault();
        }
        body.ondrop = function (e) {
            e.preventDefault();
            console.log(e.dataTransfer.files[0]);
            const reader = new FileReader();
            reader.readAsText(e.dataTransfer.files[0], 'UTF-8'); // 讀取檔案
            reader.onload = function (evt) { // 讀取完檔案之後會回來這裡
                data = JSON.parse(evt.target.result); // 讀取檔案內容
                console.log(data);

                data.schemes = ['http', 'https'];
                data.securityDefinitions = {
                    "api_key": {
                        "type": "apiKey",
                        "name": "Authorization",
                        "in": "header"
                    }
                };

                Object.keys(data.paths).forEach(function (route, key) {
                    if (route.indexOf('login') === -1) {
                        Object.keys(data.paths[route]).forEach(function (method) {
                            data.paths[route][method]['security'] = [{
                                "api_key": []
                            }]
                        })
                    }
                });
                console.log(data);

                function download(content, fileName, contentType) {
                    const a = document.createElement("a");
                    const file = new Blob([content], {type: contentType});
                    a.href = URL.createObjectURL(file);
                    a.download = fileName;
                    a.click();
                }

                download(JSON.stringify(data, null, 2), "api-docs.json", "text/json");
            }
        }
    </script>
</body>

</html>