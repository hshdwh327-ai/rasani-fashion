<?php
session_start();
header('Content-Type: application/json');

$CONFIG = [
  'password' => 'rasani2026',
  'session_name' => 'rasani_editor',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $password = isset($input['password']) ? (string)$input['password'] : '';
  if ($password === $CONFIG['password']) {
    $_SESSION[$CONFIG['session_name']] = true;
    echo json_encode(['ok' => true]);
    exit;
  }
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Password salah']);
  exit;
}

$loggedIn = isset($_SESSION[$CONFIG['session_name']]) && $_SESSION[$CONFIG['session_name']] === true;
echo json_encode(['loggedIn' => $loggedIn]);
