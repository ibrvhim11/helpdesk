<?php

include '../configDb.php';

$id_ticket = $_GET['id'];
$id_support = $_SESSION['user']['id'];

$sql = "UPDATE ticket
        SET id_support = ?
        WHERE id_ticket = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_support, $id_ticket]);

header("Location: dashboard.php");
exit();