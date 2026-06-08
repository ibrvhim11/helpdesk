<?php

   session_start();
   include '../configDb.php';
   if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
   }

  if($_SESSION['user']['role'] !== 'ADMIN') {
    echo " Acces refuse";
    exit();
  }

 $user = $_SESSION['user'];

 $users = $pdo->query("SELECT * FROM utilisateur ORDER BY id_user")->fetchAll(PDO::FETCH_ASSOC);

 $nUsers = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
 $nTickets = $pdo->query("SELECT COUNT(*) FROM ticket")->fetchColumn();

 $sql = "SELECT s.libelle, COUNT(*) as total
                         FROM ticket t
                         JOIN statut_ticket s ON t.id_statut = s.id_statut 
                         GROUP BY s.libelle";

       $stats = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

       $nNouveau = $nEncours = $nResolu = $nRejete = $nCloture= 0;

        foreach($stats as $row){
            if($row['libelle'] == 'Nouveau') $nNouveau = $row['total'];
             if($row['libelle'] == 'En cours') $nEncours = $row['total'];
              if($row['libelle'] == 'Rejete') $nRejete = $row['total'];
               if($row['libelle'] == 'Resolu') $nResolu = $row['total'];
               if($row['libelle'] == 'Cloture') $nCloture = $row['total'];
        }                  


        if(isset($_POST['update_user'])){
            $id = $_POST['id_user'];
             $nom = $_POST['nom'];
              $prenom = $_POST['prenom'];
               $email = $_POST['email'];
                $role = $_POST['role'];

                $sql = "UPDATE utilisateur 
                        set nom = :nom, prenom = :prenom, email = :email, role = :role
                        WHERE id_user = :id";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            'nom' => $nom,
                            'prenom' => $prenom,
                            'email' => $email,
                            'role' => $role,
                            'id' =>  $id
                        ]);

                        
                        $pdo->prepare("INSERT INTO audit_log(id_user, action, table_concernee)
                         VALUES (?,?,?)")->execute([
                             $_SESSION['user']['id_user'],
                             "Modification utilsateur ID $id","utilisateur"]);


                             header("location: dashboard.php");
                             exit();
           }

        
        if(isset($_GET['supp_id'])){
            $id = intval($_GET['supp_id']);

            if($id == $_SESSION['user']['id_user']){
                die("impossible de supprimer votre propre compt");

            }

            $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id_user = :id");
            $stmt->execute(['id' => $id]);

            $pdo->prepare("INSERT INTO audit_log(id_user, action, table_concernee)
                         VALUES (?,?,?)")->execute([
                            $_SESSION['user']['id_user'],
                            "Suppression utilsateur ID $id","utilisateur"]);


            header("location: dashboard.php");
            exit();
        }

         


        $statuts = $pdo->query("SELECT * FROM statut_ticket")->fetchAll();
        $priorites = $pdo->query("SELECT * FROM priorite")->fetchAll();
        $modules = $pdo->query("SELECT * FROM module_sifcom")->fetchAll();
        $categories = $pdo->query("SELECT * FROM categorie_ticket")->fetchAll();
        
        if(isset($_POST['add_statut'])) {
            $libelle = trim($_POST['new_statut']);
            if(!empty($libelle)){
                $stmt = $pdo->prepare("INSERT INTO statut_ticket (libelle) values(?)");
                $stmt->execute([$libelle]);
            }
            header("location: dashboard.php");
            exit();
        }

         if(isset($_GET['supp_statut'])) {
            $id = intval($_GET['supp_statut']);
           
                $pdo->prepare("DELETE FROM statut_ticket WHERE id_statut=?")
                ->execute([$id]);
            
            header("location: dashboard.php");
            exit();
        }


        //prioriter
        if(isset($_POST['add_priorite'])) {
            $libelle = trim($_POST['new_priorite']);
            if(!empty($libelle)){
                $stmt = $pdo->prepare("INSERT INTO priorite (libelle) values(?)");
                $stmt->execute([$libelle]);
            }
            header("location: dashboard.php");
            exit();
        }

         if(isset($_GET['supp_priorite'])) {
            $id = intval($_GET['supp_priorite']);
           
                $pdo->prepare("DELETE FROM priorite WHERE id_priorite=?")
                ->execute([$id]);
            
            header("location: dashboard.php");
            exit();
        }

        // module sifcom

        if(isset($_POST['add_module'])) {
            $nom = trim($_POST['new_module']);
            if(!empty($nom)){
                $stmt = $pdo->prepare("INSERT INTO module_sifcom (nom) values(?)");
                $stmt->execute([$nom]);
            }
            header("location: dashboard.php");
            exit();
        }

         if(isset($_GET['supp_module'])) {
            $id = intval($_GET['supp_module']);
           
                $pdo->prepare("DELETE FROM module_sifcom WHERE id_module=?")
                ->execute([$id]);
            
            header("location: dashboard.php");
            exit();
        }

        // categories tickets
        if(isset($_POST['add_categorie'])) {
            $libelle = trim($_POST['new_categorie']);
            if(!empty($libelle)){
                $stmt = $pdo->prepare("INSERT INTO categorie_ticket (libelle) values(?)");
                $stmt->execute([$libelle]);
            }
            header("location: dashboard.php");
            exit();
        }

         if(isset($_GET['supp_categorie'])) {
            $id = intval($_GET['supp_categorie']);
           
                $pdo->prepare("DELETE FROM categorie_ticket WHERE id_categorie=?")
                ->execute([$id]);
            
            header("location: dashboard.php");
            exit();
        }

    $audit = $pdo->query("SELECT a.*, u.nom, u.prenom FROM audit_log a
                          LEFT JOIN utilisateur u ON a.id_user = u.id_user
                          ORDER BY a.date_action DESC")->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
    SELECT 
        h.id_historique,
        h.action,
        h.commentaire,
        h.date_action,
        u.nom,
        u.prenom,
        t.titre
    FROM historique_ticket h
    LEFT JOIN utilisateur u ON h.id_user = u.id_user
    LEFT JOIN ticket t ON h.id_ticket = t.id_ticket
    ORDER BY h.date_action DESC
");

$stmt->execute();
$historiques = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['add_article'])) {

    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);
    $mots_cles = trim($_POST['mots_cles']);

    if (!empty($titre) && !empty($contenu)) {

        $sql = "INSERT INTO article
                (titre, contenu, mots_cles, date_creation)
                VALUES (?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $titre,
            $contenu,
            $mots_cles
        ]);

        $_SESSION['toast'] = "Article ajouté avec succès";
    }

    header("Location: dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <h2>HELPDESK ADMIN</h2>
        <ul>
            <li><a href="javascript:void(0)" onclick="showSection('dashboard')"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="#" onclick="showSection('users')"><i class="fa-solid fa-users"></i> Utilsateurs</a></li>
            <li><a href="#" onclick="showSection('audit')"><i class="fa-solid fa-clipboard-list"></i>  Audit</a></li>
            <li><a href="#" onclick="showSection('histo')"><i class="fa-solid fa-history"></i>  Historique</a></li>
            <li><a href="#" onclick="showSection('baseC')"><i class="fa-solid fa-book"></i> Base Connaissance</a></li>
            <li><a href="#" onclick="showSection('param')"><i class="fa-solid fa-gear"></i>  Parametres</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Deconnexion</a></li>
        </ul>
    </div>




<div class="main">

      <div id="dashboard" class="section active">
       <div class="header">
       <div class=head>
      
         <p>Bienvenue Admin  <?= htmlspecialchars($user['nom']) ?> </p>
       </div>
     <div class="logo">
        <img src="../logo.jpg" alt="Logo Helpdesk">
      </div>
      </div> 

 <div class="cards">
    <div class="card">
        <h3>Users</h3>
        <p><?= $nUsers ?></p>
    </div>
    <div class="card">
        <h3>Tickets</h3>
        <p><?= $nTickets ?></p>
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

    </div>
     </div>

     <div id="users" class="section">
        <div class="header">
               <div class="head">
                <p>Bienvenue Admin  <?= htmlspecialchars($user['nom']) ?> </p>
            </div>
       <div class="logo">
        <img src="../logo.jpg" alt="Logo Helpdesk">
       </div>
      </div> 
        <h3>Gestion des Users</h3>
         <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom Complet</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Date Creation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= $u['id_user'] ?></td>
                        <td><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['role'] ?></td>
                        <td><?= $u['date_creation'] ?></td>
                        <td>
                            <button class="btn edit"
                                   onclick="openEdit(
                                   <?= $u['id_user'] ?>,
                                   '<?= addslashes($u['nom']) ?>',
                                   '<?= addslashes($u['prenom']) ?>',
                                   '<?= addslashes($u['email']) ?>',
                                   '<?= $u['role'] ?>'
                                )">Update</button>
                            <button class="btn supp"
                               onclick="confsupp(<?= $u['id_user'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="editForm" style="display:none; margin-top:20px:">
            <h3>Edite Utilisateur</h3>
            <form  method="post">
                <input type="hidden" name="id_user" id="edit_id">
                <label for="">NOM</label>
                <input type="text" name="nom" id="edit_nom">
                <label for="">PRENOM</label>
                <input type="text" name="prenom" id="edit_prenom">
                <label for="">E-MAIL</label>
                <input type="email" name="email" id="edit_email">
                <label for="">ROLE</label>
                  <select name="role" id="edit_role">
                    <option value="UTILISATEUR">Utilisateur</option>
                    <option value="SUPPORT_N1">Support N1</option>
                    <option value="SUPPORT_N2">Support N2</option>
                    <option value="SUPERVISEUR">Superviseur</option>
                    <option value="ADMIN">Admin</option> 
                  </select>
                  <br><br>
                  <button type="submit" name="update_user">Valider</button>
            </form>

        </div>
    </div>

    <div id="histo" class="section">
    <div class="header">
        <div class="head">
            <p>Bienvenue Admin <?= htmlspecialchars($user['nom']) ?></p>
        </div>

        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>
    </div>

    <h2>Historique du Système</h2>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ticket</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Commentaire</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            <?php if(count($historiques) > 0): ?>
                <?php foreach($historiques as $h): ?>
                    <tr>
                        <td><?= $h['id_historique'] ?></td>
                        <td><?= htmlspecialchars($h['titre'] ?? 'Système') ?></td>
                        <td>
                            <?= htmlspecialchars($h['nom'].' '.$h['prenom']) ?>
                        </td>
                        <td><span class="badge"> <?= htmlspecialchars($h['action']) ?> </span></td>
                        <td><?= htmlspecialchars($h['commentaire']) ?></td>
                        <td><?= $h['date_action'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Aucun historique</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>   

     <div id="param" class="section">
        <div class="header">
               <div class="head">
                <p>Bienvenue Admin  <?= htmlspecialchars($user['nom']) ?> </p>
            </div>
       <div class="logo">
        <img src="../logo.jpg" alt="Logo Helpdesk">
       </div>
      </div> 
        <h2>Parametrage du Systeme</h2>
    <div class="param-grids">

        <div class="param-boxs">
        <h3>Statuts des Tickets</h3>
        <form action="" method="post">
            <input type="text" name="new_statut" placeholder="Ajoute une nouvelle statut">
            <button type="submit" name="add_statut">Ajouter</button>
        </form>
        <ul>
            <?php foreach($statuts as $s): ?>
                <li>
                    <?= htmlspecialchars($s['libelle']) ?>
                    <a href="?supp_statut=<?= $s['id_statut'] ?>"><i class="fa-solid fa-trash"></i></a>
                </li>
            <?php endforeach; ?>    
        </ul>
        </div>

        <div class="param-boxs">
        <h3>Priorite des Tickets</h3>
        <form action="" method="post">
            <input type="text" name="new_priorite" placeholder="Ajoute une nouvelle priorite">
            <button type="submit" name="add_priorite">Ajouter</button>
        </form>
        <ul>
            <?php foreach($priorites as $p): ?>
                <li>
                    <?= htmlspecialchars($p['libelle']) ?>
                    <a href="?supp_priorite=<?= $p['id_priorite'] ?>"><i class="fa-solid fa-trash"></i></a>
                </li>
            <?php endforeach; ?>    
        </ul>
        </div>

        <div class="param-boxs">
        <h3>Modules Sifcom</h3>
        <form action="" method="post">
            <input type="text" name="new_module" placeholder="Ajoute une nouvelle module">
            <button type="submit" name="add_module">Ajouter</button>
        </form>
        <ul>
            <?php foreach($modules as $m): ?>
                <li>
                    <?= htmlspecialchars($m['nom']) ?>
                    <a href="?supp_module=<?= $m['id_module'] ?>"><i class="fa-solid fa-trash"></i></a>
                </li>
            <?php endforeach; ?>    
        </ul>
        </div>

        <div class="param-boxs">
        <h3>Categories des Tickets</h3>
        <form action="" method="post">
            <input type="text" name="new_categorie" placeholder="Ajoute une nouvelle categories">
            <button type="submit" name="add_categorie">Ajouter</button>
        </form>
        <ul>
            <?php foreach($categories as $c): ?>
                <li>
                    <?= htmlspecialchars($c['libelle']) ?>
                    <a href="?supp_categorie=<?= $c['id_categorie'] ?>"><i class="fa-solid fa-trash"></i></a>
                </li>
            <?php endforeach; ?>    
        </ul>
      </div>
    </div>
 </div>
 <div id="audit" class="section">
        <div class="header">
               <div class="head">
                <p>Bienvenue Admin  <?= htmlspecialchars($user['nom']) ?> </p>
            </div>
       <div class="logo">
        <img src="../logo.jpg" alt="Logo Helpdesk">
       </div>
      </div> 
        <h2>Audit du Systeme</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID Log</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Table Concernee</th>
                    <th>Date Creation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($audit as $a): ?>
                    <tr>
                        <td><?= $a['id_log'] ?></td>
                        <td><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></td>
                        <td><?= htmlspecialchars($a['action']) ?> </td>
                        <td><?= htmlspecialchars($a['table_concernee']) ?> </td>
                        <td><?= htmlspecialchars($a['date_action']) ?> </td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>    
<div id="baseC" class="section">
      <?php if(isset($_SESSION['toast'])): ?>
    <div class="success-msg">
        <?= $_SESSION['toast']; ?>
    </div>
   <?php unset($_SESSION['toast']); endif; ?>

    <div class="header">
        <div class="head">
            <p>Bienvenu ADMIN <?= htmlspecialchars($user['nom']) ?></p>
        </div>
        <div class="logo">
            <img src="../logo.jpg" alt="Logo Helpdesk">
        </div>
    </div>

    <h2>Ajouter un Article</h2>

    <form method="POST" class="form-box">

        <input type="text"
               name="titre"
               placeholder="Titre"
               required> <br> <br>

        <textarea name="mots_cles"
                  placeholder="Mots clés : réseau, mot de passe..."
                  required></textarea> <br> <br>       

        <textarea name="contenu"
                  placeholder="Contenu de l'article"
                  required></textarea> <br> <br>

        

        <button type="submit" name="add_article" class="btn success">
            Ajouter
        </button>

    </form>


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

    function openEdit(id, nom, prenom, email, role) {
        document.getElementById("editForm").style.display="block";

        document.getElementById("edit_id").value = id;
        document.getElementById("edit_nom").value = nom;
        document.getElementById("edit_prenom").value = prenom;
        document.getElementById("edit_email").value = email;
        document.getElementById("edit_role").value = role;

        window.scrollTo(0, document.body.scrollHeight);
    }

    function confsupp(id){
        if (confirm("Voulez-vous supprimer cet user")){
            window.location.href = "dashboard.php?supp_id=" + id;
    }
                }
   </script>
</body>
</html>