<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $policy->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; line-height: 1.5; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { color: #666; font-size: 10px; margin-bottom: 16px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
        .content { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>{{ $policy->title }}</h1>
    <div class="meta">
        SNTC &nbsp;|&nbsp; Generated {{ $generatedAt ?? now()->format('d-m-Y H:i') }}
    </div>
    <div class="content">{!! nl2br(e($policy->content)) !!}</div>
</body>
</html>
