<?php
session_start();
include 'configDb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (empty($email) || empty($mot_de_passe)) {
        $error = "Veuillez remplir tous les champs.";
    } else {

        $sql = "SELECT * FROM utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user) {

        if(password_verify($mot_de_passe, $user['mot_de_passe'])) {

            $_SESSION['user'] = [
                'id' => $user['id_user'],
                'nom' => $user['nom'],
                'role' => $user['role']  
            ];
        
            if($user['role'] == 'ADMIN') {
                header("Location: admin/dashboard.php");  
            } elseif($user['role'] == 'SUPPORT_N1' || $user['role'] == 'SUPPORT_N2') {
                 header("Location: support/dashboard.php");
            } elseif($user['role'] == 'UTILISATEUR') {
                    header("Location:dashboard.php");
            } elseif($user['role'] == 'SUPERVISEUR'){
                    header("Location: superviseur/dashboard.php");
            }
                  exit();  

        } else {
            $error = "utilisateur ou mot de passe incorrect.";
        }

        } else {
            $error = "utilisateur non trouvé.";
        }
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* un style attrayante pour la page de connexion de Helpdesk, avec une mise en page centrée, des couleurs apaisantes (blanc et marron) */
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            color: #333;
        }
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
        label {
            display: block;
            margin-bottom: 10px;
            text-align: left;
        }
        input[type="email"],
        input[type="password"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #8b4513;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #a0522d;
        }

        .back{
            background-color: #ecb7a7;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
      <div >
        <button class="back"><a href="index.php">Retour</a></button>
    </div>
    <div class="container">
        <h1>Se connecter</h1>
        <?php if (isset($error)){?>
            <p style = "color:red;"><?php echo $error; ?></p>
        <?php } ?>

        <form method="post">
            <label for="email">Nom d'utilisateur ou E-mail:</label>
            <input type="email" id="email" name="email" required><br><br>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="mot_de_passe" required><br><br>
            <input type="submit" value="Se connecter">
        </form>
    </div>
</body>
</html>