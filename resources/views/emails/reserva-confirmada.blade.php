<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreserba baieztatuta</title>
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
        .info-box {
            background-color: #f9f9f9;
            border-left: 4px solid #6366F1;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .info-box li {
            margin-bottom: 10px;
            font-size: 1.05rem;
        }
        .info-box li:last-child {
            margin-bottom: 0;
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
            <h2>Erreserba baieztatuta!</h2>
            <p>Kaixo <strong>{{ $erreserba->bezero_izena }}</strong>,</p>
            <p>Nova Bitesen zure erreserba ondo erregistratu da. Hona hemen xehetasunak:</p>
            
            <div class="info-box">
                <ul>
                    <li><strong>Data:</strong> {{ $erreserba->eguna_ordua->format('d/m/Y') }}</li>
                    <li><strong>Ordua:</strong> {{ $erreserba->eguna_ordua->format('H:i') }} - {{ $erreserba->eguna_ordua->copy()->addHours(2)->format('H:i') }}</li>
                    <li><strong>Zerbitzua:</strong> {{ $erreserba->eguna_ordua->hour < 15 ? 'Bazkaria' : 'Afaria' }}</li>
                    <li><strong>Mahaia:</strong> Mahaia {{ $erreserba->mahai->zenbakia ?? $erreserba->mahai->id }}</li>
                    <li><strong>Pertsonak:</strong> {{ (int) $erreserba->pertsona_kopurua }}</li>
                    <li><strong>Helbidea:</strong> Argi Kalea 12, 20240 Ordizia, Gipuzkoa</li>
                </ul>
            </div>

            <p>Gomendatzen dizugu ordua baino minutu batzuk lehenago iristea. Erreserba ezeztatu behar baduzu, egin dezakezu webguneko zure erreserben ataletik.</p>
            <p>Saioa hasi gabe bazaude, botoiak saioa hasteko orrira eramango zaitu eta ondoren zure erreserbetara itzuliko zaitu.</p>
            
            <p>Zure zain gaituzu!</p>
            
            <a href="{{ $reservasUrl }}" class="button">Ikusi nire erreserbak</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Nova Bites. Eskubide guztiak erreserbatuta.</p>
            <p>Gozatu esperientziaz.</p>
        </div>
    </div>
</body>
</html>
