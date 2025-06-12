<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['cat_id'])) {
  echo json_encode([]);
  exit;
}

$catId = (int)$_GET['cat_id'];

$stmt = $conn->prepare("
  SELECT m.id, m.title, m.cover_image 
  FROM mangas m 
  JOIN manga_categoria mc ON mc.manga_id = m.id 
  WHERE mc.categoria_id = ?
");
$stmt->bind_param("i", $catId);
$stmt->execute();
$result = $stmt->get_result();

$mangas = [];
while ($row = $result->fetch_assoc()) {
  $mangas[] = $row;
}

$stmt->close();

echo json_encode($mangas);
