<?php
// --- SMS Blast Page ---
// Combines your Tailwind UI with PHP to load residents from DB into the recipients UI
// and post the campaign to a backend handler (sms_blast_handler.php).

session_start();
$page_title = 'SMS Blast';
require_once 'includes/header.php'; // must define $link (mysqli)

// Role-based access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Barangay Secretary') {
    header('Location: index.php');
    exit;
}

// Simple HTML escaper if your project doesn't already define html_escape()
if (!function_exists('html_escape')) {
    function html_escape($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

 if (isset($_GET['status'])): ?>
    <div id="statusAlert" 
         class="alert 
         <?php echo ($_GET['status'] === 'success') ? 'alert-success' : 'alert-danger'; ?>">
         
        <?php if ($_GET['status'] === 'success'): ?>
             Success! SentroGlad has delivered a message to residents.
        <?php elseif ($_GET['status'] === 'error_message_empty'): ?>
            ⚠️ Please enter a message before sending.
        <?php elseif ($_GET['status'] === 'error_no_recipients'): ?>
            ⚠️ Please select at least one recipient.
        <?php elseif ($_GET['status'] === 'error_db_prepare'): ?>
            ⚠️ Database error. Please try again later.
        <?php else: ?>
            ⚠️ An unexpected error occurred.
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            const alertBox = document.getElementById('statusAlert');
            if (alertBox) {
                alertBox.style.transition = "opacity 1s";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 1000);
            }
        }, 5000);
    </script>
<?php endif; ?>


<?php 
$total_recipients = 0;
$count_sql = "SELECT COUNT(id) AS total_recipients
              FROM residents
              WHERE contact_number IS NOT NULL
                AND contact_number != ''
                AND status = 'Active'";
if ($res = mysqli_query($link, $count_sql)) {
    if ($row = mysqli_fetch_assoc($res)) {
        $total_recipients = (int)$row['total_recipients'];
    }
    mysqli_free_result($res);
}

// --- Fetch Puroks ---
$puroks = [];
$purok_sql = "SELECT id, purok_name, purok_leader FROM puroks ORDER BY purok_name ASC";
if ($purok_res = mysqli_query($link, $purok_sql)) {
    while ($p = mysqli_fetch_assoc($purok_res)) {
        // Get count of active residents with contact numbers for this purok
        $count_sql = "SELECT COUNT(id) as resident_count
                      FROM residents
                      WHERE purok_id = ?
                        AND contact_number IS NOT NULL
                        AND contact_number != ''
                        AND status = 'Active'";
        if ($count_stmt = mysqli_prepare($link, $count_sql)) {
            mysqli_stmt_bind_param($count_stmt, "i", $p['id']);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $count_row = mysqli_fetch_assoc($count_result);
            $p['resident_count'] = (int)$count_row['resident_count'];
            mysqli_stmt_close($count_stmt);
        } else {
            $p['resident_count'] = 0;
        }
        $puroks[] = $p;
    }
    mysqli_free_result($purok_res);
}

// Optionally a CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo html_escape($page_title); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = { theme: { extend: { colors: { primary: '#5D5CDE', 'primary-dark': '#4C4BC9' } } } };
  </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold text-center mb-2">SMS Blast Manager</h1>
    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">Manage contacts, create templates, and send SMS campaigns</p>

    <?php if ($message_feedback): ?>
      <div class="mb-4"><?php echo $message_feedback; ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <nav class="flex space-x-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 mb-8">
      <button type="button" onclick="switchTab('contacts')" id="tab-contacts" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md"> <i class="fas fa-address-book mr-2"></i>Contacts </button>
      <button type="button" onclick="switchTab('templates')" id="tab-templates" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md"> <i class="fas fa-file-text mr-2"></i>Templates </button>
      <button type="button" onclick="switchTab('compose')" id="tab-compose" class="tab-button flex-1 px-4 py-2 text-sm font-medium rounded-md"> <i class="fas fa-paper-plane mr-2"></i>Compose </button>
      
    </nav>

    <!-- Contacts Tab -->
    <div id="contacts-tab" class="tab-content">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Purok Overview</h2>
          <span class="text-sm text-gray-500 dark:text-gray-400">Total eligible contacts: <?php echo number_format($total_recipients); ?></span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Purok Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Leader</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Eligible Residents</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <?php if (empty($puroks)): ?>
                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No puroks found.</td></tr>
              <?php else: ?>
                <?php foreach ($puroks as $p): ?>
                  <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3 text-sm font-medium"><?php echo html_escape($p['purok_name']); ?></td>
                    <td class="px-4 py-3 text-sm"><?php echo html_escape($p['purok_leader'] ?? 'Not assigned'); ?></td>
                    <td class="px-4 py-3 text-sm">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $p['resident_count'] > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'; ?>">
                        <?php echo number_format($p['resident_count']); ?> residents
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Templates Tab -->
    <div id="templates-tab" class="tab-content hidden">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold">Message Templates</h2>
          <button type="button" onclick="showCreateTemplateModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg"> <i class="fas fa-plus mr-2"></i>Create Template </button>
        </div>
        <div id="templates-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
      </div>
    </div>

    <!-- Compose Tab -->
    <div id="compose-tab" class="tab-content hidden">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold mb-6">Compose SMS Blast</h2>

        <form id="blast-form" method="POST" action="sms_blast_handler.php" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />

          <!-- Left: Message -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2">Campaign Name</label>
              <input type="text" name="campaign_name" id="campaign-name" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Select Template (Optional)</label>
              <select id="template-select" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                <option value="">Select a template...</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Message</label>
              <textarea name="message" id="message-content" rows="6" required oninput="updateCharacterCount()" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 resize-none"></textarea>
              <div class="flex justify-between text-sm mt-1 text-gray-500">
                <span>Characters: <span id="char-count">0</span>/160</span>
                <span>Parts: <span id="message-parts">0</span></span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Send Schedule</label>
              <div class="flex space-x-4">
                <label class="flex items-center"><input type="radio" name="schedule" value="now" checked class="mr-2">Send Now</label>
                <label class="flex items-center"><input type="radio" name="schedule" value="scheduled" class="mr-2">Schedule Later</label>
              </div>
              <input type="datetime-local" name="schedule_time" id="schedule-time" disabled class="mt-2 w-full px-4 py-2 border rounded-lg dark:bg-gray-700" />
            </div>
          </div>

          <!-- Right: Recipients -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2">Recipients</label>
              <div class="space-y-2">
                <label class="flex items-center"><input type="radio" name="recipients_mode" value="all" checked class="mr-2"> All Residents (<?php echo number_format($total_recipients); ?>)</label>
                <label class="flex items-center"><input type="radio" name="recipients_mode" value="purok" class="mr-2"> Selected Puroks</label>
              </div>
            </div>

            <div id="purok-selection">
              <label class="block text-sm font-medium mb-2">Choose Puroks</label>
              <select id="purok-select" name="purok_ids[]" multiple size="10" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                <?php foreach ($puroks as $purok): ?>
                  <option value="<?php echo (int)$purok['id']; ?>">
                    <?php echo html_escape($purok['purok_name']); ?> (<?php echo $purok['resident_count']; ?> residents)
                    <?php if (!empty($purok['purok_leader'])): ?>
                      - Leader: <?php echo html_escape($purok['purok_leader']); ?>
                    <?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) / Cmd (Mac) to select multiple puroks.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
              <h4 class="font-medium mb-2">Campaign Summary</h4>
              <div class="space-y-1 text-sm">
                <div>Recipients: <span id="summary-recipients">0</span></div>
                <div>Estimated Cost: ₱<span id="estimated-cost">0.00</span></div>
                <div>Send Time: <span id="summary-send-time">Immediately</span></div>
              </div>
            </div>

            <button type="submit" id="send-button" class="w-full bg-primary hover:bg-primary-dark text-white py-3 rounded-lg font-medium">
              <i class="fas fa-paper-plane mr-2"></i>Send SMS Blast
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Analytics Tab -->
    

  <!-- Template Modal -->
  <div id="create-template-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Create Message Template</h3>
        <form id="create-template-form" class="space-y-4" onsubmit="event.preventDefault(); createTemplate();">
          <div>
            <label class="block text-sm font-medium mb-1">Template Name</label>
            <input type="text" id="template-name" required class="w-full px-3 py-2 border rounded dark:bg-gray-700">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Message</label>
            <textarea id="template-message" rows="4" required class="w-full px-3 py-2 border rounded dark:bg-gray-700 resize-none"></textarea>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeCreateTemplateModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-primary text-white hover:bg-primary-dark rounded">Create Template</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Dark mode sync
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) { document.documentElement.classList.add('dark'); }

    // In-memory stores for templates only (contacts are server-driven)
    let templates = [];

    document.addEventListener('DOMContentLoaded', function() {
      switchTab('compose');
      updateTemplateSelect();
      updateRecipientSelection();

      // events for schedule radios
      document.querySelectorAll('input[name="schedule"]').forEach(r => r.addEventListener('change', function(){
        document.getElementById('schedule-time').disabled = (this.value === 'now');
        document.getElementById('summary-send-time').textContent = this.value === 'now' ? 'Immediately' : (document.getElementById('schedule-time').value || 'Scheduled');
      }));

      // recipients mode change
      document.querySelectorAll('input[name="recipients_mode"]').forEach(r => r.addEventListener('change', updateRecipientSelection));

      // purok selection change
      document.getElementById('purok-select').addEventListener('change', updateCostEstimate);

      // schedule time change
      document.getElementById('schedule-time').addEventListener('input', function(){
        document.getElementById('summary-send-time').textContent = this.value ? this.value : 'Immediately';
      });

      // cost + counts on load
      updateCharacterCount();
    });

    // Tab switching
    function switchTab(tabName) {
      document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
      document.getElementById(tabName + '-tab').classList.remove('hidden');
      document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('bg-primary','text-white');
        btn.classList.add('text-gray-500','hover:text-gray-700');
      });
      const active = document.getElementById('tab-' + tabName);
      active.classList.add('bg-primary','text-white');
      active.classList.remove('text-gray-500');
    }

    // Template modal
    function showCreateTemplateModal(){ const m = document.getElementById('create-template-modal'); m.classList.remove('hidden'); m.classList.add('flex'); }
    function closeCreateTemplateModal(){ const m = document.getElementById('create-template-modal'); m.classList.add('hidden'); m.classList.remove('flex'); document.getElementById('create-template-form').reset(); }

    function createTemplate() {
  const name = document.getElementById('template-name').value.trim();
  const message = document.getElementById('template-message').value.trim();
  if (!name || !message) return;

  const newTemplate = { id: Date.now(), name, message, created: new Date().toISOString() };
  templates.push(newTemplate);

  // Save to localStorage
  localStorage.setItem('sms_templates', JSON.stringify(templates));

  updateTemplatesList();
  updateTemplateSelect();
  closeCreateTemplateModal();
}

// On page load, restore
document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('sms_templates');
  if (saved) templates = JSON.parse(saved);
  updateTemplatesList();
  updateTemplateSelect();
});

    function deleteTemplate(id) {
      templates = templates.filter(t => t.id !== id);
      updateTemplatesList();
      updateTemplateSelect();
    }

    function useTemplate(id){
      const t = templates.find(x => x.id === id);
      if (!t) return;
      document.getElementById('message-content').value = t.message;
      updateCharacterCount();
      switchTab('compose');
    }

    function updateTemplatesList() {
      const container = document.getElementById('templates-list');
      if (!templates.length) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500"><i class="fas fa-file-text text-4xl mb-4 opacity-50"></i><p>No templates created yet. Create your first template to get started!</p></div>`;
        return;
      }
      container.innerHTML = templates.map(t => `
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition-shadow">
          <div class="flex justify-between items-start mb-2">
            <h3 class="font-medium truncate">${t.name}</h3>
            <button type="button" onclick="deleteTemplate(${t.id})" class="text-red-500 hover:text-red-700 ml-2"><i class="fas fa-trash text-sm"></i></button>
          </div>
          <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-3">${t.message}</p>
          <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
            <span>Created: ${new Date(t.created).toLocaleDateString()}</span>
            <button type="button" onclick="useTemplate(${t.id})" class="text-primary hover:text-primary-dark">Use Template</button>
          </div>
        </div>`).join('');
    }

    function updateTemplateSelect() {
      const select = document.getElementById('template-select');
      select.innerHTML = '<option value="">Select a template...</option>' + templates.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
      select.addEventListener('change', function(){
        const t = templates.find(x => String(x.id) === this.value);
        if (t) { document.getElementById('message-content').value = t.message; updateCharacterCount(); }
      });
    }

    // Composition helpers
    function updateCharacterCount() {
      const msg = document.getElementById('message-content').value || '';
      const chars = msg.length;
      const parts = chars > 0 ? Math.ceil(chars / 160) : 0;
      document.getElementById('char-count').textContent = chars;
      document.getElementById('message-parts').textContent = parts;
      updateCostEstimate();
    }

    function updateRecipientSelection(){
      const mode = document.querySelector('input[name="recipients_mode"]:checked').value;
      const purokSelectBox = document.getElementById('purok-select');
      purokSelectBox.disabled = (mode !== 'purok');
      updateCostEstimate();
    }

    function updateCostEstimate(){
      const mode = document.querySelector('input[name="recipients_mode"]:checked').value;
      const allCount = <?php echo (int)$total_recipients; ?>;
      let recipientCount = 0;

      if (mode === 'all') {
        recipientCount = allCount;
      } else if (mode === 'purok') {
        // Calculate total recipients from selected puroks
        const selectedPuroks = Array.from(document.getElementById('purok-select').selectedOptions);
        recipientCount = selectedPuroks.reduce((total, option) => {
          // Extract resident count from option text (format: "Purok Name (X residents)")
          const match = option.textContent.match(/\((\d+) residents\)/);
          return total + (match ? parseInt(match[1], 10) : 0);
        }, 0);
      }

      const parts = parseInt(document.getElementById('message-parts').textContent, 10) || 0;
      const cost = recipientCount * parts * 0.56; // Example: ₱0.56 per segment (adjust to your gateway)
      document.getElementById('summary-recipients').textContent = recipientCount;
      document.getElementById('estimated-cost').textContent = cost.toFixed(2);
      if (document.getElementById('total-cost-last')) {
        document.getElementById('total-cost-last').textContent = cost.toFixed(2);
      }
    }

    // Prevent submit if selected mode without IDs
    document.getElementById('blast-form').addEventListener('submit', function(e){
      const mode = document.querySelector('input[name="recipients_mode"]:checked').value;
      const msg = (document.getElementById('message-content').value || '').trim();
      if (!msg) { e.preventDefault(); alert('Message cannot be empty.'); return; }
      if (mode === 'purok') {
        const count = Array.from(document.getElementById('purok-select').selectedOptions).length;
        if (count === 0) { e.preventDefault(); alert('Please select at least one purok.'); return; }
      }
    });
  </script>
</body>
</html>
