<?php
require_once 'db.php';

$doc_data = null;
$doc_files = [];
$attachments = []; // Added to hold attachments
$error_message = null;
$salt = 'DDTMS_V1_SECURE'; // Ensure this perfectly matches the salt in the other files!

if (!isset($_GET['token']) && isset($_GET['doc_id'])) {
    $legacy_doc_id = filter_input(INPUT_GET, 'doc_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($legacy_doc_id) {
        $raw_token = $legacy_doc_id . '|' . hash('sha256', $legacy_doc_id . $salt);
        $secure_token = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($raw_token));

        header('Location: verify_doc.php?token=' . urlencode($secure_token), true, 302);
        exit();
    }

    $error_message = "Invalid document verification request.";
}

if ($error_message === null && isset($_GET['token'])) {
    
    // Reverse the URL-safe encoding safely before decrypting
    $b64 = str_replace(['-', '_'], ['+', '/'], $_GET['token']);
    $decoded = base64_decode($b64);

    if ($decoded && strpos($decoded, '|') !== false) {
        list($id_part, $hash_part) = explode('|', $decoded);
        
        // Cryptographically verify the hash matches our ID to ensure it wasn't tampered with
        if (hash_equals(hash('sha256', $id_part . $salt), $hash_part)) {
            $doc_id = intval($id_part);

        // Fetch document details along with the initiator's name
        // We strictly check that status = 'Completed' so drafts can't be publicly viewed
        $query = "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS initiator_name, u.email AS initiator_email
          FROM documents d
          LEFT JOIN users u ON d.initiator_id = u.user_id
          WHERE d.doc_id = ? AND d.status IN ('Completed', 'Archived')";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $doc_data = $result->fetch_assoc();

                // Fetch attached final files ONLY (Filter for filenames or paths containing "signed_")
                // --- Fetch ONLY the single most recent signed file ---
                $f_query = "SELECT * FROM document_files 
                            WHERE doc_id = ? 
                            AND (filepath LIKE '%/signed_%' OR filepath LIKE 'signed_%' OR filename LIKE 'signed_%')
                            ORDER BY created_at DESC 
                            LIMIT 1"; 

                $f_stmt = $conn->prepare($f_query);
                $f_stmt->bind_param("i", $doc_id);
                $f_stmt->execute();
                $files_result = $f_stmt->get_result();

                // Clear array and add only the latest file
                $doc_files = []; 
                if($f = $files_result->fetch_assoc()) {
                    $doc_files[] = $f;
                }

                // --- Fetch Supporting Documents / Attachments ---
                // Looks for files that start with "[Attachment] - "
                $a_query = "SELECT * FROM document_files 
                            WHERE doc_id = ? 
                            AND filename LIKE '[Attachment] - %'
                            ORDER BY version DESC, file_id ASC";
                
                $a_stmt = $conn->prepare($a_query);
                $a_stmt->bind_param("i", $doc_id);
                $a_stmt->execute();
                $att_result = $a_stmt->get_result();

                while($att = $att_result->fetch_assoc()) {
                    $attachments[] = $att;
                }

            } else {
                $error_message = "This document does not exist, has not been fully finalized, or is not available for public verification.";
            }
        } else {
            // Deliberately generic error so attackers don't know the exact reason it failed
            $error_message = "Invalid or tampered verification token.";
        }
    } else {
        $error_message = "Malformed or unreadable verification token.";
    }
} elseif ($error_message === null) {
    $error_message = "No secure verification token provided in the URL.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification | D-Sign</title>
    <link rel="icon" type="image/png" href="logo/ddtms.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.4); }
        .verify-pulse { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        /* Hide scrollbar for the modal iframe to look cleaner */
        #docIframe::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-8 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">

    <div class="max-w-2xl w-full">
        <?php if ($doc_data): ?>
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-3 bg-white px-5 py-2.5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="bg-slate-900 p-1.5 rounded-lg">
                        <i class="fas fa-check-double text-emerald-400 text-sm"></i>
                    </div>
                    <span class="text-lg font-extrabold tracking-tight text-slate-800">D-Sign <span class="text-emerald-600">VERIFICATION</span></span>
                </div>
            </div>

            <div class="glass-panel rounded-[2rem] shadow-2xl shadow-slate-200/50 overflow-hidden relative">
                <div class="h-3 bg-emerald-500 w-full"></div>
                
                <div class="p-8 sm:p-10 text-center border-b border-slate-100">
                    
                <img src="logo/banner.png" 
                    alt="D-Sign Banner" 
                    class="mx-auto h-20 sm:h-24 md:h-28 lg:h-32 object-contain mb-6">

                    <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6 verify-pulse">
                        <i class="fas fa-shield-check text-4xl text-emerald-500"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 leading-tight">Authentic & Verified</h1>
                    <p class="text-slate-500 font-medium text-sm sm:text-base">This document has been fully executed and signed using valid PNPKI credentials.</p>
                </div>

                <div class="p-8 sm:p-10 bg-white">
                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Document Information</h2>
                    
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 mb-1">Title</span>
                                <span class="block text-sm font-bold text-slate-800"><?php echo htmlspecialchars($doc_data['title']); ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 mb-1">Document Type</span>
                                <span class="inline-block px-2 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-md uppercase tracking-wider"><?php echo htmlspecialchars($doc_data['doc_type']); ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 mb-1">Initiator / Owner</span>
                                <span class="block text-sm font-bold text-slate-800"><?php echo htmlspecialchars($doc_data['initiator_name'] ?? 'System User'); ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 mb-1">Date Finalized</span>
                                <span class="block text-sm font-bold text-slate-800"><?php echo date('F j, Y, g:i a', strtotime($doc_data['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">PNPKI Digital Signatures</h2>
                    <div class="flex items-start gap-4 bg-emerald-50/50 border border-emerald-100 p-5 rounded-2xl mb-8">
                        <div class="text-emerald-500 mt-1">
                            <i class="fas fa-fingerprint text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-900">Cryptographically Secured</h3>
                            <p class="text-xs text-emerald-700 mt-1 leading-relaxed">The attached files have been validated against the Philippine National Public Key Infrastructure (PNPKI) registry. Any modifications to these files will invalidate the signatures.</p>
                        </div>
                    </div>

                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Main Verified Document</h2>
                    <div class="space-y-3">
                        <?php if(empty($doc_files)): ?>
                            <div class="p-4 text-center bg-slate-50 rounded-xl border border-slate-100 text-slate-500 text-sm">
                                No public files attached.
                            </div>
                        <?php else: ?>
                            <?php foreach ($doc_files as $f): ?>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors gap-4">
                                    <div class="flex items-center gap-3 overflow-hidden w-full">
                                        <i class="fas fa-file-pdf text-rose-500 text-lg"></i>
                                        <span class="text-sm font-bold text-slate-700 truncate"><?php echo htmlspecialchars($f['filename']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                                        <button onclick="openViewerModal('<?php echo htmlspecialchars($f['filepath']); ?>')" class="flex-1 sm:flex-none h-9 px-4 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition border border-blue-100 flex items-center justify-center gap-2">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        
                                        <a href="<?php echo htmlspecialchars($f['filepath']); ?>" download class="flex-1 sm:flex-none h-9 px-4 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-emerald-600 transition flex items-center justify-center gap-2">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($attachments)): ?>
                        <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4 mt-8">Supporting Documents / Attachments</h2>
                        <div class="space-y-3">
                            <?php foreach ($attachments as $att): ?>
                                <?php 
                                    // Clean the display name for UI
                                    $clean_name = str_replace('[Attachment] - ', '', $att['filename']);
                                    $ext = strtolower(pathinfo($clean_name, PATHINFO_EXTENSION));
                                    $icon_class = 'fa-file-alt text-slate-500';
                                    if ($ext === 'pdf') $icon_class = 'fa-file-pdf text-rose-500';
                                    elseif (in_array($ext, ['doc', 'docx'])) $icon_class = 'fa-file-word text-blue-500';
                                    elseif (in_array($ext, ['xls', 'xlsx'])) $icon_class = 'fa-file-excel text-green-500';
                                ?>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors gap-4">
                                    <div class="flex items-center gap-3 overflow-hidden w-full">
                                        <i class="fas <?php echo $icon_class; ?> text-lg"></i>
                                        <span class="text-sm font-bold text-slate-700 truncate"><?php echo htmlspecialchars($clean_name); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                                        <button onclick="openViewerModal('<?php echo htmlspecialchars($att['filepath']); ?>')" class="flex-1 sm:flex-none h-9 px-4 bg-slate-50 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition border border-slate-200 flex items-center justify-center gap-2">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        
                                        <a href="<?php echo htmlspecialchars($att['filepath']); ?>" download="<?php echo htmlspecialchars($clean_name); ?>" class="flex-1 sm:flex-none h-9 px-4 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-300 transition flex items-center justify-center gap-2">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="text-center mt-8 text-xs font-semibold text-slate-400">
                <p>Scanned on <?php echo date('F j, Y'); ?></p>
                <p class="mt-1">D-Sign Secure Verification Portal</p>
            </div>

        <?php else: ?>
            <div class="bg-white rounded-[2rem] shadow-2xl p-10 text-center border-t-4 border-rose-500">
                <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-exclamation-triangle text-3xl text-rose-500"></i>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mb-3">Verification Failed</h1>
                <p class="text-slate-500 font-medium mb-8 leading-relaxed"><?php echo $error_message; ?></p>
                <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition">
                    <i class="fas fa-home"></i> Return to Homepage
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div id="viewerModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 transition-opacity" style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden relative border border-slate-700">
            <div class="px-6 py-4 bg-slate-900 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-alt text-blue-400 text-lg"></i>
                    <h3 class="text-white font-bold text-sm uppercase tracking-wider">Document Preview</h3>
                </div>
                <button onclick="closeViewerModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-rose-500 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="flex-grow bg-slate-200 relative flex items-center justify-center">
                <div id="viewerLoader" class="absolute inset-0 flex items-center justify-center bg-slate-100 z-10 hidden">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i>
                </div>
                
                <iframe id="docIframe" class="w-full h-full border-none z-20 relative bg-white" src="" onload="document.getElementById('viewerLoader').classList.add('hidden')"></iframe>
                
                <div id="viewerFallback" class="hidden absolute inset-0 flex items-center justify-center bg-white z-30">
                    <div class="text-center p-8">
                        <i class="fas fa-eye-slash text-4xl text-slate-300 mb-4"></i>
                        <h3 class="text-slate-700 font-bold">Preview not available</h3>
                        <p class="text-slate-500 text-sm mt-2">This file format cannot be previewed in the browser.<br>Please use the Download button instead.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openViewerModal(filepath) {
            const modal = document.getElementById('viewerModal');
            const iframe = document.getElementById('docIframe');
            const fallback = document.getElementById('viewerFallback');
            const loader = document.getElementById('viewerLoader');
            
            // Extract the file extension
            const ext = filepath.split('.').pop().toLowerCase();
            
            // Show the modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Supported preview formats
            const supportedFormats = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (supportedFormats.includes(ext)) {
                // Show loader, hide fallback, set iframe source
                loader.classList.remove('hidden');
                iframe.classList.remove('hidden');
                fallback.classList.add('hidden');
                
                iframe.src = filepath;
            } else {
                // Show fallback, hide iframe
                iframe.src = '';
                iframe.classList.add('hidden');
                loader.classList.add('hidden');
                
                fallback.classList.remove('hidden');
                fallback.classList.add('flex');
            }
        }

        function closeViewerModal() {
            const modal = document.getElementById('viewerModal');
            const iframe = document.getElementById('docIframe');
            const loader = document.getElementById('viewerLoader');
            
            // Hide modal and clean up
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            iframe.src = ''; // Stops iframe from continuing to download/play in background
            loader.classList.add('hidden');
        }

        // Close modal when clicking outside of it
        document.getElementById('viewerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeViewerModal();
            }
        });
    </script>

</body>
</html>
