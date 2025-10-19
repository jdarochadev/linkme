<?php
require_once 'config/db.php';

$username = $_GET['username'] ?? '';
$username = trim($username, '/');

if (empty($username)) {
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE LOWER(username) = LOWER(?)");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada</title>
    <link rel="stylesheet" href="/linkme/css/style.css">
</head>
<body>
    <div class="public-page" style="background-color: #f5f5f5;">
        <div class="profile-container">
            <h1 style="color: #333;">Página não encontrada</h1>
            <p style="color: #666;">O usuário que você está procurando não existe.</p>
            <a href="/linkme/register.php" class="link-button" style="background-color: #333; color: #fff; margin-top: 20px;">Criar minha página</a>
        </div>
    </div>
</body>
</html>';
        exit;
    }

    $stmt = $pdo->prepare("SELECT profile_title, bio, bg_color, link_color, text_color FROM profiles WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $profile = $stmt->fetch();

    if (!$profile) {
        $profile = [
            'profile_title' => 'Meus Links',
            'bio' => '',
            'bg_color' => '#FFFFFF',
            'link_color' => '#000000',
            'text_color' => '#FFFFFF'
        ];
    }

    $stmt = $pdo->prepare("SELECT id, title, url FROM links WHERE user_id = ? ORDER BY display_order ASC, created_at ASC");
    $stmt->execute([$user['id']]);
    $links = $stmt->fetchAll();

} catch(PDOException $e) {
    http_response_code(500);
    die('Erro ao carregar a página.');
}

$page_title = htmlspecialchars($profile['profile_title']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - @<?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="/linkme/css/style.css">
    <style>
        .public-page {
            background-color: <?php echo htmlspecialchars($profile['bg_color']); ?>;
        }
        .link-button {
            background-color: <?php echo htmlspecialchars($profile['link_color']); ?>;
            color: <?php echo htmlspecialchars($profile['text_color']); ?>;
        }
    </style>
</head>
<body>
    <div class="public-page">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                </div>
                <h1 class="profile-title"><?php echo htmlspecialchars($profile['profile_title']); ?></h1>
                <?php if (!empty($profile['bio'])): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="links-container">
                <?php if (empty($links)): ?>
                    <p class="no-links">Nenhum link disponível no momento.</p>
                <?php else: ?>
                    <?php foreach ($links as $link): ?>
                        <a href="/linkme/redirect.php?id=<?php echo $link['id']; ?>" class="link-button" target="_blank" rel="noopener noreferrer">
                            <?php echo htmlspecialchars($link['title']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="powered-by">
                <a href="/linkme/register.php">Crie sua própria página</a>
            </div>
        </div>
    </div>
</body>
</html>
