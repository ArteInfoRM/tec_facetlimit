<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{l s='Too many requests' mod='tec_facetlimit'}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .box {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .icon {
            font-size: 56px;
            line-height: 1;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        p {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        a {
            display: inline-block;
            padding: 11px 28px;
            background: #25b9d7;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 500;
            transition: background .15s;
        }
        a:hover { background: #1da8c4; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⏳</div>
        <h1>{l s='Too many requests' mod='tec_facetlimit'}</h1>
        <p>{l s='You have sent too many filter requests in a short time. Please wait a moment before continuing your navigation.' mod='tec_facetlimit'}</p>
        <a href="{$blocked_back_url|escape:'html':'UTF-8'}">
            ← {l s='Back' mod='tec_facetlimit'}
        </a>
    </div>
</body>
</html>
