<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reorder_links') {
        $order = $_POST['order'] ?? '';

        if (empty($order)) {
            echo json_encode(['success' => false, 'message' => 'Ordem inválida']);
            exit;
        }

        $link_ids = explode(',', $order);
        $link_ids = array_filter($link_ids, 'is_numeric');

        if (empty($link_ids)) {
            echo json_encode(['success' => false, 'message' => 'Ordem inválida']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE links SET display_order = ? WHERE id = ? AND user_id = ?");

            foreach ($link_ids as $index => $link_id) {
                $stmt->execute([$index, $link_id, $user_id]);
            }

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Ordem atualizada com sucesso']);

        } catch(PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar ordem']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
