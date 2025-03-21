<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código de Recuperação de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #0066cc;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
        }
        .token {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            text-align: center;
            margin: 20px 0;
            color: #0066cc;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Digital Bank - Recuperação de Senha</h2>
        </div>
        <div class="content">
            <p>Você solicitou a recuperação de senha para sua conta no Digital Bank.</p>

            <p>Use o código abaixo para redefinir sua senha. Este código é válido por {{ $expiresIn }} minutos.</p>

            <div class="token">{{ $token }}</div>

            <p>Se você não solicitou a recuperação de senha, ignore este e-mail.</p>

            <p>Atenciosamente,<br>Equipe Digital Bank</p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático. Por favor, não responda.</p>
            <p>&copy; {{ date('Y') }} Digital Bank. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
