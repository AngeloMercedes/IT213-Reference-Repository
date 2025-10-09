<?php
header('Content-Type: application/json; charset=utf-8');

// SECURITY: allow only JSON
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false){
  echo json_encode(['ok'=>false, 'error'=>'invalid_content_type']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)){
  echo json_encode(['ok'=>false, 'error'=>'bad_json']); exit;
}

// Honeypot field should be empty
if (!empty($input['hp'])){
  echo json_encode(['ok'=>true, 'ticket'=>'ignored']); exit; // pretend OK to confuse bots
}

// Validate
$location = trim($input['location'] ?? '');
$issue    = trim($input['issue'] ?? '');
$details  = trim($input['details'] ?? '');
$contact  = trim($input['contact'] ?? '');
$priority = trim($input['priority'] ?? 'Normal');

if ($location === '' || $issue === ''){
  echo json_encode(['ok'=>false, 'error'=>'missing_fields']); exit;
}

// Build record
$record = [
  'ticket'      => 'SJC-' . strtoupper(bin2hex(random_bytes(3))), // e.g., SJC-A1B2C3
  'location'    => $location,
  'issue'       => $issue,
  'details'     => $details,
  'contact'     => $contact,
  'priority'    => $priority,
  'client_time' => $input['client_time'] ?? null,
  'server_time' => date('c'),
  'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
  'ua'          => $_SERVER['HTTP_USER_AGENT'] ?? null
];

// Ensure data folder
$dir = __DIR__ . '/data';
if (!is_dir($dir)){
  if (!mkdir($dir, 0775, true)){
    echo json_encode(['ok'=>false, 'error'=>'mkdir_failed']); exit;
  }
}

$file = $dir . '/reports.json';
if (!file_exists($file)){
  // initialize
  file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
}

// Atomic-ish append: read + write with lock
$fp = fopen($file, 'c+');
if(!$fp){ echo json_encode(['ok'=>false, 'error'=>'open_failed']); exit; }
flock($fp, LOCK_EX);
$contents = stream_get_contents($fp);
$exists = $contents ? json_decode($contents, true) : [];
if (!is_array($exists)) $exists = [];
$exists[] = $record;
ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($exists, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// Optional: send email (uncomment + configure)
// mail('maintenance@school.edu', '[SJC] New maintenance report '.$record['ticket'], print_r($record, true));

echo json_encode(['ok'=>true, 'ticket'=>$record['ticket']]);
