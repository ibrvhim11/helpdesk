<?php
   session_start();
include 'configDb.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim ($_POST['nom']);
    $prenom = trim ($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $role = $_POST['role'];
    $id_commune = $_POST['id_commune'];

   if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($role) || empty($id_commune)) {
     $error = "Tous les champs sont obligatoires";
   } else {
   
   $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = :email");
   $check->execute(['email' => $email]);
   
   if ($check->rowCount() > 0) {
    $error = " Email deja utiliser";
   } else {
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

   $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, id_commune, date_creation)
           VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :id_commune, NOW())";

           $stmt = $pdo->prepare($sql);
           $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $hash,
            'role' => $role,
            'id_commune' => $id_commune
           ]);
          
           header("Location: login.php");
            exit();
       }    

    }

}
    $communes = $pdo->query("SELECT * FROM commune")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
     <style>
        /* un style attrayante pour la page de connexion de Helpdesk, avec une mise en page centrée, des couleurs apaisantes (blanc et marron) */
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
    background-attachment: fixed;
}
        .back{
            background-color: #ecb7a7;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
        input[type="text"],
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
    </style>
</head>
<body>
    <div>
        <button class="back"><a href="index.php"> Retour</a></button>
    </div>
    <div class="container">
        <h1>Creer un Compte</h1>
         <?php if (isset($error)){?>
            <p style = "color:red;"><?php echo $error; ?></p>
        <?php } ?>
        <form method="post">
            <label for="text">Nom:</label>
            <input type="text" name="nom" placeholder ="saisir vorte nom">
            <label for="text">Prenom:</label>
            <input type="text" name="prenom" placeholder="saisir votre prenom">
            <label for="email">E-mail:</label>
            <input type="email"  name="email" placeholder="saisir votre email" ><br><br>
            <label for="password">Mot de passe:</label>
            <input type="password"  name="mot_de_passe" placeholder="saisir votre mot de passe"><br><br>
             
            <select name="id_commune">
                <option value="">choisir votre commune </option>
                 <?php foreach ($communes as $commune) { ?>
                  <option value ="<?= $commune['id_commune'] ?>">
                    <?= $commune['nom'] ?> (<?= $commune['region'] ?>)
                </option>  
                <?php } ?>  
            </select><br><br>

            <select name="role">
                <option value="">choisir un role </option>
                <option value="UTILISATEUR">Utilisateur</option>
                <!-- <option value="SUPPORT_N1">Support N1</option>
                <option value="SUPPORT_N2">Support N2</option>
                <option value="SUPERVISEUR">Superviseur</option>
                <option value="ADMIN">Admin</option> -->
            </select><br><br>
            
            <input type ="submit" value = "S'inscrire"> 
        </form>

    </div>
    
</body>
</html>