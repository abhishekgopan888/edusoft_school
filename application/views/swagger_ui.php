<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School API Docs</title>
    <link rel="stylesheet" href="<?php echo base_url('api/swagger-ui-assets/swagger-ui.css'); ?>" onerror="this.onerror=null;this.href='https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css';">
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }

        *,
        *::before,
        *::after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="<?php echo base_url('api/swagger-ui-assets/swagger-ui-bundle.js'); ?>"></script>
    <script src="<?php echo base_url('api/swagger-ui-assets/swagger-ui-standalone-preset.js'); ?>"></script>
    <script>
        function initSwagger() {
            if (typeof SwaggerUIBundle === 'undefined') {
                var s1 = document.createElement('script');
                s1.src = 'https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js';
                s1.onload = function() {
                    var s2 = document.createElement('script');
                    s2.src = 'https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js';
                    s2.onload = startUi;
                    document.body.appendChild(s2);
                };
                document.body.appendChild(s1);
            } else {
                startUi();
            }
        }

        function startUi() {
            window.ui = SwaggerUIBundle({
                url: "<?php echo site_url('docs/json'); ?>?t=" + new Date().getTime(),
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: 'StandaloneLayout'
            });
        }

        window.onload = initSwagger;
    </script>
</body>
</html>
