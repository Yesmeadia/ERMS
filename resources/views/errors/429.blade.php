<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests | ERMS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #020617; color: #e2e8f0; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; padding: 2rem; max-width: 480px; }
        .code { font-size: 6rem; font-weight: 700; line-height: 1; background: linear-gradient(135deg, #f59e0b, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; font-weight: 600; color: #f1f5f9; margin-bottom: 0.75rem; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem; }
        .btn { display: inline-block; padding: 0.65rem 1.5rem; background: #3b82f6; color: #fff; border-radius: 0.5rem; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: background 0.2s; }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">429</div>
        <h1>Too Many Requests</h1>
        <p>You have sent too many requests in a short period of time. Please wait a moment before trying again.</p>
        <a href="javascript:history.back()" class="btn">← Go Back</a>
    </div>
</body>
</html>
