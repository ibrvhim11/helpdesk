<?php
session_start();
include '../configDb.php';
include '../audit.php';

$id_ticket = $_GET['id'];

$id_support = $_SESSION['user']['id_user'];

$sql = "UPDATE ticket
        SET id_support = ?
        WHERE id_ticket = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_support, $id_ticket]); 

$hist = $pdo->prepare("INSERT INTO historique_ticket(id_ticket, id_user, action, commentaire) 
                      VALUES (?, ?, ?, ?)");


$hist->execute([$id_ticket, $id_support, 'Affectation', 'Ticket affecter au support n2']);

addAudit(
    $pdo,
    $id_support,
    "Affectation du ticket #$id_ticket",
    "ticket"
);

$_SESSION['toast'] = "Ticket affecté avec succès";

header("Location: dashboard.php");
exit();