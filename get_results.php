<?php
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=tiktok_checker', 'root', '');
$stmt = $pdo->prepare("SELECT * FROM combos ORDER BY checked_at DESC LIMIT 100");
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as &$result) {
    $result['status'] = $result['status']; // valid/invalid
}

echo json_encode($results);
?>
