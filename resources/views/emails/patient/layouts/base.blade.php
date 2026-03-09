<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Chenaker Smile Maker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #25703e; padding: 24px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 0.5px; }
        .content { padding: 32px; color: #333333; line-height: 1.6; }
        .content h2 { color: #25703e; margin-top: 0; }
        .btn { display: inline-block; padding: 14px 32px; background-color: #25703e; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
        p { margin: 0 0 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🦷 Chenaker Smile Maker</h1>
        </div>
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            © {{ date('Y') }} Chenaker Smile Maker. All rights reserved.
        </div>
    </div>
</body>
</html>
