<?php
require_once 'app/connect.php';

if (isset($_GET['noteId'])) {
    $noteId = $_GET['noteId'];
    
    // ========== FETCH DATA ==========
    $stmt = $pdo->prepare('
        SELECT n.id_note, n.Note, n.Date, p.nom, p.prenom, p.datenai, p.numtel, p.diagnostique
        FROM note n
        JOIN patient p ON n.ID_patient = p.IDpat
        WHERE n.id_note = ?
    ');
    $stmt->execute([$noteId]);
    $data = $stmt->fetch();
    
    if (!$data) {
        die("❌ Note not found!");
    }
    
    // ========== SAFE VALUES ==========
    $nom = htmlspecialchars($data['nom']);
    $prenom = htmlspecialchars($data['prenom']);
    $datenai = htmlspecialchars($data['datenai']);
    $numtel = htmlspecialchars($data['numtel'] ?? 'Non communiqué');  // ✅ FIX: Default value
    $diagnostique = htmlspecialchars($data['diagnostique'] ?? 'Pas de diagnostique');
    $note = htmlspecialchars($data['Note'] ?? '');
    $dateFormatted = date('d/m/Y H:i', strtotime($data['Date']));
    $noteId = htmlspecialchars($noteId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDA Document - <?php echo $noteId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .cda-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        .cda-header {
            border-bottom: 3px solid #0088cc;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .cda-header h1 {
            color: #0088cc;
            font-size: 24px;
        }
        .patient-info {
            background: #e8f4f8;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0088cc;
            border-radius: 4px;
        }
        .patient-info p {
            margin: 8px 0;
            font-size: 14px;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 16px;
            color: #0088cc;
            margin-bottom: 10px;
            border-bottom: 2px solid #0088cc;
            padding-bottom: 5px;
        }
        .section-content {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .buttons {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .btn-custom {
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .btn-download {
            background: #28a745;
            color: white;
        }
        .btn-download:hover {
            background: #218838;
            color: white;
        }
        .btn-back {
            background: #007bff;
            color: white;
        }
        .btn-back:hover {
            background: #0056b3;
            color: white;
        }
        .metadata {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="cda-container">
        <!-- Header -->
        <div class="cda-header">
            <h1>📋 Clinical Document Architecture (CDA)</h1>
            <div class="metadata">
                <strong>Document ID:</strong> <?php echo $noteId; ?> | 
                <strong>Date:</strong> <?php echo $dateFormatted; ?>
            </div>
        </div>

        <!-- Patient Information -->
        <div class="patient-info">
            <h5>👤 Patient Information</h5>
            <p><strong>Name:</strong> <?php echo $prenom; ?> <?php echo $nom; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $datenai; ?></p>
            <p><strong>Contact:</strong> <?php echo $numtel; ?></p>
        </div>

        <!-- Diagnosis Section -->
        <div class="section">
            <div class="section-title">📌 Diagnosis</div>
            <div class="section-content">
                <?php echo $diagnostique; ?>
            </div>
        </div>

        <!-- Clinical Notes Section -->
        <div class="section">
            <div class="section-title">📝 Clinical Notes</div>
            <div class="section-content">
                <?php echo $note; ?>
            </div>
        </div>

        <!-- Document Signature -->
        <div class="section" style="border-top: 2px solid #ddd; padding-top: 20px; margin-top: 30px;">
            <p style="font-size: 12px; color: #999;">
                <strong>Document Type:</strong> Clinical Consultation Note<br>
                <strong>Standard:</strong> HL7 CDA R2<br>
                <strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                <strong>Confidentiality:</strong> Patient
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="buttons">
            <a href="generate_cda.php?noteId=<?php echo $noteId; ?>" class="btn-custom btn-download">
                📥 Télécharger XML
            </a>
            <a href="javascript:history.back()" class="btn-custom btn-back">
                ← Retour
            </a>
            <a href="javascript:window.print()" class="btn-custom" style="background: #6c757d; color: white;">
                🖨️ Imprimer
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
<?php } else {
    die("❌ Note ID required!");
}
?>
