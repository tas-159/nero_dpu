<?php
// ========== ERROR DISPLAY (Dev) ==========
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========== DATABASE & SESSION ==========
require_once 'app/connect.php';
session_start();

// ========== GET PATIENT ID ==========
if (!isset($_GET['id_patient'])) {
    die("❌ Patient ID required!");
}
$patientId = $_GET['id_patient'];

// ========== UPDATE LAST CONSULTATION DATE ==========
$lastConsultation = $pdo->prepare("UPDATE `patient` SET `datemes` = NOW() WHERE `IDpat` = ?");
$lastConsultation->execute([$patientId]);

// ========== FETCH PATIENT DATA ==========
$reponse = $pdo->prepare("SELECT * FROM patient WHERE IDpat = ?");
$reponse->execute([$patientId]);
$patient = $reponse->fetch();

if (!$patient) {
    die("❌ Patient not found!");
}

// ========== SET SESSION & FOLDER PATHS ==========
$_SESSION['IDpat'] = $patientId;
$id = $patient["IDpat"];
$baseDir = "data/patients/$id/";

// ========== CREATE FOLDERS IF NOT EXIST ==========
@mkdir($baseDir, 0777, true);
@mkdir($baseDir . "image/", 0777, true);
@mkdir($baseDir . "document/", 0777, true);
@mkdir($baseDir . "zip/", 0777, true);
@mkdir($baseDir . "video/", 0777, true);

// ========== FOLDER PATHS ==========
$updirim = $baseDir . "image/";
$updirdoc = $baseDir . "document/";
$updirzip = $baseDir . "zip/";
$updirvid = $baseDir . "video/";

// ========== FETCH CONSULTATION NOTES ==========
$reponseNote = $pdo->prepare("
    SELECT `ID_patient`, `id_note`, `Note`, SUBSTRING(Note, 1, 150) AS Excerpt, `Date` 
    FROM `note` 
    WHERE ID_patient = ?
    ORDER BY Date DESC
");
$reponseNote->execute([$patientId]);
$notes = $reponseNote->fetchAll();

// ========== HANDLE FILE UPLOADS ==========
$uploadSuccess = false;
$uploadError = "";

if (isset($_POST['upim'])) {  // DICOM Image
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file = $_FILES["file"];
        $fileName = basename($file["name"]);
        $target = $updirim . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $target)) {
            $uploadSuccess = true;
            header("Location: patient.php?id_patient=$patientId");
            exit();
        } else {
            $uploadError = "❌ Erreur upload image";
        }
    }
}

if (isset($_POST['updoc'])) {  // Document
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file = $_FILES["file"];
        $fileName = basename($file["name"]);
        $target = $updirdoc . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $target)) {
            $uploadSuccess = true;
            header("Location: patient.php?id_patient=$patientId");
            exit();
        } else {
            $uploadError = "❌ Erreur upload document";
        }
    }
}

if (isset($_POST['upzip'])) {  // Archive
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file = $_FILES["file"];
        $fileName = basename($file["name"]);
        $target = $updirzip . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $target)) {
            $uploadSuccess = true;
            header("Location: patient.php?id_patient=$patientId");
            exit();
        } else {
            $uploadError = "❌ Erreur upload archive";
        }
    }
}

if (isset($_POST['upvid'])) {  // Video
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file = $_FILES["file"];
        $fileName = basename($file["name"]);
        $target = $updirvid . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $target)) {
            $uploadSuccess = true;
            header("Location: patient.php?id_patient=$patientId");
            exit();
        } else {
            $uploadError = "❌ Erreur upload vidéo";
        }
    }
}

// ========== LOAD TEMPLATE ==========
$title = 'Dossier Patient';
$template = 'patient';
include 'layout.phtml';
?>
