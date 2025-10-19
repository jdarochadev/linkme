<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT profile_title, bio, bg_color, link_color, text_color FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();

    if (!$profile) {
        $stmt = $pdo->prepare("INSERT INTO profiles (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $profile = [
            'profile_title' => 'Meus Links',
            'bio' => '',
            'bg_color' => '#FFFFFF',
            'link_color' => '#000000',
            'text_color' => '#FFFFFF'
        ];
    }

} catch(PDOException $e) {
    die('Erro ao carregar dados do usuário.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_link') {
            $title = trim($_POST['title'] ?? '');
            $url = trim($_POST['url'] ?? '');

            if (empty($title) || empty($url)) {
                $error = 'Título e URL são obrigatórios.';
            } else {
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }

                try {
                    $stmt = $pdo->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM links WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $next_order = $stmt->fetch()['next_order'];

                    $stmt = $pdo->prepare("INSERT INTO links (user_id, title, url, display_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $url, $next_order]);
                    $message = 'Link adicionado com sucesso!';
                } catch(PDOException $e) {
                    $error = 'Erro ao adicionar link.';
                }
            }
        } elseif ($action === 'delete_link') {
            $link_id = $_POST['link_id'] ?? 0;

            try {
                $stmt = $pdo->prepare("DELETE FROM links WHERE id = ? AND user_id = ?");
                $stmt->execute([$link_id, $user_id]);
                $message = 'Link excluído com sucesso!';
            } catch(PDOException $e) {
                $error = 'Erro ao excluir link.';
            }
        } elseif ($action === 'edit_link') {
            $link_id = $_POST['link_id'] ?? 0;
            $title = trim($_POST['title'] ?? '');
            $url = trim($_POST['url'] ?? '');

            if (empty($title) || empty($url)) {
                $error = 'Título e URL são obrigatórios.';
            } else {
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }

                try {
                    $stmt = $pdo->prepare("UPDATE links SET title = ?, url = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$title, $url, $link_id, $user_id]);
                    $message = 'Link atualizado com sucesso!';
                } catch(PDOException $e) {
                    $error = 'Erro ao atualizar link.';
                }
            }
        } elseif ($action === 'update_profile') {
            $profile_title = trim($_POST['profile_title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $bg_color = $_POST['bg_color'] ?? '#FFFFFF';
            $link_color = $_POST['link_color'] ?? '#000000';
            $text_color = $_POST['text_color'] ?? '#FFFFFF';

            try {
                $stmt = $pdo->prepare("UPDATE profiles SET profile_title = ?, bio = ?, bg_color = ?, link_color = ?, text_color = ? WHERE user_id = ?");
                $stmt->execute([$profile_title, $bio, $bg_color, $link_color, $text_color, $user_id]);
                $message = 'Aparência atualizada com sucesso!';

                $profile['profile_title'] = $profile_title;
                $profile['bio'] = $bio;
                $profile['bg_color'] = $bg_color;
                $profile['link_color'] = $link_color;
                $profile['text_color'] = $text_color;
            } catch(PDOException $e) {
                $error = 'Erro ao atualizar aparência.';
            }
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT id, title, url, clicks, display_order FROM links WHERE user_id = ? ORDER BY display_order ASC, created_at ASC");
    $stmt->execute([$user_id]);
    $links = $stmt->fetchAll();
} catch(PDOException $e) {
    $links = [];
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <div class="header-content">
            <h1>Meu Painel</h1>
            <div class="header-actions">
                <a href="/linkme/<?php echo htmlspecialchars($user['username']); ?>" class="btn btn-secondary" target="_blank">Ver minha página</a>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-section">
                <h2>Gerenciar Links</h2>

                <div class="card">
                    <h3>Adicionar Novo Link</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_link">
                        <div class="form-group">
                            <label for="title">Título</label>
                            <input type="text" id="title" name="title" placeholder="Ex: Meu Instagram" required>
                        </div>
                        <div class="form-group">
                            <label for="url">URL</label>
                            <input type="text" id="url" name="url" placeholder="Ex: instagram.com/meuusuario" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Adicionar Link</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Meus Links</h3>
                    <?php if (empty($links)): ?>
                        <p class="no-data">Você ainda não tem links. Adicione seu primeiro link acima!</p>
                    <?php else: ?>
                        <div id="sortable-links" class="links-list">
                            <?php foreach ($links as $link): ?>
                                <div class="link-item" data-id="<?php echo $link['id']; ?>">
                                    <div class="link-handle">☰</div>
                                    <div class="link-info">
                                        <strong><?php echo htmlspecialchars($link['title']); ?></strong>
                                        <small><?php echo htmlspecialchars($link['url']); ?></small>
                                        <span class="link-clicks"><?php echo $link['clicks']; ?> cliques</span>
                                    </div>
                                    <div class="link-actions">
                                        <button onclick="openEditModal(<?php echo $link['id']; ?>, '<?php echo htmlspecialchars(addslashes($link['title'])); ?>', '<?php echo htmlspecialchars(addslashes($link['url'])); ?>')" class="btn btn-sm btn-secondary">Editar</button>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este link?');">
                                            <input type="hidden" name="action" value="delete_link">
                                            <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Personalizar Aparência</h2>

                <div class="card">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label for="profile_title">Título da Página</label>
                            <input type="text" id="profile_title" name="profile_title" value="<?php echo htmlspecialchars($profile['profile_title']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="bio">Biografia</label>
                            <textarea id="bio" name="bio" rows="3" placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="bg_color">Cor de Fundo</label>
                            <input type="color" id="bg_color" name="bg_color" value="<?php echo htmlspecialchars($profile['bg_color']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="link_color">Cor dos Links</label>
                            <input type="color" id="link_color" name="link_color" value="<?php echo htmlspecialchars($profile['link_color']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="text_color">Cor do Texto</label>
                            <input type="color" id="text_color" name="text_color" value="<?php echo htmlspecialchars($profile['text_color']); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Aparência</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Prévia</h3>
                    <div class="preview-box" style="background-color: <?php echo htmlspecialchars($profile['bg_color']); ?>; padding: 20px; border-radius: 8px;">
                        <div style="text-align: center;">
                            <h4 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($profile['profile_title']); ?></h4>
                            <?php if (!empty($profile['bio'])): ?>
                                <p style="font-size: 14px; margin: 0 0 15px 0;"><?php echo htmlspecialchars($profile['bio']); ?></p>
                            <?php endif; ?>
                            <div style="background-color: <?php echo htmlspecialchars($profile['link_color']); ?>; color: <?php echo htmlspecialchars($profile['text_color']); ?>; padding: 12px; border-radius: 8px; text-align: center;">
                                Exemplo de Link
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeEditModal()">&times;</span>
        <h3>Editar Link</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit_link">
            <input type="hidden" id="edit_link_id" name="link_id">

            <div class="form-group">
                <label for="edit_title">Título</label>
                <input type="text" id="edit_title" name="title" required>
            </div>

            <div class="form-group">
                <label for="edit_url">URL</label>
                <input type="text" id="edit_url" name="url" required>
            </div>

            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
