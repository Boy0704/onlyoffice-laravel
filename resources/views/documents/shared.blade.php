<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shared: {{ $document->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .shared-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .shared-header h3 {
            margin: 0;
            font-size: 20px;
        }
        .shared-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        #onlyoffice-shared {
            width: 100%;
            height: calc(100vh - 80px);
        }
    </style>
</head>
<body>
    <div class="shared-header">
        <h3>📄 {{ $document->title }}</h3>
        <p>Shared Document - View Only</p>
    </div>

    <div id="onlyoffice-shared"></div>

    <script type="text/javascript" src="{{ $onlyofficeUrl }}/web-apps/apps/api/documents/api.js"></script>
    <script type="text/javascript">
        window.onload = function() {
            var config = {!! $config !!};
            config.token = "{{ $token }}";
            new DocsAPI.DocEditor("onlyoffice-shared", config);
        };
    </script>
</body>
</html>
