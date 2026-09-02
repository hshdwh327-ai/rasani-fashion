<?php
session_name('rasani_editor');
session_start();

header('Content-Type: application/json');
$BASE_DIR = __DIR__ . '/..';
$INV_PATH = $BASE_DIR . '/data/inventory.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (!file_exists($INV_PATH)) {
    echo json_encode(['ok' => false, 'error' => 'inventory tidak ditemukan']);
    exit;
  }
  $content = file_get_contents($INV_PATH);
  $data = json_decode($content, true);
  if (!is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'JSON inventory invalid']);
    exit;
  }
  echo json_encode(['ok' => true, 'data' => $data]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_SESSION['rasani_editor'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Tidak diizinkan']);
    exit;
  }

  $raw = file_get_contents('php://input');
  $payload = json_decode($raw, true);
  if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Payload harus berisi data array']);
    exit;
  }

  $tmp = $INV_PATH . '.tmp';
  $json = json_encode($payload['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($json === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Gagal encode JSON']);
    exit;
  }

  if (file_put_contents($tmp, $json) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Gagal menulis file inventory']);
    exit;
  }

  if (!rename($tmp, $INV_PATH)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Gagal mengganti inventory']);
    exit;
  }

  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method tidak diizinkan']);
