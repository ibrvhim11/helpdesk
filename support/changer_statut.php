<?php

session_start();
include '../configDb.php';
include '../audit.php';

if(!isset($_SESSION['user'])){
    header("location: ../login.php");
    exit();
}

$id_ticket = $_GET['id'];
$statut = $_GET['statut'];


//récupérer id_statut
$stmt = $pdo->prepare("
    SELECT id_statut 
    FROM statut_ticket
    WHERE libelle = ?
");

$stmt->execute([$statut]);
$id_statut = $stmt->fetchColumn();

if(!$id_statut){
    die("Statut introuvable");
}


//update ticket
$update = $pdo->prepare("
    UPDATE ticket
    SET id_statut = ?
    WHERE id_ticket = ?
");

$update->execute([$id_statut, $id_ticket]);


//récupérer utilisateur propriétaire du ticket
$stmtUser = $pdo->prepare("
    SELECT id_user, titre 
    FROM ticket 
    WHERE id_ticket = ?
");

$stmtUser->execute([$id_ticket]);
$ticket = $stmtUser->fetch(PDO::FETCH_ASSOC);

$id_user_ticket = $ticket['id_user'];
$titre = $ticket['titre'];


//historique
$stmtHist = $pdo->prepare("
    INSERT INTO historique_ticket
    (id_ticket, id_user, action, commentaire)
    VALUES (?, ?, ?, ?)
");

$stmtHist->execute([
    $id_ticket,
    $_SESSION['user']['id_user'],
    'Changement statut',
    'Ticket passe en '.$statut
]);

// NOTIFICATION
$stmtNotif = $pdo->prepare("
    INSERT INTO notification (message, type, id_user)
    VALUES (?, ?, ?)
");

$stmtNotif->execute([
    "Votre ticket #$id_ticket ($titre) est maintenant : $statut",
    "statut",
    $id_user_ticket
]);

// audit
addAudit(
    $pdo,
    $id_user,
    "Changement statut ticket #$id_ticket vers $statut",
    "ticket"
);

header("location: dashboard.php");
exit();
?>