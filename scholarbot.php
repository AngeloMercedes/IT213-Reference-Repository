<?php
session_start();

// If request is POST -> treat as AJAX chat request and return JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // --- Load dataset (same as the large FAQ you provided) ---
    // For brevity I'll load a condensed but representative structure.
    // You can expand texts below as needed (I used full text blocks where important).
    $faq = [
        'meta' => [
            'office_function' => "The Scholarship Office is a function of the Student Affairs and Services Office. It handles or facilitates scholarship opportunities, starting from application to fund payouts.",
            'location' => "Ground floor, 2nd Door Room 119, College Building – Main Campus, Saint Joseph College, Maasin City, Southern Leyte.",
            'director' => "Joenisa M. Hoyla",
            'staff' => "Lara O. Orais and Zaneth O. Mulig",
            'facebook' => "D’ JOSEPHIAN ESKOLARS & Student Affairs and Services Office",
            'mobile' => "0947-3855424 or 0927-0182744",
            'landline' => "(053) 570–8448 local 110"
        ],
        'categories' => [
            'Government-funded Scholarships' => [
                'description' => "Government funded scholarships available at SJC include CMSP, TES-UNIFAST, TDP/TINGOG, and CoScho Coconut Scholarship.",
                'items' => [
                    'CHED Merit Scholarship Program (CMSP)' => "A government scholarship for incoming first-year students who meet specific academic requirements. Full: ₱60,000/yr; Half: ₱30,000/yr. Subject to evaluation every semester.",
                    'Tertiary Education Subsidy (TES-UNIFAST)' => "For college students from low-income families verified through DSWD LISTAHAN. ₱20,000 per year or ₱10,000 per semester (private schools).",
                    'Tulong Dunong Program (TDP/TINGOG)' => "Government assistance for low-income students. ₱7,500 per semester.",
                    'CoScho Coconut Scholarship Program' => "For registered farmers' dependents meeting income & course eligibility."
                ]
            ],
            'Diocese-funded Scholarships' => [
                'description' => "Scholarships granted by the Diocese of Maasin or allied programs (CORE, YSLEP/Caritas Manila).",
                'items' => [
                    'Diocesan Scholarship' => "70% tuition discount for diocesan school alumni; conditions and maintenance apply.",
                    'CORE' => "40% tuition discount to students from Sogod, Baybay and beyond.",
                    'YSLEP / Caritas Manila' => "Full college education for eligible poor but deserving youth."
                ]
            ],
            'School-funded Scholarships' => [
                'description' => "School-funded: Academic Scholarship, Varsity, Chorale, KAST, Presidential, Group, Employee discounts, etc.",
                'items' => [
                    'Academic Scholarship' => "Full / 3/4 / 1/2 tuition discounts depending on GWA ranges. Maintenance conditions apply.",
                    'Varsity Scholarship' => "For athletes representing SJC—covers tuition fully or partially.",
                    'Working Scholars Program' => "Students work part-time in assigned offices; ₱35/hour."
                ]
            ],
            'Alumni-funded Scholarships' => [
                'description' => "Scholarships supported by alumni such as High School Alumni Foundation Scholarship and Adopt-A-Student Program.",
                'items' => [
                    'High School Alumni Foundation Scholarship' => "Open to children of SJC alumni; conditions apply.",
                    'Adopt-A-Student Program' => "Sponsorship and cash allowance provided by identified alumni donors."
                ]
            ],
            'General' => [
                'description' => "General FAQs: grading, renewal, eligibility, application steps, deadlines, notification methods.",
                'items' => [
                    'How to apply - Government' => "Apply online or in person when CHED opens slots; Scholarship Office facilitates submission.",
                    'Notification of results' => "Notified via email, Scholarship bulletin, and official FB page."
                ]
            ]
        ],
        'grading' => [
            'system' => "Decimal grading system used; examples: 1.0 = 98%+ (Excellent), 1.25 = 97–95% (Very Good), 1.50 = 94–92%, 1.75 = 91–89%, 2.0 = 88–86%, 3.0 = 76–75% (Passed), 5.0 = below 75 (Failed).",
            'special' => "NC = No Credit, W = Withdrawn, FW = Failure due to withdrawal (unofficial), FA = Failure due to excess absences."
        ]
    ];

    // --- helpers ---
    $raw = $_POST['message'] ?? '';
    $q = trim($raw);
    $low = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $q));

    if ($low === '') {
        echo json_encode(['ok'=>false, 'type'=>'error','reply'=>'⚠️ Please enter a question.']);
        exit;
    }

    // Simple intent matching rules
    $reply = null;
    // direct meta questions
    if (preg_match('/\b(function|purpose|what is the function)\b/', $low)) {
        $reply = $faq['meta']['office_function'];
    } elseif (preg_match('/\b(where|location|room|address|located)\b/', $low)) {
        $reply = $faq['meta']['location'] . " (Landline: " . $faq['meta']['landline'] . ")";
    } elseif (preg_match('/\b(director|head|who is the director)\b/', $low)) {
        $reply = "Director: <b>" . $faq['meta']['director'] . "</b>. Staff: " . $faq['meta']['staff'] . ".";
    } elseif (preg_match('/\b(phone|mobile|contact|call|number)\b/', $low)) {
        $reply = "Mobile: " . $faq['meta']['mobile'] . " — Landline: " . $faq['meta']['landline'];
    } elseif (preg_match('/\b(facebook|fb|page)\b/', $low)) {
        $reply = "Facebook page: <b>" . $faq['meta']['facebook'] . "</b>.";
    } elseif (preg_match('/\b(what types|how many types|types of scholarships|what scholarships)\b/', $low)) {
        // list category keys
        $cats = array_keys($faq['categories']);
        $reply = "SJC offers these scholarship categories:\n• " . implode("\n• ", $cats);
    } elseif (preg_match('/\b(ched|cmsP|merit)\b/i', $q) || preg_match('/\b(CHED Merit|CMSP)\b/i', $q)) {
        $reply = $faq['categories']['Government-funded Scholarships']['items']['CHED Merit Scholarship Program (CMSP)'];
    } else {
        // Fallback: full-text search across FAQ text
        $candidates = [];
        // search meta
        foreach ($faq['meta'] as $k => $v) {
            if (stripos($v, $q) !== false || stripos($k, $low) !== false) {
                $candidates[] = $v;
            }
        }
        // search category items
        foreach ($faq['categories'] as $cat => $block) {
            if (stripos($block['description'], $q) !== false || stripos($cat, $q) !== false) {
                $candidates[] = $block['description'];
            }
            foreach ($block['items'] as $title => $text) {
                if (stripos($title, $q) !== false || stripos($text, $q) !== false || similar_text(strtolower($title), strtolower($q)) > 30) {
                    $candidates[] = "<b>$title</b>: $text";
                }
            }
        }
        // grading
        foreach ($faq['grading'] as $k => $v) {
            if (stripos($v, $q) !== false) $candidates[] = $v;
        }

        if (!empty($candidates)) {
            // prefer the best candidate (first)
            $reply = array_values(array_unique($candidates))[0];
        }
    }

    // If still no reply:
    if (!$reply) {
        $reply = "❓ I don’t have an exact answer for that yet. Try asking about office location, contact, scholarship types, or say 'show government scholarships'. You can also browse categories from the UI.";
    }

    // Save to session history and return last messages
    if (!isset($_SESSION['chat_history'])) $_SESSION['chat_history'] = [];
    $_SESSION['chat_history'][] = ['type'=>'user','message'=>htmlspecialchars($raw),'time'=>date('H:i')];
    $_SESSION['chat_history'][] = ['type'=>'bot','message'=>nl2br(htmlspecialchars($reply)),'time'=>date('H:i')];

    echo json_encode(['ok'=>true, 'reply'=>$reply, 'history'=>$_SESSION['chat_history'] ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- If GET (load UI) ----------
// We'll render the UI and also embed the FAQ dataset in JS for browsing convenience.
// Create a JSON-safe copy of the $faq used above for client-side browsing.
$faq_for_js = [
    'meta' => [
        'office_function' => "The Scholarship Office is a function of the Student Affairs and Services Office. It handles or facilitates scholarship opportunities, starting from application to fund payouts.",
        'location' => "Ground floor, 2nd Door Room 119, College Building – Main Campus, Saint Joseph College, Maasin City, Southern Leyte.",
        'director' => "Joenisa M. Hoyla",
        'staff' => "Lara O. Orais and Zaneth O. Mulig",
        'facebook' => "D’ JOSEPHIAN ESKOLARS & Student Affairs and Services Office",
        'mobile' => "0947-3855424 or 0927-0182744",
        'landline' => "(053) 570–8448 local 110"
    ],
    'categories' => [
        'Government-funded Scholarships' => [
            'description' => "Government funded scholarships available at SJC include CMSP, TES-UNIFAST, TDP/TINGOG, and CoScho Coconut Scholarship.",
            'items' => [
                'CHED Merit Scholarship Program (CMSP)' => "Full: ₱60,000/yr; Half: ₱30,000/yr. Subject to evaluation every semester.",
                'Tertiary Education Subsidy (TES-UNIFAST)' => "For low-income college students. ₱20,000 per year or ₱10,000 per semester (private schools).",
                'Tulong Dunong Program (TDP/TINGOG)' => "₱7,500 per semester.",
                'CoScho Coconut Scholarship Program' => "For registered farmers' dependents meeting income & course eligibility."
            ]
        ],
        'Diocese-funded Scholarships' => [
            'description' => "Scholarships granted by the Diocese of Maasin or allied programs (CORE, YSLEP/Caritas Manila).",
            'items' => [
                'Diocesan Scholarship' => "70% tuition discount for diocesan school alumni; conditions apply.",
                'CORE' => "40% tuition discount for certain areas.",
                'YSLEP / Caritas Manila' => "Full education support for eligible youth."
            ]
        ],
        'School-funded Scholarships' => [
            'description' => "School-funded: Academic Scholarship, Varsity, Chorale, KAST, Presidential, Group, Employee discounts, etc.",
            'items' => [
                'Academic Scholarship' => "Full / 3/4 / 1/2 tuition discounts depending on GWA ranges.",
                'Varsity Scholarship' => "For athletes representing SJC.",
                'Working Scholars Program' => "Students work part-time; ₱35/hour."
            ]
        ],
        'Alumni-funded Scholarships' => [
            'description' => "Scholarships supported by alumni such as High School Alumni Foundation Scholarship and Adopt-A-Student Program.",
            'items' => [
                'High School Alumni Foundation Scholarship' => "Open to children of SJC alumni.",
                'Adopt-A-Student Program' => "Sponsorship and cash allowance provided by alumni."
            ]
        ]
    ],
    'grading' => [
        'system' => "Decimal grading system used; examples: 1.0=98%+, 1.25=97–95%, 1.50=94–92%, 1.75=91–89%, 2.0=88–86%, 3.0=76–75%, 5.0<75% (Failed).",
        'special' => "NC, W, FW, FA definitions available."
    ]
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>SJC ScholarBot — Scholarship Assistant</title>
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<style>
  :root{
    --sjc-blue:#003399;
    --bg:#f4f6fb;
    --card:#ffffff;
    --muted:#6b7280;
  }
  *{box-sizing:border-box}
  body{
    margin:0; min-height:100vh; font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial;
    background:linear-gradient(180deg,#eef2ff 0%, #f7f9ff 100%);
    display:flex; flex-direction:column;
  }

  /* Header */
  header{
    background:var(--sjc-blue);
    color:#fff; padding:14px 20px; box-shadow:0 3px 10px rgba(0,0,0,.08);
    display:flex; align-items:center; gap:12px; justify-content:space-between;
  }
  .brand{ display:flex; gap:12px; align-items:center }
  .logo{ width:44px; height:44px; border-radius:8px; background:#fff; color:var(--sjc-blue); display:grid; place-items:center; font-weight:700; font-size:18px }
  .title{ font-weight:700; font-size:18px }
  .subtitle{ font-size:13px; opacity:.9; margin-top:2px; color:rgba(255,255,255,.9) }

  /* Layout */
  .wrap{ display:grid; grid-template-columns: 320px 1fr; gap:20px; padding:20px; align-items:start; width:100%; max-width:1200px; margin:18px auto; }
  /* Left: categories */
  .panel{ background:var(--card); border-radius:12px; padding:14px; box-shadow:0 6px 20px rgba(17,24,39,.06) }
  .panel h3{ margin:0 0 8px; font-size:15px; color:#111827 }
  .cat-list{ display:flex; flex-direction:column; gap:8px }
  .cat-btn{ text-align:left; padding:10px 12px; border-radius:10px; border:1px solid #eef2ff; background:linear-gradient(180deg,#fff,#fcfdff); cursor:pointer; font-weight:600; color:var(--sjc-blue) }
  .cat-btn:hover{ transform:translateY(-2px); box-shadow:0 6px 18px rgba(3,102,214,.06) }

  /* Main chat card */
  .chat-card{ background:var(--card); border-radius:12px; height:70vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 8px 30px rgba(2,6,23,.06) }
  .chat-header{ padding:14px 18px; border-bottom:1px solid #eef2ff; display:flex; justify-content:space-between; align-items:center }
  .chat-title{ font-weight:700; color:#0f172a }
  .chat-sub{ color:var(--muted); font-size:13px }

  .chat-body{ padding:16px; overflow:auto; flex:1; display:flex; flex-direction:column; gap:12px; background:linear-gradient(180deg,#fbfdff, #f8fbff) }

  .msg{ max-width:78%; padding:12px 14px; border-radius:12px; font-size:14px; line-height:1.45; box-shadow:0 2px 8px rgba(2,6,23,.04); word-break:break-word }
  .bot{ background:#ffffff; align-self:flex-start; color:#0f172a; border-radius:12px 12px 12px 4px }
  .user{ background:linear-gradient(90deg,#0d6efd,#0b5ed7); color:#fff; align-self:flex-end; border-radius:12px 12px 4px 12px }

  .meta{ font-size:12px; color:var(--muted); margin-top:6px }

  .chat-footer{ padding:12px; border-top:1px solid #eef2ff; display:flex; gap:10px; align-items:center; background:linear-gradient(180deg,#fff,#fbfdff) }
  .input{ flex:1; display:flex; gap:8px; align-items:center }
  .input textarea{ width:100%; padding:10px 12px; border-radius:10px; border:1px solid #e6eefc; resize:none; min-height:44px; font-family:inherit; font-size:14px }
  .send{ background:var(--sjc-blue); color:#fff; border:none; padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer }

  .typing{ display:flex; gap:6px; align-items:center }
  .dot{ width:8px; height:8px; background:#cbd5e1; border-radius:50%; animation:blink 1s infinite }
  .dot:nth-child(2){ animation-delay:.12s } .dot:nth-child(3){ animation-delay:.24s }
  @keyframes blink{ 0%,80%,100%{ opacity:.25 } 40%{ opacity:1 } }

  /* FAQ viewer */
  .faq-view{ max-height:44vh; overflow:auto; padding:8px; border-radius:8px }
  .faq-item{ padding:8px; border-radius:8px; border:1px solid #f0f4ff; background:#fff; margin-bottom:8px }
  .faq-item h4{ margin:0 0 6px; font-size:14px; color:#0f172a }
  .faq-item p{ margin:0; color:#334155; font-size:13px; white-space:pre-wrap }

  /* Responsive */
  @media (max-width: 900px){
    .wrap{ grid-template-columns: 1fr; padding:12px }
    .panel{ order:2 }
    .chat-card{ order:1; height:66vh }
  }
</style>
</head>
<body>

<header>
  <div class="brand">
    <div class="logo">SJC</div>
    <div>
      <div class="title">🎓 SJC ScholarBot</div>
      <div class="subtitle">Scholarship Assistant — ask about scholarships, requirements, and contacts</div>
    </div>
  </div>
  <div style="font-size:13px; color:rgba(255,255,255,.95)">Saint Joseph College — Maasin City</div>
</header>

<main class="wrap" role="main">
  <!-- Left panel: categories & quick info -->
  <aside class="panel" aria-label="Categories">
    <h3>Browse categories</h3>
    <div class="cat-list" id="categoryList"></div>

    <hr style="margin:12px 0; border:none; border-top:1px solid #f1f5ff"/>

    <h3>Quick contact</h3>
    <div style="font-size:13px; color:#334155; margin-top:8px">
      <div><strong>Director:</strong> Joenisa M. Hoyla</div>
      <div><strong>Staff:</strong> Lara O. Orais, Zaneth O. Mulig</div>
      <div style="margin-top:6px"><strong>Mobile:</strong> 0947-3855424 / 0927-0182744</div>
      <div><strong>Landline:</strong> (053) 570–8448 loc 110</div>
      <div style="margin-top:6px"><strong>FB:</strong> D’ JOSEPHIAN ESKOLARS & Student Affairs and Services Office</div>
    </div>
  </aside>

  <!-- Right: Chat -->
  <section class="chat-card" aria-label="Chat">
    <div class="chat-header">
      <div>
        <div class="chat-title">SJC ScholarBot</div>
        <div class="chat-sub">Type a question or click a category to browse FAQs</div>
      </div>
      <div style="font-size:13px; color:var(--muted)">No database required — local session only</div>
    </div>

    <div class="chat-body" id="chatBody" aria-live="polite">
      <!-- render history -->
      <?php
        if (!isset($_SESSION['chat_history'])) {
          $_SESSION['chat_history'] = [
            ['type'=>'bot','message'=>'👋 Hello! I’m your SJC ScholarBot. Ask about scholarship types, requirements, location, or contact info.','time'=>date('H:i')]
          ];
        }
        foreach ($_SESSION['chat_history'] as $m) {
          $cls = ($m['type']==='user') ? 'user' : 'bot';
          echo '<div><div class="msg '.$cls.'">'.($m['message']).'</div><div class="meta">'.htmlspecialchars($m['time']).'</div></div>';
        }
      ?>
    </div>

    <div class="chat-footer">
      <div style="flex:1" class="input">
        <textarea id="inputBox" placeholder="Type your question here (press Enter to send)"></textarea>
      </div>
      <div>
        <button class="send" id="sendBtn">Send</button>
      </div>
    </div>
  </section>
</main>

<script>
  // embed FAQ dataset for client browsing
  const FAQ = <?php echo json_encode($faq_for_js, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); ?>;

  // populate category list
  const categoryList = document.getElementById('categoryList');
  Object.keys(FAQ.categories).forEach(cat => {
    const btn = document.createElement('button');
    btn.className = 'cat-btn';
    btn.innerText = cat;
    btn.onclick = () => showCategory(cat);
    categoryList.appendChild(btn);
  });

  // show category in chat as clickable FAQ list
  function showCategory(cat){
    const block = FAQ.categories[cat];
    const items = block.items;
    let html = `<div class="faq-view">`;
    html += `<div class="faq-item"><h4>${cat}</h4><p>${block.description}</p></div>`;
    for (const [title, text] of Object.entries(items)) {
      html += `<div class="faq-item"><h4>${title}</h4><p>${text}</p><div style="margin-top:8px"><button onclick="sendQuick('${escapeJs(title)}')">Ask about this</button></div></div>`;
    }
    html += `</div>`;
    appendBot(html);
  }

  // escape for JS->server text
  function escapeJs(s){
    return s.replace(/'/g,"\\'").replace(/"/g,'\\"');
  }

  // Chat functions
  const chatBody = document.getElementById('chatBody');
  const inputBox = document.getElementById('inputBox');
  const sendBtn = document.getElementById('sendBtn');

  sendBtn.addEventListener('click', onSend);
  inputBox.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      onSend();
    }
  });

  function appendUser(text){
    const wrap = document.createElement('div');
    const userDiv = document.createElement('div');
    userDiv.className = 'msg user';
    userDiv.innerText = text;
    const meta = document.createElement('div'); meta.className='meta'; meta.innerText = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    wrap.appendChild(userDiv); wrap.appendChild(meta);
    chatBody.appendChild(wrap);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function appendBot(htmlContent){
    const wrap = document.createElement('div');
    const botDiv = document.createElement('div');
    botDiv.className = 'msg bot';
    // allow limited HTML (we produced it), so set innerHTML
    botDiv.innerHTML = htmlContent;
    const meta = document.createElement('div'); meta.className='meta'; meta.innerText = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    wrap.appendChild(botDiv); wrap.appendChild(meta);
    chatBody.appendChild(wrap);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function sendQuick(q){
    inputBox.value = q;
    onSend();
  }

  // typing indicator
  function showTyping(){
    const t = document.createElement('div'); t.id='typing';
    t.innerHTML = `<div class="msg bot"><div class="typing"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div>`;
    chatBody.appendChild(t); chatBody.scrollTop = chatBody.scrollHeight;
  }
  function removeTyping(){ const t=document.getElementById('typing'); if(t) t.remove(); }

  async function onSend(){
    const text = inputBox.value.trim();
    if (!text) return;
    appendUser(text);
    inputBox.value = '';
    showTyping();

    try {
      const form = new URLSearchParams();
      form.append('message', text);
      const res = await fetch('', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: form });
      const data = await res.json();
      removeTyping();
      if (data && data.ok) {
        // server returns reply (as plain text). We allow minimal HTML from server
        appendBot(data.reply.replace(/\n/g,'<br>'));
      } else if (data && data.reply) {
        appendBot(data.reply);
      } else {
        appendBot('⚠️ Unexpected response from server.');
      }
    } catch (err) {
      removeTyping();
      appendBot('⚠️ Network error. Please try again.');
    }
  }

  // initial scroll to bottom
  chatBody.scrollTop = chatBody.scrollHeight;
</script>
</body>
</html>
