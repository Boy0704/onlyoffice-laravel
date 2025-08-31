<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Preview: {{ $document->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h3 {
            color: #333;
            font-size: 18px;
            margin: 0;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        #onlyoffice-preview {
            width: 100%;
            height: calc(100vh - 60px);
        }

        .preview-info {
            position: absolute;
            top: 70px;
            right: 20px;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            display: none;
        }

        .preview-info.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media print {
            .header {
                display: none;
            }
            #onlyoffice-preview {
                height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>📄 Preview: {{ $document->title }}</h3>
        <div class="header-buttons">
            <button onclick="toggleInfo()" class="btn btn-info">ℹ️ Info</button>
            <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-primary">✏️ Edit</a>
            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success">⬇️ Download</a>
            <button onclick="shareDocument()" class="btn btn-secondary">🔗 Share</button>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
    </div>

    <div id="preview-info" class="preview-info">
        <strong>Mode Preview (Read-Only)</strong><br>
        Anda sedang melihat dokumen dalam mode preview.<br>
        Klik tombol "Edit" untuk mengubah dokumen.
    </div>

    <div id="onlyoffice-preview"></div>

    <script type="text/javascript" src="{{ $onlyofficeUrl }}/web-apps/apps/api/documents/api.js"></script>
    <script type="text/javascript">
        window.onload = function() {
            var config = {!! $config !!};
            config.token = "{{ $token }}";

            // Callback events
            config.events = {
                'onAppReady': onAppReady,
                'onDocumentStateChange': onDocumentStateChange,
                'onError': onError,
                'onWarning': onWarning
            };

            window.docEditor = new DocsAPI.DocEditor("onlyoffice-preview", config);
        };

        function onAppReady() {
            console.log('OnlyOffice Preview Ready');
        }

        function onDocumentStateChange(event) {
            console.log('Document State:', event.data);
        }

        function onError(event) {
            console.error('Error:', event.data);
            alert('Error: ' + event.data.errorDescription);
        }

        function onWarning(event) {
            console.warn('Warning:', event.data);
        }

        function toggleInfo() {
            var info = document.getElementById('preview-info');
            info.classList.toggle('show');
            setTimeout(function() {
                info.classList.remove('show');
            }, 5000);
        }

        function shareDocument() {
            fetch('/documents/{{ $document->id }}/share')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        prompt('Share URL (valid for ' + data.expiresIn + '):', data.shareUrl);
                    }
                });
        }

        // Show info on load
        setTimeout(function() {
            toggleInfo();
        }, 1000);
    </script>
</body>
</html>
