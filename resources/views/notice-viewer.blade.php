<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #1a1d21; color: #e4e6eb; }
        .viewer-toolbar {
            padding: 12px 16px;
            background: #25282c;
            border-bottom: 1px solid #3a3d42;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .viewer-toolbar h1 { margin: 0; font-size: 1.1rem; font-weight: 600; }
        .viewer-toolbar a {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        .viewer-toolbar a:hover { background: #0b5ed7; }
        .viewer-frame-wrap {
            position: absolute;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #2b2e33;
            overflow: auto;
        }
        .viewer-frame-wrap iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        #docx-container {
            padding: 24px;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            color: #222;
            min-height: 100%;
        }
        #docx-container.docx-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e4e6eb;
            font-size: 15px;
        }
        #docx-container.docx-error {
            color: #f87171;
        }
    </style>
    @if(!$isPdf && in_array($extension ?? '', ['docx', 'doc']))
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/docx-preview@0.3.0/dist/docx-preview.css">
    @endif
</head>
<body>
    <div class="viewer-toolbar">
        <h1>{{ $title }}</h1>
        <a href="{{ $downloadUrl }}" download>Download</a>
    </div>
    <div class="viewer-frame-wrap">
        @if($isPdf ?? true)
        <iframe src="{{ $viewUrl }}" title="{{ $title }}"></iframe>
        @else
        <div id="docx-container" class="docx-loading">Loading document…</div>
        @endif
    </div>
    @if(!$isPdf && in_array($extension ?? '', ['docx', 'doc']))
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.0/dist/docx-preview.min.js"></script>
    <script>
        (function() {
            var container = document.getElementById('docx-container');
            if (!container) return;
            var viewUrl = {!! json_encode($viewUrl) !!};
            var downloadUrl = {!! json_encode($downloadUrl) !!};
            fetch(viewUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,*/*' } })
                .then(function(r) {
                    if (!r.ok) throw new Error('Could not load document');
                    var ct = (r.headers.get('content-type') || '').toLowerCase();
                    if (ct.indexOf('html') !== -1) throw new Error('Server returned HTML instead of document');
                    return r.blob();
                })
                .then(function(blob) {
                    container.classList.remove('docx-loading');
                    if (typeof docx === 'undefined' || !docx.renderAsync) {
                        throw new Error('Viewer not ready');
                    }
                    return docx.renderAsync(blob, container);
                })
                .then(function() {
                    container.style.background = '#fff';
                })
                .catch(function(err) {
                    container.classList.remove('docx-loading');
                    container.classList.add('docx-error');
                    container.innerHTML = '';
                    var msg = document.createElement('p');
                    msg.style.margin = '0 0 12px 0';
                    msg.textContent = 'Preview is not available for this file. You can open it in a new tab or download it.';
                    container.appendChild(msg);
                    var openLink = document.createElement('a');
                    openLink.href = downloadUrl;
                    openLink.target = '_blank';
                    openLink.rel = 'noopener';
                    openLink.style.cssText = 'color:#60a5fa; margin-right:12px;';
                    openLink.textContent = 'Open in new tab';
                    container.appendChild(openLink);
                    var dlLink = document.createElement('a');
                    dlLink.href = downloadUrl;
                    dlLink.download = '';
                    dlLink.style.cssText = 'color:#60a5fa;';
                    dlLink.textContent = 'Download';
                    container.appendChild(dlLink);
                });
        })();
    </script>
    @endif
</body>
</html>
