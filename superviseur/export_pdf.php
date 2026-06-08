<?php
session_start();
ob_start();
include '../configDb.php';

if(!isset($_SESSION['user'])) {
    header("location: ../login.php");
    exit();
}

 if($_SESSION['user']['role'] !== 'SUPERVISEUR') {
    echo " Acces refuse";
    exit();
  }

 require '../vendor/autoload.php';
   use Dompdf\Dompdf;
   use Dompdf\Options; 

   $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';
     $id_ticket = $_GET['id_ticket'] ?? '';

    $sql = "SELECT  t.id_ticket, t.titre, t.description, t.date_creation, u.nom,
            u.prenom, s.libelle AS statut, c.libelle AS categorie, m.nom AS module
            FROM ticket t  LEFT JOIN utilisateur u ON t.id_user = u.id_user
            LEFT JOIN statut_ticket s  ON t.id_statut = s.id_statut 
            LEFT JOIN categorie_ticket c ON t.id_categorie = c.id_categorie
            LEFT JOIN module_sifcom m ON t.id_module = m.id_module WHERE 1=1";

        $params = [];
        
        
    if(!empty($id_ticket)) {
        $sql .= " AND t.id_ticket = ?";
        $params[] = $id_ticket;
    } 
    
    if(!empty($date_debut) && !empty($date_fin)) {
        $sql .= " AND DATE(t.date_creation) BETWEEN ? AND ?";
        $params[] = $date_debut;
        $params[] = $date_fin;
    }

    $sql .= " ORDER BY t.date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
   
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $logoPath = __DIR__ . '/../logo.jpg';
    $logo = base64_encode(file_get_contents($logoPath));

   $logoHtml = '<img src="data:image/jpeg;base64,' . $logo . '" width="90">';


    $html = '
    <style>
       body{
       font-family: Arial;
       }

       h1{
       text-align: center;
       color: #2c3e50;
       }
       table{
        width: 100%;
        border-collapse: collapse;
       }
        th{
         background: #3498db;
        }
         th, td{
          border: 1px solid #ddd;
          padding: 8px;
          font-size: 12px;
         }
    </style>

    <div style="text-align:center; margin-bottom:15px;">
       '.$logoHtml.'
    </div>
    
    <h1> Rapport HelpDesk </h1>
        <div class="info">
          <p><strong>Date :</strong> '.date('d/m/Y H:i').'</p>
          <p><strong>Total tickets :</strong> '.count($tickets).'</p>
        </div>
       <table>
            <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Module</th>
                    <th>Catégorie</th>
                    <th>Utilisateur</th>
                    <th>Statut</th>
                    <th>Date</th>
             </tr>
    ';

    foreach($tickets as $t){
        $html .= '
          <tr>
                <td>' .$t['id_ticket']. '</td>
               <td>' .htmlspecialchars($t['titre']). '</td>
               <td>'.htmlspecialchars($t['description']).'</td>
               <td>'.htmlspecialchars($t['module']).'</td>
               <td>'.htmlspecialchars($t['categorie']).'</td>
               <td>' .htmlspecialchars($t['nom']. ' ' .$t['prenom']). '</td>
               <td>' .$t['statut']. '</td>
                <td>' .$t['date_creation']. '</td>
            </tr>';
    }

    $html .= '</table>';


    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();


    ob_end_clean();
    $dompdf->stream("rapport_helpdesk.pdf", ["Attachment" => true]);
    exit;

?>