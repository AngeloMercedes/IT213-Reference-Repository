<?php
header('Content-Type: application/json; charset=utf-8');

// --- Helpers
function norm($s){
  $s = strtolower(trim($s ?? ''));
  return preg_replace('/[^a-z0-9\s]/','', $s);
}
function reply($type, $text=''){
  echo json_encode(['type'=>$type, 'reply'=>$text], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = $_POST['message'] ?? '';
$msg = norm($raw);

if ($msg === '') reply('error', '⚠️ Please enter a question.');

// --- Intents (keywords -> response)
$intents = [
  'greet' => [
    'keys'=>['hello','hi','good morning','good afternoon','good evening','hey'],
    'text'=>"👋 Hello! How can I help you with maintenance today?"
  ],
  'office_location' => [
    'keys'=>['where','location','office','maintenance office'],
    'text'=>"📍 The maintenance office is located in Building A, Room 102."
  ],
  'office_hours' => [
    'keys'=>['hours','time','open','schedule','office hours'],
    'text'=>"🕗 Office hours: Monday–Friday, 8:00 AM – 5:00 PM."
  ],
  'head' => [
    'keys'=>['who','in charge','head','chief'],
    'text'=>"👨‍🔧 Mr. John Smith is the head of the maintenance office."
  ],
  'contact_phone' => [
    'keys'=>['phone','contact','number','call'],
    'text'=>"📞 Contact us at (123) 456-7890."
  ],
  'contact_email' => [
    'keys'=>['email','mail'],
    'text'=>"📧 Email: maintenance@school.edu"
  ],
  'report' => [
    'keys'=>['report','broken','fix','repair','issue','problem','malfunction'],
    'text'=>"REPORT_FORM"
  ],
  'thanks' => [
    'keys'=>['thank you','thanks','ty'],
    'text'=>"You're welcome! 😊"
  ],
  'bye' => [
    'keys'=>['bye','goodbye','see you'],
    'text'=>"👋 Goodbye! Have a nice day."
  ],
];

// --- Simple matching: keyword containment + small fuzz
$detected = null;
foreach($intents as $intent => $data){
  foreach($data['keys'] as $k){
    if (strpos($msg, $k) !== false){
      $detected = $intent;
      break 2;
    }
  }
}

// If none matched, try single-word intersections
if (!$detected){
  $words = array_filter(explode(' ', $msg));
  foreach($intents as $intent=>$data){
    foreach($data['keys'] as $k){
      $parts = explode(' ', $k);
      if (count(array_intersect($words, $parts)) > 0){
        $detected = $intent; break 2;
      }
    }
  }
}

// Respond
if ($detected){
  $text = $intents[$detected]['text'];
  if ($text === 'REPORT_FORM'){
    echo json_encode(['type'=>'report_form']); exit;
  } else {
    reply('reply', $text);
  }
}

// Fallback
reply('fallback', '❓ Sorry, I don’t understand. Try asking about office location, hours, contacts, or say “report” to file an issue.');
