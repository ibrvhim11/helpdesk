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
                            $user_id = $_SESSION['user']['id'];

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
    <link rel="stylesheet" href="../admin/admin.css">
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