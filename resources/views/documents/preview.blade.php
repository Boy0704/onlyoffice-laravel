<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Preview: {{ $document->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 100;
            height: 60px;
        }

        .header h3 {
            color: #333;
            font-size: 18px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 400px;
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
            white-space: nowrap;
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

        /* IMPORTANT: Container untuk OnlyOffice */
        .editor-container {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
        }

        #onlyoffice-preview {
            width: 100%;
            height: 100%;
        }

        /* Loading indicator */
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header h3 {
                font-size: 14px;
                max-width: 150px;
            }

            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }

            .header-buttons {
                gap: 5px;
            }

            .btn-text {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>📄 {{ $document->title }}</h3>
        <div class="header-buttons">
            <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-primary">
                ✏️ <span class="btn-text">Edit</span>
            </a>
            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success">
                ⬇️ <span class="btn-text">Download</span>
            </a>
            <button onclick="shareDocument()" class="btn btn-info">
                🔗 <span class="btn-text">Share</span>
            </button>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                ← <span class="btn-text">Kembali</span>
            </a>
        </div>
    </div>

    <div class="editor-container">
        <div id="onlyoffice-preview">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading document...</p>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{ $onlyofficeUrl }}/web-apps/apps/api/documents/api.js"></script>
    <script type="text/javascript">
        var docEditor;

        window.addEventListener('load', function() {
            initEditor();
        });

        function initEditor() {
            try {
                var config = {!! $config !!};
                config.token = "{{ $token }}";

                // Set proper dimensions
                config.width = "100%";
                config.height = "100%";

                // Event handlers
                config.events = {
                    'onAppReady': onAppReady,
                    'onDocumentReady': onDocumentReady,
                    'onError': onError,
                    'onWarning': onWarning,
                    'onRequestClose': onRequestClose
                };

                console.log('Initializing OnlyOffice with config:', config);

                docEditor = new DocsAPI.DocEditor("onlyoffice-preview", config);
            } catch (error) {
                console.error('Failed to initialize OnlyOffice:', error);
                showError('Failed to load document editor. Please refresh the page.');
            }
        }

        function onAppReady() {
            console.log('OnlyOffice App Ready');
            // Hide loading indicator
            var loading = document.querySelector('.loading');
            if (loading) {
                loading.style.display = 'none';
            }
        }

        function onDocumentReady() {
            console.log('Document Ready');
        }

        function onError(event) {
            console.error('OnlyOffice Error:', event);
            if (event && event.data) {
                showError('Error: ' + (event.data.errorDescription || 'Unknown error occurred'));
            }
        }

        function onWarning(event) {
            console.warn('OnlyOffice Warning:', event);
        }

        function onRequestClose() {
            window.location.href = "{{ route('documents.index') }}";
        }

        function showError(message) {
            var preview = document.getElementById('onlyoffice-preview');
            preview.innerHTML = `
                <div style="text-align: center; padding: 50px; color: #dc3545;">
                    <h3>⚠️ Error</h3>
                    <p>${message}</p>
                    <a href="{{ route('documents.index') }}" class="btn btn-primary" style="margin-top: 20px;">
                        Back to Documents
                    </a>
                </div>
            `;
        }

        function shareDocument() {
            fetch('/documents/{{ $document->id }}/share')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var url = prompt('Share URL (valid for ' + data.expiresIn + '):', data.shareUrl);
                    }
                })
                .catch(error => {
                    console.error('Share error:', error);
                    alert('Failed to generate share link');
                });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (docEditor) {
                // OnlyOffice should auto-adjust
                console.log('Window resized');
            }
        });
    </script>
</body>
</html>
