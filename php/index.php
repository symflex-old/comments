<?php

require_once 'db.php';
$config = include 'config.php';

$db = new Db($config['dsn'], $config['user'], $config['pass']);

$action      = isset($_GET['action']) ? $_GET['action'] : null;
$postId      = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$commentId   = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$commentText = isset($_POST['text']) ? $_POST['text'] : null;

$result = [];

try {
    switch ($action) {
        case 'create':
            $result = $db->insertComment($postId, $commentText, $commentId);
            break;
        default:
            $result = $db->getComments($postId);
    }
} catch (Exception $e) {
    $result = [
        'error' => true,
        'message' => $e->getMessage()
    ];
}

echo json_encode($result, true);
