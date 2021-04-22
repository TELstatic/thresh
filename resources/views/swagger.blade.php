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
    <title>Thresh Swagger 预览</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/swagger-ui/3.46.0/swagger-ui.min.css" rel="stylesheet">
    <style>
        html
        {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *,
        *:before,
        *:after
        {
            box-sizing: inherit;
        }

        body
        {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
</body>
<script src="https://cdn.bootcdn.net/ajax/libs/swagger-ui/3.46.0/swagger-ui-bundle.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/swagger-ui/3.46.0/swagger-ui-standalone-preset.js"></script>
<script>
    window.onload = function() {
        const ui = SwaggerUIBundle({
            'dom_id': '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: 'StandaloneLayout',
            url: '/thresh?type=swagger',
        });

        window.ui = ui;
    };
</script>
</html>
