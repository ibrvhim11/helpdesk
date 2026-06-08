<?php 
 session_start();
   include '../configDb.php';
   if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
   }

   $id = intval ($_GET['id']);

   $stmt = $pdo->prepare("SELECT t.*, u.nom, u.prenom, s.libelle as statut
                           FROM ticket t LEFT JOIN utilisateur u on t.id_user = u.id_user
                           LEFT JOIN statut_ticket s ON t.id_statut = s.id_statut
                           WHERE t.id_ticket = ?");

                           $stmt->execute([$id]);
                           $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

                        if(isset($_POST['add_comment'])) {
                            $commentaire = trim($_POST['commentaire']);
                            $user_id = $_SESSION['user']['id_user'];

                        if(!empty($commentaire)){
                            $stmt= $pdo->prepare(" INSERT INTO  commentaire (id_ticket, id_user, message, date_commentaire)
                                                   VALUES (?,?,?, NOW())");
                                    $stmt->execute([$id, $user_id, $commentaire]);               
                        }

                        header("location: detail.php?id=".$id);
                        exit();
                        }


                        $comments = $pdo->prepare("SELECT c.*, u.nom, u.prenom from commentaire c 
                                                   join utilisateur u on c.id_user = u.id_user
                                                   where c.id_ticket = ? ORDER BY c.date_commentaire DESC");
                        $comments->execute([$id]);
                        $comments = $comments->fetchAll(PDO::FETCH_ASSOC);                         

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Ticket</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
}

/* Conteneur principal */
.main {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Bouton retour */
.back {
    background: #3498db;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.back a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
}

/* Titre */
h2, h3 {
    color: #2c3e50;
}

/* Carte ticket */
.card {
    background: #f9fafc;
    padding: 15px;
    border-radius: 8px;
    border-left: 5px solid #3498db;
    margin-bottom: 20px;
}

.card p {
    margin: 8px 0;
}

/* Commentaires */
.comment {
    background: #ffffff;
    border: 1px solid #e1e1e1;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: 0.2s;
}

.comment:hover {
    transform: scale(1.01);
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

.comment strong {
    color: #2c3e50;
}

.comment small {
    color: #888;
}

/* Formulaire commentaire */
form textarea {
    width: 100%;
    height: 90px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    resize: none;
    margin-top: 10px;
}

form button {
    margin-top: 10px;
    background: #2ecc71;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

form button:hover {
    background: #27ae60;
}

/* Responsive */
@media (max-width: 768px) {
    .main {
        margin: 10px;
        padding: 15px;
    }
}
    </style>
</head>
<body>

<div class="main">
    <div >
        <button class="back"><a href="dashboard.php">Retour</a></button>
    </div>

    <h2>Details du Ticket</h2>

    <div class="card">
        <p><strong>Titre :</strong><?= htmlspecialchars($ticket['titre']) ?></p>
         <p><strong>Description :</strong><?= htmlspecialchars($ticket['description']) ?></p>
          <p><strong>Utilisateur :</strong><?= $ticket['nom']." ".$ticket['prenom'] ?></p>
          <p><strong>Statut :</strong><?= $ticket['statut'] ?></p>
          <p><strong>Date :</strong><?= $ticket['date_creation'] ?></p>
    </div>

    <h3>Commentaires</h3>
      
       <?php if(count($comments) > 0): ?>
         <?php foreach($comments as $c ): ?>
            <div class="comment">
                <p><strong><?= $c['nom']." ".$c['prenom'] ?></strong></p>
                 <p><?= htmlspecialchars($c['message']) ?></p>
                 <small><?= $c['date_commentaire'] ?></small>
            </div>
            <?php endforeach;?>
            <?php else: ?>
                <p>Aucun commentaire</p>
                <?php endif; ?>

     <h3>Ajouter un Commentaire</h3>   
        <form action="" method="post">
            <textarea name="commentaire" required placeholder="Saisir un commentaire..."></textarea><br>
            <button type="submit" name="add_comment">Envoyer</button>

        </form>        
</div>
    
</body>
</html>