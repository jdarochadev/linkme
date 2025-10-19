<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$page_title = 'Login';
$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Conta criada com sucesso! Faça login para continuar.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Preencha todos os campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'E-mail ou senha incorretos.';
            }
        } catch(PDOException $e) {
            $error = 'Erro ao fazer login. Tente novamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Login</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>

        <p class="auth-link">Não tem uma conta? <a href="register.php">Criar conta</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
