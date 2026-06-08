<?php
 session_start();
   include '../configDb.php';
   if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
   }

  if($_SESSION['user']['role'] !== 'UTILISATEUR') {
    echo " Acces refuse";
    exit();
  }

 $user = $_SESSION['user'];

 $categories = $pdo->query("SELECT * FROM categorie_ticket")->fetchAll(PDO::FETCH_ASSOC);

$modules = $pdo->query("SELECT * FROM module_sifcom")->fetchAll(PDO::FETCH_ASSOC);

$priorites = $pdo->query("SELECT * FROM priorite")->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['create_ticket'])) {

    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $categorie = $_POST['id_categorie'];
    $module = $_POST['id_module'];
    $priorite = $_POST['id_priorite'];

    //Validation simple
    if (empty($titre) || empty($description)) {
        die("Champs obligatoires manquants");
    }

    //récupérer statut "Nouveau" dynamiquement (PRO)
    $stmtStatut = $pdo->prepare("SELECT id_statut FROM statut_ticket WHERE libelle = ?");
$stmtStatut->execute(['Nouveau']);
$id_statut = $stmtStatut->fetchColumn();

if (!$id_statut) {
    die("Erreur : statut 'Nouveau' introuvable");
}

    //récupérer délai priorité
    $stmtPriorite = $pdo->prepare("SELECT delai_resolution FROM priorite WHERE id_priorite = ?");
    $stmtPriorite->execute([$priorite]);
    $prioriteData = $stmtPriorite->fetch(PDO::FETCH_ASSOC);

    if (!$prioriteData) {
        die("Priorité introuvable");
    }

    $heures = (int) $prioriteData['delai_resolution'];

    $date_limite = date('Y-m-d H:i:s', strtotime("+$heures hours"));

    //INSERT ticket
    $sql = "INSERT INTO ticket (
                titre,
                description,
                id_user,
                id_categorie,
                id_module,
                id_priorite,
                id_statut,
                date_limite
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titre,
        $description,
        $user['id_user'],
        $categorie,
        $module,
        $priorite,
        $id_statut,
        $date_limite
    ]);

    $id_ticket = $pdo->lastInsertId();

    //AUDIT LOG (important PRO)
    $pdo->prepare("
        INSERT INTO audit_log(id_user, action, table_concernee)
        VALUES (?, ?, ?)
    ")->execute([
        $user['id_user'],
        "Création ticket #$id_ticket : $titre",
        "ticket"
    ]);

    header("Location: dashboard.php");
    exit();
}




$mesTickets = $pdo->prepare("
    SELECT t.*, 
           s.libelle AS statut,
           p.libelle AS priorite,
           m.nom AS module
    FROM ticket t
    LEFT JOIN statut_ticket s 
        ON t.id_statut = s.id_statut
    LEFT JOIN priorite p 
        ON t.id_priorite = p.id_priorite
    LEFT JOIN module_sifcom m 
        ON t.id_module = m.id_module
    WHERE t.id_user = ?
    ORDER BY t.date_creation DESC
");

$mesTickets->execute([$user['id_user']]);

$mesTickets = $mesTickets->fetchAll(PDO::FETCH_ASSOC);

$notifications = $pdo->prepare("
    SELECT *
    FROM notification
    WHERE id_user = ?
    ORDER BY date_envoi DESC
");

$notifications->execute([$user['id_user']]);
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
$stmtNewNotif = $pdo->prepare("
    SELECT COUNT(*) 
    FROM notification 
    WHERE id_user = ? AND vu = 0
");

$stmtNewNotif->execute([$user['id_user']]);
$nbNewNotif = $stmtNewNotif->fetchColumn();


$stmtTotal = $pdo->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_user = ?
");
$stmtTotal->execute([$user['id_user']]);
$totalTickets = $stmtTotal->fetchColumn();

$stmtEncours = $pdo->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_user = ? 
    AND id_statut = (
        SELECT id_statut FROM statut_ticket WHERE libelle = 'En cours'
    )
");
$stmtEncours->execute([$user['id_user']]);
$ticketsEncours = $stmtEncours->fetchColumn();

$stmtEnattente = $pdo->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_user = ? 
    AND id_statut = (
        SELECT id_statut FROM statut_ticket WHERE libelle = 'En attente'
    )
");
$stmtEnattente->execute([$user['id_user']]);
$ticketsEnattente = $stmtEnattente->fetchColumn();

$stmtResolu = $pdo->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_user = ? 
    AND id_statut = (
        SELECT id_statut FROM statut_ticket WHERE libelle = 'Résolu'
    )
");
$stmtResolu->execute([$user['id_user']]);
$ticketsResolu = $stmtResolu->fetchColumn();


$search = $_GET['q'] ?? '';

if (!empty($search)) {

    $articles = $pdo->prepare("
        SELECT *
        FROM article
        WHERE titre LIKE :q
           OR contenu LIKE :q
           OR mots_cles LIKE :q
        ORDER BY date_creation DESC
    ");

    $articles->execute([
        'q' => "%$search%"
    ]);

    $articles = $articles->fetchAll(PDO::FETCH_ASSOC);

} else {

    $articles = $pdo->query("
        SELECT *
        FROM article
        ORDER BY date_creation DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <style>
        .stats-container {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.stat-card {
    flex: 1;
    background: #fff;
    padding: 20px;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.stat-card h3 {
    font-size: 28px;
    margin-bottom: 5px;
    color: #8b4513;
}

.stat-card p {
    margin: 0;
    color: #666;
}
     </style>
</head>
<body>
    <div class="sidebar">
        <h2>HELPDESK USER</h2>
        <ul>
            <li><a href="javascript:void(0)" onclick="showSection('newticket')"><i class="fa-solid fa-plus"></i>  Nouveau Ticket</a></li>
            <li><a href="#" onclick="showSection('mesticket')"><i class="fa-solid fa-ticket"></i> Mes Tickets</a></li>
            <li><a href="#" onclick="showSection('notif')"><i class="fa-solid fa-bell"></i> Notifications
                <?php if ($nbNewNotif > 0): ?>
            <span class="badge-new"><?= $nbNewNotif ?></span>
        <?php endif; ?>
            </a></li>
            <li><a href="#" onclick="showSection('profil')"><i class="fa-solid fa-user"></i> Profil</a></li>
            <li><a href="#" onclick="showSection('baseC')"><i class="fa-solid fa-book"></i> Base Connaissance</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Deconnexion</a></li>
        </ul>
    </div>
   <div class="main">

        <div id="newticket" class="section active">
            <div class="header">
                <div class="head">
                   <p>Bienvenue Users  <?= htmlspecialchars($user['nom']) ?> </p>
                </div>
                <div class="logo">
                   <img src="../logo.jpg" alt="Logo Helpdesk">
                </div>
            </div> 
            <h2>Créer un Ticket</h2>

<form method="post" class="ticket-form">

    <label>Titre</label>
    <input type="text" name="titre" required>

    <label>Description</label>
    <textarea name="description" rows="5" required></textarea>

    <label>Catégorie</label>
    <select name="id_categorie" required>
        <?php foreach($categories as $c): ?>
            <option value="<?= $c['id_categorie'] ?>">
                <?= htmlspecialchars($c['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Module SIFCOM</label>
    <select name="id_module" required>
        <?php foreach($modules as $m): ?>
            <option value="<?= $m['id_module'] ?>">
                <?= htmlspecialchars($m['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Priorité</label>
    <select name="id_priorite" required>
        <?php foreach($priorites as $p): ?>
            <option value="<?= $p['id_priorite'] ?>">
                <?= htmlspecialchars($p['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <button type="submit" name="create_ticket">
        Créer Ticket
    </button>

</form>
        </div>

        <div id="mesticket" class="section ">
            <div class="header">
                <div class="head">
                   <p>Bienvenue Users  <?= htmlspecialchars($user['nom']) ?> </p>
                </div>
                <div class="logo">
                   <img src="../logo.jpg" alt="Logo Helpdesk">
                </div>
            </div> 
            <h2>Mes Tickets</h2>

<table class="table">

    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Module</th>
            <th>Priorité</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Date Limite</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach($mesTickets as $t): ?>

            <tr>

                <td><?= $t['id_ticket'] ?></td>

                <td><?= htmlspecialchars($t['titre']) ?></td>

                <td><?= htmlspecialchars($t['module']) ?></td>

                <td><?= htmlspecialchars($t['priorite']) ?></td>

                <td>
                    <span class="badge">
                        <?= htmlspecialchars($t['statut']) ?>
                    </span>
                </td>

                <td><?= $t['date_creation'] ?></td>

                <td><?= $t['date_limite'] ?></td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>
        </div>

       <div id="notif" class="section">

    <div class="header">
        <div class="head">
            <p>Notifications de <?= htmlspecialchars($user['nom']) ?></p>
        </div>
        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>
    </div>

    <h2>Mes Notifications</h2>

    <div class="notif-list">

        <?php if (empty($notifications)): ?>
            <p>Aucune notification pour le moment.</p>
        <?php else: ?>

            <?php foreach ($notifications as $n): ?>

                <div class="notif-card">

                    <p>
                        🔔 <?= htmlspecialchars($n['message']) ?>
                    </p>

                    <p>
                        Type :
                        <span class="badge">
                            <?= htmlspecialchars($n['type']) ?>
                        </span>
                    </p>

                    <small>
                        🕒 <?= $n['date_envoi'] ?>
                    </small>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</div>

<div id="profil" class="section">

    <div class="header">
        <div class="head">
            <p>Mon Profil - <?= htmlspecialchars($user['nom']) ?></p>
        </div>
        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>
    </div>

    <h2>Informations personnelles</h2>

    <div class="profile-card">

        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>

        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>

        <p><strong>ID utilisateur :</strong> <?= htmlspecialchars($user['id_user']) ?></p>

    </div>

     <h2>Statistiques utilisateur</h2>

    <div class="stats-container">

        <div class="stat-card">
            <h3><?= $totalTickets ?></h3>
            <p>Total tickets</p>
        </div>

        <div class="stat-card">
            <h3><?= $ticketsEncours ?></h3>
            <p>Tickets en cours</p>
        </div>

        <div class="stat-card">
            <h3><?= $ticketsEnattente ?></h3>
            <p>Tickets en attente</p>
        </div>


        <div class="stat-card">
            <h3><?= $ticketsResolu ?></h3>
            <p>Tickets résolus</p>
        </div>

    </div>

</div>


<div id="baseC" class="section">

    <div class="header">
        <div class="head">
            <p>Bienvenu User <?= htmlspecialchars($user['nom']) ?></p>
        </div>
        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>
    </div>

    <h2>Base de Connaissances</h2>
         <form method="GET" class="search-box">
           <input type="text" name="q" placeholder="Rechercher un article..." value="<?= $_GET['q'] ?? '' ?>">
           <button type="submit">🔍</button>
        </form><br><br>

    <div class="articles">

        <?php foreach ($articles as $a): ?>
            <div class="card-article">

                <h3><?= htmlspecialchars($a['titre']) ?></h3>

                <p class="meta">
                    <?= htmlspecialchars($a['mots_cles']) ?> •
                    <?= $a['date_creation'] ?>
                </p>

                <p class="preview" id="preview-<?= $a['id_article'] ?>">
                    <?= nl2br(htmlspecialchars(substr($a['contenu'], 0, 120))) ?>...
                </p>

                <p class="full" id="full-<?= $a['id_article'] ?>" style="display:none;">
                    <?= nl2br(htmlspecialchars($a['contenu'])) ?>
                </p>

                <button class="btn-plus"
                        onclick="toggleArticle(<?= $a['id_article'] ?>)">
                    +
                </button>

            </div>
        <?php endforeach; ?>

    </div>
</div>


    </div>






<script>

function showSection(id){

    let sections = document.querySelectorAll('.section');

    sections.forEach(sec => {
        sec.classList.remove('active');
    });

    document.getElementById(id).classList.add('active');
}

function toggleArticle(id) {

    let preview = document.getElementById("preview-" + id);
    let full = document.getElementById("full-" + id);

    if (full.style.display === "none") {
        full.style.display = "block";
        preview.style.display = "none";
    } else {
        full.style.display = "none";
        preview.style.display = "block";
    }
}
</script>

</body>
</html>