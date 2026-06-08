<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Helpdesk</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* un style attrayante pour la page d'accueil de Helpdesk, avec une mise en page centrée, des couleurs apaisantes (blanc et marron) */
            body {
                background-color: #f5f5f5;
                font-family: Arial, sans-serif;
                color: #333;
            }
             body {
    background-image: url('images.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;}
            .container {
                max-width: 600px;
                margin: 50px auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            h1 {
                color: #8b4513;
            }
            .btn {
                display: inline-block;
                margin: 10px;
                padding: 10px 20px;
                background-color: #8b4513;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s ease;
            }
            .btn:hover {
                background-color: #a0522d;
            }

            
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Bienvenue sur Helpdesk</h1>
            <img src="logo.jpg" alt="Logo Helpdesk">
            <p>Votre solution de support technique en ligne

            </p>
            <a href="login.php" class="btn">Se connecter</a>
            <a href="register.php" class="btn">S'inscrire</a>
        </div>
    </body>