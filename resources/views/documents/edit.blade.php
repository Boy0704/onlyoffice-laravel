<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit: {{ $document->title }}</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #onlyoffice-editor {
            width: 100%;
            height: 100vh;
        }
    </style>
</head>
<body>
    <div id="onlyoffice-editor"></div>

    <script type="text/javascript" src="{{ $onlyofficeUrl }}/web-apps/apps/api/documents/api.js"></script>
    <script type="text/javascript">
        window.onload = function() {
            var config = {!! $config !!};
            config.token = "{{ $token }}";

            new DocsAPI.DocEditor("onlyoffice-editor", config);
        };
    </script>
</body>
</html>
