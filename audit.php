<?php

function addAudit($pdo, $id_user, $action, $table)
{
    $stmt = $pdo->prepare("
        INSERT INTO audit_log (id_user, action, table_concernee, date_action)
        VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([$id_user, $action, $table]);
}

var_dump($_SESSION['user']);
exit;

?>