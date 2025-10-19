<?php
require_once 'config/db.php';

$link_id = $_GET['id'] ?? 0;

if (!$link_id || !is_numeric($link_id)) {
    header('Location: /linkme/');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT url FROM links WHERE id = ?");
    $stmt->execute([$link_id]);
    $link = $stmt->fetch();

    if (!$link) {
        header('Location: /linkme/');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE links SET clicks = clicks + 1 WHERE id = ?");
    $stmt->execute([$link_id]);

    header('Location: ' . $link['url']);
    exit;

} catch(PDOException $e) {
    header('Location: /linkme/');
    exit;
}
