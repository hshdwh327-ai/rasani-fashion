<?php
session_name('rasani_editor');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method tidak diizinkan']);
  exit;
}

if (empty($_SESSION['rasani_editor'])) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'Tidak diizinkan']);
  exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'File tidak ditemukan']);
  exit;
}

$id = isset($_POST['id_produk']) ? (string)$_POST['id_produk'] : '';
if ($id === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'id_produk wajib diisi']);
  exit;
}

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Gagal membuat folder uploads']);
  exit;
}

$tmp = $_FILES['file']['tmp_name'];
$info = getimagesize($tmp);
if ($info === false) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'File bukan gambar']);
  exit;
}

$ext = image_type_to_extension($info[2], false);
$filename = preg_replace('/[^A-Za-z0-9._-]/', '', $id) . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
$target = $uploadDir . '/' . $filename;

if (!move_uploaded_file($tmp, $target)) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Gagal memindahkan file']);
  exit;
}

$invPath = __DIR__ . '/../data/inventory.json';
$oldUrl = '';
if (file_exists($invPath)) {
  $content = file_get_contents($invPath);
  $data = json_decode($content, true);
  if (is_array($data)) {
    foreach ($data as $item) {
      if ((string)($item['id_produk'] ?? '') === $id) {
        $oldUrl = (string)($item['foto'] ?? '');
        break;
      }
    }
  }
}

$publicUrl = '/uploads/' . rawurlencode($filename);

if ($oldUrl !== '' && str_starts_with($oldUrl, '/uploads/')) {
  $oldPath = $uploadDir . '/' . basename(parse_url($oldUrl, PHP_URL_PATH));
  if (is_file($oldPath)) {
    @unlink($oldPath);
  }
}

echo json_encode(['ok' => true, 'url' => $publicUrl, 'file' => $filename]);
