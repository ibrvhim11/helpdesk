<?php
   session_start();
   include '../configDb.php';
   if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
   }

  if($_SESSION['user']['role'] !== 'SUPERVISEUR') {
    echo " Acces refuse";
    exit();
  }

 $user = $_SESSION['user'];

  $nTickets = $pdo->query("SELECT COUNT(*) FROM ticket")->fetchColumn();
  $sql = "SELECT s.libelle, COUNT(*) as total
                         FROM ticket t
                         JOIN statut_ticket s ON t.id_statut = s.id_statut 
                         GROUP BY s.libelle";

       $stats = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

       $nNouveau = $nEncours = $nResolu = $nRejete = $nCloture = $nEnattente = $nEnpause = 0;

        foreach($stats as $row){
            if($row['libelle'] == 'Nouveau') $nNouveau = $row['total'];
             if($row['libelle'] == 'En cours') $nEncours = $row['total'];
              if($row['libelle'] == 'Rejete') $nRejete = $row['total'];
               if($row['libelle'] == 'Resolu') $nResolu = $row['total'];
               if($row['libelle'] == 'Cloture') $nCloture = $row['total'];
               if($row['libelle'] == 'En attente') $nEnattente = $row['total'];
               if($row['libelle'] == 'En pause') $nEnpause = $row['total'];
        } 
        
  $tickets = $pdo->query("SELECT t.id_ticket, t.titre, t.date_creation, u.nom, u.prenom, s.libelle AS statut
                          FROM ticket t 
                          LEFT JOIN utilisateur u ON t.id_user = u.id_user
                          LEFT JOIN statut_ticket s ON t.id_statut = s.id_statut
                          ORDER BY t.date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);

   
$taux = $nTickets > 0 ? round(($nResolu / $nTickets) * 100, 2) : 0;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superviseur Dashboard</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <h2>HELPDESK SUPERVISEUR</h2>
        <ul>
            <li><a href="#" onclick="showSection('dashboard')"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="#" onclick="showSection('tickets')"><i class="fa-solid fa-ticket"></i> Tickets</a></li>
            <li><a href="#" onclick="showSection('stats')"> <i class="fa-solid fa-chart-line"></i>  Statistique</a></li>
            <li><a href="#" onclick="showSection('rapports')"><i class="fa-solid fa-chart-pie"></i> Rapports</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Deconnexion</a></li>
        </ul>
    </div>

 <div class="main">
    <div id="dashboard" class="section active">
            <div class="header">
                 <div class="head">
                   <p>Bienvenue Superviseur  <?= htmlspecialchars($user['nom']) ?> </p>
                 </div>
                <div class="logo">
                   <img src="../logo.jpg" alt="Logo Helpdesk">
               </div>
            </div> 

      <div class="cards">
         <div class="card">
           <h3>Taux de resolution</h3>
           <p><?= $taux ?>%</p>
        </div>
        <div class="card">
           <h3>Tickets</h3>
           <p><?= $nTickets ?></p>
        </div>
        <div class="card">
           <h3>Nouveau</h3>
          <p><?= $nNouveau ?></p>
        </div>
        <div class="card">
          <h3>Rejete</h3>
          <p><?= $nRejete ?></p>
       </div>
       <div class="card">
          <h3>En cours</h3>
          <p><?= $nEncours ?></p>
        </div>
        <div class="card">
          <h3>Resolu</h3>
          <p><?= $nResolu ?></p>
        </div>
        <div class="card">
          <h3>Cloture</h3>
          <p><?= $nCloture ?></p>
        </div>
        <div class="card">
          <h3>En attente</h3>
          <p><?= $nEnattente ?></p>
        </div>
        <div class="card">
           <h3>En pause</h3>
          <p><?= $nEnpause ?></p>
        </div>
     </div>
     <h2>Activites Recent</h2>
         <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Statut</th>
                    <th>Date</th>                    
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($tickets,0,5) as $t): ?>
                    <tr>
                        <td><?= $t['id_ticket'] ?></td>
                        <td><?= htmlspecialchars($t['titre']) ?></td>
                        <td><?= $t['statut'] ?> </td>
                        <td><?= $t['date_creation'] ?></td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
         </table>
   </div>

    <div id="tickets" class="section">
        <div class="header">
               <div class="head">
                <p>Bienvenue Superviseur  <?= htmlspecialchars($user['nom']) ?> </p>
            </div>
           <div class="logo">
             <img src="../logo.jpg" alt="Logo Helpdesk">
           </div>
        </div> 
           <input type="text"  id="searchTicket" placeholder="search un ticket...">

           <h2> Liste des Tickets </h2>
           <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Utilisateur</th>
                    <th>Statut</th>
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
                        <td><?= htmlspecialchars($t['nom']. ' ' . $t['prenom']) ?></td>
                        <td>
                            <span class="badge <?= strtolower(str_replace(' ', '-', $t['statut'])) ?>">
                                <?= $t['statut'] ?>
                            </span>
                        </td>
                        <td><?= $t['date_creation'] ?></td>
                        <td>
                         <a href="detail.php?id=<?= $t['id_ticket'] ?>" class="btn view">Detail</a>
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
                
        </div> 
        
        <div id="stats" class="section">

            <div class="header">

               <div class="head">
                  <p>Bienvenue Superviseur  <?= htmlspecialchars($user['nom']) ?> </p>
              </div>

               <div class="logo">
                  <img src="../logo.jpg" alt="Logo Helpdesk">
               </div>
            </div>   

            <h2> Statistiques des Tickets </h2>

            <div class="chart-container">
                <canvas id="ticketChart"></canvas>
            </div>
        </div>

        <div id="rapports" class="section">

            <div class="header">

               <div class="head">
                <p>Bienvenue Superviseur  <?= htmlspecialchars($user['nom']) ?> </p>
               </div>
              <div class="logo">
                <img src="../logo.jpg" alt="Logo Helpdesk">
               </div>
           </div> 
            <h2> Rapports  et Exports</h2>
            <div class="report-cards">
                <div class="report-card">
                    <h3> Rapport PDF</h3>
                    <form action="export_pdf.php" method="get">
                        <div class="form-group">
                            <label>Date Debut</label>
                            <input type="date" name="date_debut">
                        </div>
                        <div class="form-group">
                            <label>Date Fin</label>
                            <input type="date" name="date_fin">
                        </div>
                        <div class="form-group">
                            <label>ID Ticket</label>
                            <input type="number" name="id_ticket">
                        </div>
                        <button type="submit" class="btn pdf-btn">Export PDF</button>
                    </form>
               </div>

               <div class="report-card">
                   <h3> Rapport PDF</h3>
                   <p>
                     Telecharger les donnees  des tickets dans un fichier excel pour analyse
                    </p>
                    <a href="export_excel.php" class="btn excel-btn">Exporter en Excel</a>
               </div>
            </div>   
               <br> <br>
            <div class="cards">
                <div class="card">
                   <h3> Totals Tickets</h3>
                   <p><?= $nTickets ?></p>
                </div>
                 <div class="card">
                   <h3> Tickets Resolus</h3>
                   <p><?= $nResolu ?></p>
                </div>
                 <div class="card">
                   <h3> Tickets En attente</h3>
                   <p><?= $nEnattente ?></p>
                </div>
                 <div class="card">
                   <h3> Tickets Rejeter</h3>
                   <p><?= $nRejete ?></p>
                </div>
            </div>

        </div>        
       


    </div> <!-- Fin de .main -->
<script>

    function showSection(id) {
        let sections = document.querySelectorAll('.section');

            sections.forEach(sec =>{
               sec.classList.remove("active");

            });

            document.getElementById(id).classList.add('active');
            
     }

    document.getElementById("searchTicket").addEventlistener("keyup", function() {
        let value = this.value.tolowerCase();
        let rows  = document.querySelectorAll(".table tbody tr");

        rows.forEach(row => {
            rows.style.display = row.innerText.tolowerCase().includes(value) ?"" : "none";

        });
    });
</script>
<script>
     const ctx = document.getElementById('ticketChart');
    new Chart(ctx, {
        type: 'bar',

        data: {
            labels: ['Nouveau', 'En cours', 'Resolu', 
                    'Rejete', 'Cloture', 'En attente', 'En pause' 
                ],

        datasets: [{
            label: 'Nombre de tickets',
              data: [<?= $nNouveau ?>, <?= $nEncours ?>, <?= $nResolu ?>, 
                     <?= $nRejete ?>, <?= $nEnattente ?>, <?= $nCloture ?>, <?= $nEnpause ?>
                ],

        backgroundColor: [ '#3498db', '#f39c12', '#2ecc72', '#e74c3c',
                             '#9b59b6', '#1abc9c', '#95a5a6' 
        ],  
        
         borderRadius: 8,
         borderWidth: 1

        }]            
        },
        options: {
            responsive: true,

            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>