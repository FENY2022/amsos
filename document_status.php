<?php
require_once 'db.php';
// IMPORTANT: Include the Pusher SDK to use the trigger
require_once __DIR__ . '/vendor/autoload.php';

// Retrieve token from public URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

$document = null;
$actions = [];
$error_message = "";

if (!empty($token)) {
    // 1. Fetch Document Details matching your `documents` table
    $sql_doc = "SELECT d.*, 
                       COALESCE(u.first_name, 'Unknown') AS f_name,
                       COALESCE(u.last_name, 'User') AS l_name
                FROM documents d
                LEFT JOIN users u ON d.initiator_id = u.user_id
                WHERE d.token = ? LIMIT 1";
                
    if ($stmt = $conn->prepare($sql_doc)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $document = $result->fetch_assoc();
            
            // 2. Fetch Audit Trail / Document Actions matching your `document_actions` table
            $sql_actions = "SELECT da.action, da.message, 
                                   da.created_at AS action_date,
                                   COALESCE(u.first_name, 'System') AS actor_f_name,
                                   COALESCE(u.last_name, '') AS actor_l_name
                            FROM document_actions da
                            LEFT JOIN users u ON da.user_id = u.user_id
                            WHERE da.doc_id = ?
                            ORDER BY da.created_at DESC";
                            
            if ($stmt_act = $conn->prepare($sql_actions)) {
                $stmt_act->bind_param("i", $document['doc_id']);
                $stmt_act->execute();
                $res_actions = $stmt_act->get_result();
                while ($row = $res_actions->fetch_assoc()) {
                    $actions[] = $row;
                }
                $stmt_act->close();
            }

            // ==========================================
            // START: PUSHER LIVE TIMELINE TRIGGER
            // ==========================================
            // This will broadcast an event every time this page is loaded/viewed
            try {
                if (class_exists('Pusher\Pusher')) {
                    $options = array(
                        'cluster' => 'ap3',
                        'useTLS' => true
                    );
                    
                    // IMPORTANT: Replace with your actual credentials
                    $pusher = new Pusher\Pusher(
                        '98d5a35431a9fefb0370', // Your App Key
                        'd4c2ad94090a33d8abaf',      // Your App Secret
                        '2129830',          // Your App ID
                        $options
                    );

                    date_default_timezone_set('Asia/Manila'); 

                    // Payload indicating the document was viewed
                    $pusher_data = [
                        'action'     => 'Viewed',
                        'message'    => 'Document status was checked via tracking portal.',
                        'actor_name' => 'Guest / Public',
                        'status'     => $document['status'],
                        'dateStr'    => date('M d, Y'),
                        'timeStr'    => date('h:i A')
                    ];

                    // Trigger the specific document channel
                    $pusher->trigger('document-' . $document['doc_id'], 'new-action', $pusher_data);
                }
            } catch (Exception $e) {
                error_log("Pusher Error in document_status: " . $e->getMessage());
            }
            // ==========================================
            // END: PUSHER LIVE TIMELINE TRIGGER
            // ==========================================

        } else {
            $error_message = "Document not found or the tracking token is invalid.";
        }
        $stmt->close();
    } else {
        $error_message = "Database error: Unable to fetch document.";
    }
} else {
    $error_message = "No tracking token provided. Please scan a valid QR code.";
}

// Helper function to color-code and assign icons based on the action word
function getActionGraphic($actionText) {
    $text = strtolower($actionText);
    
    // Default (Forwarded / Received / Route)
    $graphic = [
        'bg' => 'bg-indigo-500', 
        'icon_color' => 'text-white',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>'
    ];
    
    // Added "viewed" to get an icon when the tracking page triggers its event
    if (strpos($text, 'viewed') !== false) {
        $graphic['bg'] = 'bg-cyan-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    } elseif (strpos($text, 'draft') !== false) {
        $graphic['bg'] = 'bg-gray-400';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
    } elseif (strpos($text, 'submit') !== false || strpos($text, 'forward') !== false || strpos($text, 'release') !== false) {
        $graphic['bg'] = 'bg-blue-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>';
    } elseif (strpos($text, 'received') !== false) {
        $graphic['bg'] = 'bg-teal-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>';
    } elseif (strpos($text, 'email') !== false) {
        $graphic['bg'] = 'bg-purple-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>';
    } elseif (strpos($text, 'complet') !== false || strpos($text, 'approv') !== false || strpos($text, 'sign') !== false) {
        $graphic['bg'] = 'bg-emerald-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
    } elseif (strpos($text, 'return') !== false || strpos($text, 'reject') !== false) {
        $graphic['bg'] = 'bg-red-500';
        $graphic['icon'] = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l-2-2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
    }
    
    return $graphic;
}

// Helper to determine status badge color
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        case 'draft': return 'bg-gray-100 text-gray-800 border-gray-200';
        case 'returned': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-blue-100 text-blue-800 border-blue-200'; // Review, Signing, etc.
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D-Sign Document Tracking Portal</title>
    
    <link rel="icon" href="logo/ddtms.ico" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        .timeline-container { position: relative; }
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 2rem; /* Adjusted for mobile */
            width: 2px;
            background-color: #e2e8f0;
        }
        @media (min-width: 640px) {
            .timeline-container::before {
                left: 10rem; /* Moves line to the right for dates on desktop */
            }
        }
        
        /* Message bubble pointer */
        .bubble-pointer::before {
            content: '';
            position: absolute;
            top: 1rem;
            left: -0.5rem;
            width: 1rem;
            height: 1rem;
            background-color: #f8fafc;
            border-left: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            transform: rotate(45deg);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center py-6 px-4 sm:px-6 lg:px-8">

    <div class="max-w-5xl w-full">
        
        <div class="w-full flex flex-col items-center justify-center mb-10 mt-4">
            <div class="relative flex items-center justify-center bg-white p-5 rounded-full shadow-lg border border-slate-100 mb-6 ring-4 ring-slate-50 transition-transform duration-300 hover:scale-105">
                <img src="logo/logo.png" alt="System Logo" class="w-24 h-24 object-contain" onerror="this.src='logo/ddtms.ico'">
            </div>
            
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">D-Sign Document Tracking Portal</h1>
            <p class="mt-2 text-sm text-slate-500 text-center">View the real-time status and audit trail of the document.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Access Denied</h3>
                <p class="mt-2 text-slate-500"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php elseif ($document): ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 space-y-6 sticky top-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Document Profile</h2>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-900 leading-tight mb-4">
                                <?php echo htmlspecialchars($document['title']); ?>
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Current Status</p>
                                    <span id="doc-status-badge" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border <?php echo getStatusBadge($document['status']); ?>">
                                        <span class="w-1.5 h-1.5 rounded-full mr-2 bg-current opacity-75"></span>
                                        <span id="doc-status-text"><?php echo htmlspecialchars(strtoupper($document['status'])); ?></span>
                                    </span>
                                </div>
                                
                                <div class="pt-2 border-t border-slate-100">
                                    <p class="text-xs text-slate-500 font-medium">Document Type</p>
                                    <p class="text-sm text-slate-900 font-medium mt-0.5"><?php echo htmlspecialchars($document['doc_type']); ?></p>
                                </div>
                                
                                <div class="pt-2 border-t border-slate-100">
                                    <p class="text-xs text-slate-500 font-medium">Classification</p>
                                    <p class="text-sm text-slate-900 font-medium mt-0.5"><?php echo htmlspecialchars($document['classification']); ?></p>
                                </div>

                                <div class="pt-2 border-t border-slate-100">
                                    <p class="text-xs text-slate-500 font-medium">Initiator</p>
                                    <p class="text-sm text-slate-900 font-medium mt-0.5">
                                        <?php echo htmlspecialchars(strtoupper($document['f_name'] . ' ' . $document['l_name'])); ?>
                                    </p>
                                </div>
                                
                                <?php if (!empty($document['Station'])): ?>
                                <div class="pt-2 border-t border-slate-100">
                                    <p class="text-xs text-slate-500 font-medium">Origin Station</p>
                                    <p class="text-sm text-slate-900 font-medium mt-0.5"><?php echo htmlspecialchars($document['Station']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-mono">ID: #<?php echo str_pad($document['doc_id'], 6, '0', STR_PAD_LEFT); ?></span>
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-bold text-slate-900 mb-8 flex items-center border-b border-slate-100 pb-4">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Activity Timeline
                            <span id="real-time-indicator" class="ml-auto text-xs font-semibold text-emerald-500 flex items-center hidden">
                                <span class="relative flex h-2 w-2 mr-1">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Live
                            </span>
                        </h2>

                        <div id="timeline-wrapper">
                            <?php if (empty($actions)): ?>
                                <div id="empty-state" class="text-center py-10">
                                    <p class="text-sm text-slate-500">No actions recorded yet.</p>
                                </div>
                            <?php else: ?>
                                <div id="timeline-container" class="timeline-container">
                                    <?php foreach ($actions as $index => $action): 
                                        $graphic = getActionGraphic($action['action']);
                                        $timestamp = strtotime($action['action_date']);
                                        $isLatest = ($index === 0);
                                        
                                        // Separate Date and Time for the desktop layout
                                        $dateStr = date('M d, Y', $timestamp);
                                        $timeStr = date('h:i A', $timestamp);
                                    ?>
                                        <div class="relative flex items-start mb-10 group timeline-item">
                                            
                                            <div class="hidden sm:block w-32 flex-shrink-0 text-right pr-6 pt-1">
                                                <div class="text-sm font-bold text-slate-900"><?php echo $dateStr; ?></div>
                                                <div class="text-xs font-medium text-slate-500"><?php echo $timeStr; ?></div>
                                            </div>

                                            <div class="absolute left-0 sm:static flex items-center justify-center w-8 h-8 ml-4 sm:ml-0 rounded-full <?php echo $graphic['bg']; ?> ring-4 ring-white z-10 shadow-sm <?php echo $isLatest ? 'ring-blue-50 timeline-ring' : ''; ?>">
                                                <svg class="w-4 h-4 text-white relative z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $graphic['icon']; ?></svg>
                                            </div>

                                            <div class="ml-16 sm:ml-6 w-full">
                                                <div class="sm:hidden mb-1">
                                                    <span class="text-xs font-semibold text-slate-500"><?php echo $dateStr . ' • ' . $timeStr; ?></span>
                                                </div>

                                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm relative bubble-pointer timeline-bubble <?php echo $isLatest ? 'border-blue-200 bg-blue-50' : ''; ?>">
                                                    <h4 class="text-base font-bold timeline-title <?php echo $isLatest ? 'text-blue-900' : 'text-slate-900'; ?>">
                                                        <?php echo htmlspecialchars($action['action']); ?>
                                                    </h4>
                                                    <p class="text-sm text-slate-600 mt-0.5">
                                                        Processed by <span class="font-semibold text-slate-800"><?php echo htmlspecialchars(ucwords(strtolower($action['actor_f_name'] . ' ' . $action['actor_l_name']))); ?></span>
                                                    </p>
                                                    
                                                    <?php if (!empty(trim($action['message']))): ?>
                                                        <div class="mt-3 bg-white rounded-lg p-3 border border-slate-200 text-sm text-slate-700 italic">
                                                            "<?php echo nl2br(htmlspecialchars($action['message'])); ?>"
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
        <div class="mt-12 mb-8 text-center border-t border-slate-200 pt-6">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Protected by Document Tracking System</p>
            <p class="text-xs text-slate-400 mt-1">Internal access requires authentication.</p>
        </div>
    </div>

    <?php if ($document && !empty($document['doc_id'])): ?>
    <script>
        // JS Equivalents of your PHP helpers to generate styling dynamically
        function getActionGraphicJS(text) {
            let t = text.toLowerCase();
            let graphic = {
                bg: 'bg-indigo-500', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>'
            };
            
            if (t.includes('viewed')) {
                graphic.bg = 'bg-cyan-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            } else if (t.includes('draft')) {
                graphic.bg = 'bg-gray-400';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
            } else if (t.includes('submit') || t.includes('forward') || t.includes('release')) {
                graphic.bg = 'bg-blue-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>';
            } else if (t.includes('received')) {
                graphic.bg = 'bg-teal-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>';
            } else if (t.includes('email')) {
                graphic.bg = 'bg-purple-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>';
            } else if (t.includes('complet') || t.includes('approv') || t.includes('sign')) {
                graphic.bg = 'bg-emerald-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            } else if (t.includes('return') || t.includes('reject')) {
                graphic.bg = 'bg-red-500';
                graphic.icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l-2-2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            }
            return graphic;
        }

        function getStatusBadgeJS(status) {
            let s = status.toLowerCase();
            if(s === 'completed') return 'bg-green-100 text-green-800 border-green-200';
            if(s === 'draft') return 'bg-gray-100 text-gray-800 border-gray-200';
            if(s === 'returned') return 'bg-red-100 text-red-800 border-red-200';
            return 'bg-blue-100 text-blue-800 border-blue-200';
        }

        // Initialize Pusher
        var pusher = new Pusher('98d5a35431a9fefb0370', {
            cluster: 'ap3',
            forceTLS: true
        });

        // Show "Live" indicator to let users know it's connected
        pusher.connection.bind('connected', function() {
            document.getElementById('real-time-indicator').classList.remove('hidden');
        });

        // Subscribe to this specific document's channel
        var channel = pusher.subscribe('document-<?php echo $document['doc_id']; ?>');
        
        channel.bind('new-action', function(data) {
            // data requires: { action: "...", message: "...", actor_name: "...", status: "...", dateStr: "...", timeStr: "..." }

            // 1. Update the Status Badge
            const badgeSpan = document.getElementById('doc-status-badge');
            const statusText = document.getElementById('doc-status-text');
            if (badgeSpan && statusText && data.status) {
                // Remove existing classes to reset them
                badgeSpan.className = `inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border ${getStatusBadgeJS(data.status)}`;
                statusText.innerText = data.status.toUpperCase();
            }

            // 2. Clear old "latest" stylings in the timeline
            const oldRings = document.querySelectorAll('.timeline-ring');
            oldRings.forEach(el => el.classList.remove('ring-blue-50'));
            
            const oldBubbles = document.querySelectorAll('.timeline-bubble');
            oldBubbles.forEach(el => el.classList.remove('border-blue-200', 'bg-blue-50'));
            
            const oldTitles = document.querySelectorAll('.timeline-title');
            oldTitles.forEach(el => {
                el.classList.remove('text-blue-900');
                el.classList.add('text-slate-900');
            });

            // 3. Build the new timeline HTML block
            const graphic = getActionGraphicJS(data.action);
            
            let messageHtml = '';
            if (data.message && data.message.trim() !== '') {
                // Escape HTML tags to prevent XSS
                let safeMessage = data.message.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                messageHtml = `<div class="mt-3 bg-white rounded-lg p-3 border border-slate-200 text-sm text-slate-700 italic">"${safeMessage}"</div>`;
            }

            const newTimelineHtml = `
                <div class="relative flex items-start mb-10 group timeline-item animate-[fadeIn_0.5s_ease-in-out]">
                    <div class="hidden sm:block w-32 flex-shrink-0 text-right pr-6 pt-1">
                        <div class="text-sm font-bold text-slate-900">${data.dateStr}</div>
                        <div class="text-xs font-medium text-slate-500">${data.timeStr}</div>
                    </div>

                    <div class="absolute left-0 sm:static flex items-center justify-center w-8 h-8 ml-4 sm:ml-0 rounded-full ${graphic.bg} ring-4 ring-white z-10 shadow-sm ring-blue-50 timeline-ring">
                        <svg class="w-4 h-4 text-white relative z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">${graphic.icon}</svg>
                    </div>

                    <div class="ml-16 sm:ml-6 w-full">
                        <div class="sm:hidden mb-1">
                            <span class="text-xs font-semibold text-slate-500">${data.dateStr} &bull; ${data.timeStr}</span>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm relative bubble-pointer timeline-bubble border-blue-200 bg-blue-50">
                            <h4 class="text-base font-bold timeline-title text-blue-900">
                                ${data.action}
                            </h4>
                            <p class="text-sm text-slate-600 mt-0.5">
                                Processed by <span class="font-semibold text-slate-800">${data.actor_name}</span>
                            </p>
                            ${messageHtml}
                        </div>
                    </div>
                </div>
            `;

            // 4. Inject the new HTML
            let timelineWrapper = document.getElementById('timeline-wrapper');
            let emptyState = document.getElementById('empty-state');
            
            // If the timeline was previously empty
            if (emptyState) {
                timelineWrapper.innerHTML = `<div id="timeline-container" class="timeline-container">${newTimelineHtml}</div>`;
            } else {
                let timelineContainer = document.getElementById('timeline-container');
                timelineContainer.insertAdjacentHTML('afterbegin', newTimelineHtml);
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>