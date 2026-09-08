<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $policy->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; line-height: 1.5; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        h2 { font-size: 15px; margin: 14px 0 6px; }
        h3 { font-size: 13px; margin: 12px 0 6px; }
        .meta { color: #666; font-size: 10px; margin-bottom: 16px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
        .content { word-wrap: break-word; }
        .content p { margin: 0 0 8px; }
        .content ul, .content ol { margin: 0 0 8px; padding-left: 22px; }
        .content li { margin: 0 0 4px; }
        .content a { color: #0645ad; text-decoration: underline; }
        .content blockquote { margin: 8px 0; padding-left: 12px; border-left: 3px solid #ccc; color: #555; }
        .content b, .content strong { font-weight: bold; }
        .content i, .content em { font-style: italic; }
        .content u { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>{{ $policy->title }}</h1>
    <div class="meta">
        SNTC &nbsp;|&nbsp; Generated {{ $generatedAt ?? now()->format('d-m-Y H:i') }}
    </div>
    @php
        $rawContent = (string) ($policy->content ?? '');
        $hasHtml = $rawContent !== strip_tags($rawContent);
    @endphp
    <div class="content">
        @if($hasHtml)
            {!! $rawContent !!}
        @else
            {!! nl2br(e($rawContent)) !!}
        @endif
    </div>
</body>
</html>
