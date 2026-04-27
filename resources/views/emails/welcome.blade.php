<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongi etorri Nova Bitesera</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #6366F1;
            color: #ffffff;
            padding: 28px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 0.15em;
        }
        .content {
            padding: 28px 20px;
            color: #333333;
        }
        .content h2 {
            color: #6366F1;
            margin-top: 0;
        }
        .content p {
            line-height: 1.6;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #6366F1;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #4F46E5;
        }
        .footer {
            background-color: #ffffff;
            padding: 16px 20px 28px;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
    <div class="container">
        <div class="header">
            <h1>NOVA BITES</h1>
        </div>
        <div class="content">
            <h2>Kaixo {{ $user->name }}!</h2>
            <p>Ongi etorri <strong>Nova Bites</strong>era, non gastronomia onak eta zerbitzu zainduak bat egiten duten.</p>
            <p>Zure kontua arrakastaz sortu da eta gure funtzionalitate guztiez goza dezakezu:</p>
            <ul>
                <li>Arakatu gure menu esklusiboa</li>
                <li>Ezagutu gure filosofia sukaldaria</li>
                <li>Aurkitu gure jatetxeak</li>
                <li>Egin erreserbak erraztasunez</li>
            </ul>
            <p>Pozik gaude zu gurekin izateaz.</p>
            <a href="{{ $homeUrl }}" class="button">Bisitatu Nova Bites</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Nova Bites. Eskubide guztiak erreserbatuta.</p>
            <p>Gozatu esperientziaz.</p>
        </div>
    </div>
</body>
</html>
