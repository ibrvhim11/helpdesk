<?php
   session_start();
   include '../configDb.php';
   if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
   }

  if(
     $_SESSION['user']['role'] !== 'SUPPORT_N1' &&
     $_SESSION['user']['role'] !=='SUPPORT_N2'
    ){
      echo "Acces refuse";
      exit();
    }

 $user = $_SESSION['user'];

$nTickets = $pdo->query("SELECT COUNT(*) FROM ticket")->fetchColumn();

$nEncours = $pdo->query("
SELECT COUNT(*) 
FROM ticket t
JOIN statut_ticket s ON t.id_statut = s.id_statut
WHERE s.libelle = 'En cours'
")->fetchColumn();

$nResolu = $pdo->query("
SELECT COUNT(*) 
FROM ticket t
JOIN statut_ticket s ON t.id_statut = s.id_statut
WHERE s.libelle = 'Resolu'
")->fetchColumn();

$nAttente = $pdo->query("
SELECT COUNT(*) 
FROM ticket t
JOIN statut_ticket s ON t.id_statut = s.id_statut
WHERE s.libelle = 'En attente'
")->fetchColumn();

$sql = "SELECT 
            t.id_ticket,
            t.titre,
            t.description,
            t.date_creation,

            u.nom,
            u.prenom,

            s.libelle AS statut,

            p.libelle AS priorite,

            c.libelle AS categorie,

            m.nom AS module

        FROM ticket t

        LEFT JOIN utilisateur u 
            ON t.id_user = u.id_user

        LEFT JOIN statut_ticket s 
            ON t.id_statut = s.id_statut

        LEFT JOIN priorite p
            ON t.id_priorite = p.id_priorite

        LEFT JOIN categorie_ticket c
            ON t.id_categorie = c.id_categorie

        LEFT JOIN module_sifcom m
            ON t.id_module = m.id_module

        WHERE 1=1";

$params = [];

if(!empty($_GET['statut'])){
    $sql .= " AND s.libelle = ?";
    $params[] = $_GET['statut'];
}

if(!empty($_GET['priorite'])){
    $sql .= " AND p.libelle = ?";
    $params[] = $_GET['priorite'];
}



$limit = 5;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;



$sqlCount = "SELECT COUNT(*) FROM ticket";

$totalTickets = $pdo->query($sqlCount)->fetchColumn();

$totalPages = ceil($totalTickets / $limit);


$sql .= " ORDER BY t.date_creation DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard</title>
     <link rel="stylesheet" href="../admin/admin.css">
</head>
<body>
    <div class="sidebar">
        <h2>HELPDESK SUPPORT</h2>
        <ul>
            <li><a href="#" onclick="showSection('dashboard')">Dashboard</a></li>
            <li><a href="#" onclick="showSection('tickets')">Mes Tickets</a></li>
            <li><a href="#" onclick="showSection('stats')">Statistique</a></li>
            <li><a href="../logout.php">Deconnexion</a></li>
        </ul>
    </div>

    <div class="main">
          
      <div id="dashboard" class="section active">

    <div class="header">

        <div class="head">
            <p>
                Bienvenue Support
                <?= htmlspecialchars($user['nom']) ?>
            </p>
        </div>

        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>

    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Tickets</h3>
            <p><?= $nTickets ?></p>
        </div>

        <div class="card">
            <h3>En cours</h3>
            <p><?= $nEncours ?></p>
        </div>

        <div class="card">
            <h3>Resolus</h3>
            <p><?= $nResolu ?></p>
        </div>

        <div class="card">
            <h3>En attente</h3>
            <p><?= $nAttente ?></p>
        </div>

    </div>

</div>



<div id="tickets" class="section">

    <div class="header">

        <div class="head">
            <p>Gestion des Tickets</p>
        </div>

        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>

    </div>

    <h2>Liste des Tickets</h2>

    <div class="filter-box">

<form method="GET">

<select name="statut">
    <option value="">Statut</option>
    <option value="En cours">En cours</option>
    <option value="Resolu">Resolu</option>
    <option value="En attente">En attente</option>
</select>

<select name="priorite">
    <option value="">Priorite</option>
    <option value="Critique">Critique</option>
    <option value="Haute">Haute</option>
    <option value="Moyenne">Moyenne</option>
    <option value="Basse">Basse</option>
</select>

<button type="submit" class="btn view">
    Filtrer
</button>

</form>

</div>

    <table class="table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Utilisateur</th>
                <th>Statut</th>
                <th>Priorite</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        <?php if(count($tickets) > 0): ?>

            <?php foreach($tickets as $t): ?>

            <tr>

                <td><?= $t['id_ticket'] ?></td>

                <td><?= htmlspecialchars($t['titre']) ?></td>

                <td>
                    <?= htmlspecialchars($t['nom'].' '.$t['prenom']) ?>
                </td>

                <td>
                    <span class="badge <?= strtolower(str_replace(' ','-',$t['statut'])) ?>">
                        <?= $t['statut'] ?>
                    </span>
                </td>
                 <td>
                    <span class="priority <?= strtolower($t['priorite']) ?>">
                      <?= $t['priorite'] ?>
                   </span>
                </td>

             

                <td><?= $t['date_creation'] ?></td>

                <td>
                    <a href="detail.php?id=<?= $t['id_ticket'] ?>" class="btn view">
                        Detail
                    </a>
                    <a href="changer_statut.php?id=<?= $t['id_ticket'] ?>&statut=Resolu" class="btn view">
                        Resolu
                    </a>

                    <a href="changer_statut.php?id=<?= $t['id_ticket'] ?>&statut=En attente" class="btn view">
                       Attente
                    </a>
                    <a href="affecter_ticket.php?id=<?= $t['id_ticket'] ?>" class="btn view">
                      Affecter
                    </a>
                </td>

            </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="6">Aucun ticket trouve</td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

    <div class="pagination">

<?php for($i = 1; $i <= $totalPages; $i++): ?>

<a href="?page=<?= $i ?>
&statut=<?= $_GET['statut'] ?? '' ?>
&priorite=<?= $_GET['priorite'] ?? '' ?>"
class="<?= ($page == $i) ? 'active-page' : '' ?>">

<?= $i ?>

</a>

<?php endfor; ?>

</div>

</div>


<div id="stats" class="section">

    <div class="header">

        <div class="head">
            <p>Statistiques Support</p>
        </div>

        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>

    </div>

    <div class="cards">

        <div class="card">
            <h3>Tickets Resolus</h3>
            <p><?= $nResolu ?></p>
        </div>

        <div class="card">
            <h3>Tickets En attente</h3>
            <p><?= $nAttente ?></p>
        </div>

        <div class="card">
            <h3>Tickets En cours</h3>
            <p><?= $nEncours ?></p>
        </div>

    </div>

</div>


    </div>   
    <script>
        function showSection(id) {
            let sections = document.querySelectorAll('.section');

            sections.forEach(sec =>{
               sec.classList.remove("active");

            });

            document.getElementById(id).classList.add('active');
            
     }
    </script>       
</body>
</html>