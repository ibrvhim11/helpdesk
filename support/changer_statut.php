<?php

session_start();
include '../configDb.php';

if(!isset($_SESSION['user'])){
    header("location: ../login.php");
    exit();
}

$id = $_GET['id'];
$statut = $_GET['statut'];


$stmt = $pdo->prepare("
SELECT id_statut 
FROM statut_ticket
WHERE libelle = ?
");

$stmt->execute([$statut]);

$id_statut = $stmt->fetchColumn();



$update = $pdo->prepare("
UPDATE ticket
SET id_statut = ?
WHERE id_ticket = ?
");

$update->execute([$id_statut, $id]);

$sqlHist = "INSERT INTO historique_ticket
            (id_ticket, id_user, action, commentaire)
            VALUES (?, ?, ?, ?)";

$stmtHist = $pdo->prepare($sqlHist);

$stmtHist->execute([
    $id_ticket,
    $_SESSION['user']['id'],
    'Changement statut',
    'Ticket passe en '.$statut
]);

header("location: dashboard.php");
exit();
?>