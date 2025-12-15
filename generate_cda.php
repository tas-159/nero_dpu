<?php
// ========== NO OUTPUT BEFORE HEADERS! ==========
ob_start();

require_once 'app/connect.php';
session_start();

if (!isset($_GET['noteId'])) {
    ob_end_clean();
    die("❌ Note ID required!");
}

$noteId = $_GET['noteId'];

try {
    // ========== FETCH DATA ==========
    $stmt = $pdo->prepare('
        SELECT n.id_note, n.Note, n.Date, p.IDpat, p.nom, p.prenom, p.datenai, p.numtel, p.diagnostique
        FROM note n
        JOIN patient p ON n.ID_patient = p.IDpat
        WHERE n.id_note = ?
    ');
    $stmt->execute([$noteId]);
    $data = $stmt->fetch();
    
    if (!$data) {
        ob_end_clean();
        die("❌ Note not found!");
    }
    
    // ========== SAFE VALUES ==========
    $patientId = htmlspecialchars($data['IDpat']);
    $nom = htmlspecialchars($data['nom']);
    $prenom = htmlspecialchars($data['prenom']);
    $datenai = htmlspecialchars($data['datenai']);
    $numtel = htmlspecialchars($data['numtel'] ?? 'Non communiqué');
    $diagnostique = htmlspecialchars($data['diagnostique'] ?? 'Pas de diagnostique');
    $noteText = htmlspecialchars($data['Note'] ?? '');
    $noteDate = $data['Date'];
    $noteId = htmlspecialchars($noteId);
    
    // ========== GENERATE CDA XML ==========
    $cda = '<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <!-- Document Type -->
    <typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
    <templateId root="2.16.840.1.113883.10.12.1"/>
    
    <!-- Document Metadata -->
    <id root="' . uniqid('CDA-') . '"/>
    <code code="34108-1" codeSystem="2.16.840.1.113883.6.1" displayName="Outpatient Note"/>
    <title>Consultation Note - Neuro Patient</title>
    <effectiveTime value="' . date('YmdHis', strtotime($noteDate)) . '"/>
    <confidentialityCode code="N" codeSystem="2.16.840.1.113883.5.25" displayName="Normal"/>
    <languageCode code="fr-FR"/>
    
    <!-- Document Status -->
    <statusCode code="completed"/>
    
    <!-- Patient Information -->
    <recordTarget typeCode="RCT" contextControlCode="OP">
        <patientRole classCode="PAT">
            <id extension="' . $patientId . '" root="1.2.3.4.5.6.7.8.9"/>
            <patient classCode="PSN" determinerCode="INSTANCE">
                <name>
                    <given>' . $prenom . '</given>
                    <family>' . $nom . '</family>
                </name>
                <administrativeGenderCode code="M" codeSystem="2.16.840.1.113883.5.1"/>
                <birthTime value="' . str_replace('-', '', $datenai) . '"/>
            </patient>
            <providerOrganization classCode="ORG" determinerCode="INSTANCE">
                <name>Neuro Patient System</name>
            </providerOrganization>
        </patientRole>
    </recordTarget>
    
    <!-- Author (Doctor) -->
    <author typeCode="AUT" contextControlCode="OP">
        <time value="' . date('YmdHis', strtotime($noteDate)) . '"/>
        <assignedAuthor classCode="ASSIGNED">
            <id extension="' . ($_SESSION['id'] ?? 'DOC001') . '" root="1.2.3.4.5.6.7.8.9"/>
            <representedOrganization classCode="ORG" determinerCode="INSTANCE">
                <name>Medical Center</name>
            </representedOrganization>
        </assignedAuthor>
    </author>
    
    <!-- Custodian -->
    <custodian typeCode="CST">
        <assignedCustodian classCode="ASSIGNED">
            <representedCustodianOrganization classCode="ORG" determinerCode="INSTANCE">
                <name>Neuro Patient System</name>
            </representedCustodianOrganization>
        </assignedCustodian>
    </custodian>
    
    <!-- Clinical Content -->
    <component typeCode="COMP" contextConductionInd="true">
        <structuredBody classCode="SBOD" moodCode="EVN">
            
            <!-- Section: Assessment and Plan -->
            <component typeCode="COMP" contextConductionInd="true">
                <section classCode="DOCSECT" moodCode="EVN">
                    <title>Assessment and Plan</title>
                    <text>' . $noteText . '</text>
                </section>
            </component>
            
            <!-- Section: Diagnosis -->
            <component typeCode="COMP" contextConductionInd="true">
                <section classCode="DOCSECT" moodCode="EVN">
                    <title>Diagnosis</title>
                    <text>' . $diagnostique . '</text>
                </section>
            </component>
            
            <!-- Section: Patient Demographics -->
            <component typeCode="COMP" contextConductionInd="true">
                <section classCode="DOCSECT" moodCode="EVN">
                    <title>Patient Demographics</title>
                    <text>Date of Birth: ' . $datenai . '
Contact: ' . $numtel . '</text>
                </section>
            </component>
            
        </structuredBody>
    </component>
</ClinicalDocument>';
    
    // ========== SAVE XML FILE ==========
    $xmlDir = "cda_documents/";
    @mkdir($xmlDir, 0777, true);
    $xmlFile = $xmlDir . $noteId . ".xml";
    
    if (file_put_contents($xmlFile, $cda) === false) {
        ob_end_clean();
        die("❌ Erreur: Impossible de sauvegarder le fichier XML");
    }
    
    // ========== CLEAR OUTPUT BUFFER ==========
    ob_end_clean();
    
    // ========== DOWNLOAD HEADERS ==========
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="CDA-' . $noteId . '.xml"');
    header('Content-Length: ' . strlen($cda));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // ========== OUTPUT XML ==========
    echo $cda;
    exit();

} catch (Exception $e) {
    ob_end_clean();
    die("❌ Erreur: " . $e->getMessage());
}
?>
