<!--
               _____ _                   _
              |_   _| |__  _ __ ___  ___| |__
                | | | '_ \| '__/ _ \/ __| '_ \
                | | | | | | | |  __/\__ \ | | |
                |_| |_| |_|_|  \___||___/_| |_|

    ------------------------------------------------
    -->
<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="utf-8"/>
    <title>Thresh 文档预览</title>
</head>
<body>
<form name="form">
    <select name="doc">
        <option value="/thresh?type=markdown">测试文档</option>
    </select>
    <input type="button" value="预览 Markdown" onclick="handleShow()">
    <input type="button" value="预览 Swagger" onclick="handleView()">
    <input type="button" value="导出 Markdown" onclick="handleExport('markdown')">
    <input type="button" value="导出 Postman 配置" onclick="handleExport('postman')">
    <input type="button" value="导出 Swagger 配置" onclick="handleExport('swagger')">
</form>
<div id="content"></div>
</body>

<script src="https://cdn.bootcdn.net/ajax/libs/marked/2.0.3/marked.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/axios/0.21.1/axios.min.js"></script>

<script>
    function handleExport(type) {
        window.open(form.doc.value + '&type=' + type);
    }

    function handleShow() {
        axios.get(form.doc.value).then(res => {
            document.getElementById("content").innerHTML = marked(res.data);
        });
    }

    function handleView() {
        window.open('/thresh/swagger');
    }

    handleShow();
</script>
</html>
