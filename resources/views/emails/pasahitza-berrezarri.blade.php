<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasahitza berrezarri</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #6366F1;
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 0.15em;
        }
        .content {
            padding: 40px 30px;
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
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NOVA BITES</h1>
        </div>
        <div class="content">
            <h2>Pasahitza berrezarri</h2>
            <p>Kaixo,</p>
            <p>Mezu hau jaso duzu zure konturako pasahitza berrezartzeko eskaera egin delako.</p>
            
            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">Berrezarri pasahitza</a>
            </div>

            <p>Esteka hau 60 minututan iraungiko da.</p>
            
            <p>Ez baduzu zuk eskatu, ez duzu ezer egin behar.</p>
        </div>
        <div class="footer">
            <p>Argi Kalea 12, 20240 Ordizia, Gipuzkoa</p>
            <p>info@novabites.com</p>
            <p>&copy; {{ date('Y') }} Nova Bites. Eskubide guztiak erreserbatuta.</p>
        </div>
    </div>
</body>
</html>
