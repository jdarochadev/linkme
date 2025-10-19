<?php
require_once 'config/db.php';

$page_title = 'Criar Conta';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Todos os campos são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'As senhas não coincidem.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $error = 'O username deve conter apenas letras, números, hífens e underscores.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = 'Username ou e-mail já cadastrado.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);

                $user_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO profiles (user_id, profile_title, bio, bg_color, link_color, text_color) VALUES (?, 'Meus Links', '', '#FFFFFF', '#000000', '#FFFFFF')");
                $stmt->execute([$user_id]);

                $pdo->commit();

                header('Location: login.php?registered=1');
                exit;
            }
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Erro ao criar conta. Tente novamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Criar Conta</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                <small>Será usado na sua URL: linkme/seu-username</small>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Senha</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Criar Conta</button>
        </form>

        <p class="auth-link">Já tem uma conta? <a href="login.php">Entrar</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
