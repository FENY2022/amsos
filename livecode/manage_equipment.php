<?php
session_start();
require_once 'connect.php';

// --- (A) Handle POST Requests for CUD (Create, Update, Delete) Operations ---

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ADD NEW EQUIPMENT
if ($action === 'add' && isset($_POST['equipment_name'], $_POST['needed'])) {
    $name = trim($_POST['equipment_name']);
    $needed = (int)$_POST['needed'];

    if (!empty($name) && $needed >= 0) {
        // Check for duplicates before inserting
        $stmt_check = $conn->prepare("SELECT id FROM inv_typeofequipment WHERE equipment_name = ?");
        $stmt_check->bind_param("s", $name);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $_SESSION['message'] = ['text' => "Error: Equipment '{$name}' already exists in the list.", 'type' => 'danger'];
        } else {
            $stmt = $conn->prepare("INSERT INTO inv_typeofequipment (equipment_name, Needed) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $needed);
            if ($stmt->execute()) {
                $_SESSION['message'] = ['text' => "Successfully added '{$name}'.", 'type' => 'success'];
            } else {
                $_SESSION['message'] = ['text' => 'Error: Could not add the equipment.', 'type' => 'danger'];
            }
            $stmt->close();
        }
        $stmt_check->close();
    } else {
        $_SESSION['message'] = ['text' => 'Error: Equipment name cannot be empty and "Needed" must be a non-negative number.', 'type' => 'danger'];
    }
    header("Location: manage_equipment.php");
    exit();
}

// UPDATE EQUIPMENT
if ($action === 'update' && isset($_POST['id'], $_POST['equipment_name'], $_POST['needed'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['equipment_name']);
    $needed = (int)$_POST['needed'];

    if (!empty($name) && $needed >= 0 && $id > 0) {
        $stmt = $conn->prepare("UPDATE inv_typeofequipment SET equipment_name = ?, Needed = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $needed, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['text' => "Successfully updated '{$name}'.", 'type' => 'success'];
        } else {
            $_SESSION['message'] = ['text' => 'Error: Could not update the equipment.', 'type' => 'danger'];
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = ['text' => 'Error: Invalid data provided for update.', 'type' => 'danger'];
    }
    header("Location: manage_equipment.php");
    exit();
}

// DELETE EQUIPMENT
if ($action === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        // First, get the name for the success message
        $nameResult = $conn->query("SELECT equipment_name FROM inv_typeofequipment WHERE id = $id");
        $name = $nameResult->fetch_assoc()['equipment_name'] ?? 'the item';

        $stmt = $conn->prepare("DELETE FROM inv_typeofequipment WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['text' => "Successfully deleted '{$name}'.", 'type' => 'success'];
        } else {
            $_SESSION['message'] = ['text' => 'Error: Could not delete the equipment.', 'type' => 'danger'];
        }
        $stmt->close();
    } else {
         $_SESSION['message'] = ['text' => 'Error: Invalid ID for deletion.', 'type' => 'danger'];
    }
    header("Location: manage_equipment.php");
    exit();
}


// --- (B) Fetch Data for Display ---

// Fetch equipment types already being managed
$equipmentList = [];
$managedNames = []; // For filtering the dropdown
$result = $conn->query("SELECT id, equipment_name, Needed FROM inv_typeofequipment ORDER BY equipment_name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $equipmentList[] = $row;
        $managedNames[] = $row['equipment_name'];
    }
}

// Fetch all possible equipment types from the inventory table for the dropdown
$inventoryEquipmentTypes = [];
$typesResult = $conn->query("SELECT DISTINCT equipmentType FROM inv_inventory WHERE equipmentType IS NOT NULL AND equipmentType != '' ORDER BY equipmentType ASC");
if ($typesResult) {
    while ($row = $typesResult->fetch_assoc()) {
        $inventoryEquipmentTypes[] = $row['equipmentType'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Equipment Types</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal-backdrop {
            transition: opacity 0.3s ease;
        }
        .modal-content {
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Equipment Types</h1>
        
        <!-- Display Session Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 text-sm rounded-lg 
                <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" 
                role="alert">
                <span class="font-medium"><?php echo htmlspecialchars($_SESSION['message']['text']); ?></span>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Add Equipment Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Add New Equipment</h2>
            <form action="manage_equipment.php" method="post" class="grid sm:grid-cols-3 gap-4 items-end">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="equipment_name" class="block text-sm font-medium text-gray-700">Equipment Name</label>
                    <select name="equipment_name" id="equipment_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Equipment --</option>
                        <?php foreach ($inventoryEquipmentTypes as $type): ?>
                            <?php if (!in_array($type, $managedNames)): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="needed" class="block text-sm font-medium text-gray-700">Needed Quantity</label>
                    <input type="number" name="needed" id="needed" required min="0" value="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="w-full sm:w-auto justify-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center shadow-sm">
                    <i class="fas fa-plus mr-2"></i> Add Equipment
                </button>
            </form>
        </div>

        <!-- Equipment List Table -->
        <div class="bg-white rounded-xl shadow-lg p-6">
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment Name</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Needed Quantity</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($equipmentList)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No equipment types found. Add one above to get started.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equipmentList as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['equipment_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700"><?php echo htmlspecialchars($item['Needed']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                        <button class="edit-btn text-blue-600 hover:text-blue-900 mr-4" 
                                                data-id="<?php echo $item['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($item['equipment_name']); ?>" 
                                                data-needed="<?php echo $item['Needed']; ?>">
                                            <i class="fas fa-pencil-alt mr-1"></i>Edit
                                        </button>
                                        <button class="delete-btn text-red-600 hover:text-red-900"
                                                data-id="<?php echo $item['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($item['equipment_name']); ?>">
                                            <i class="fas fa-trash-alt mr-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
             </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="flex items-center justify-center min-h-screen">
            <div class="modal-content bg-white rounded-lg shadow-xl p-6 m-4 max-w-md w-full relative transform scale-95">
                <h3 class="text-xl font-semibold mb-4">Edit Equipment</h3>
                <form action="manage_equipment.php" method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-4">
                        <label for="edit-name" class="block text-sm font-medium text-gray-700">Equipment Name</label>
                        <input type="text" name="equipment_name" id="edit-name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="edit-needed" class="block text-sm font-medium text-gray-700">Needed Quantity</label>
                        <input type="number" name="needed" id="edit-needed" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" id="edit-cancel" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="flex items-center justify-center min-h-screen">
            <div class="modal-content bg-white rounded-lg shadow-xl p-6 m-4 max-w-md w-full relative transform scale-95">
                <h3 class="text-xl font-semibold mb-2">Confirm Deletion</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete '<span id="delete-name" class="font-bold"></span>'?</p>
                <form action="manage_equipment.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <div class="flex justify-end gap-3">
                        <button type="button" id="delete-cancel" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Edit Modal Logic ---
        const editModal = document.getElementById('edit-modal');
        const editModalContent = editModal.querySelector('.modal-content');
        const editIdInput = document.getElementById('edit-id');
        const editNameInput = document.getElementById('edit-name');
        const editNeededInput = document.getElementById('edit-needed');
        
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                editIdInput.value = button.dataset.id;
                editNameInput.value = button.dataset.name;
                editNeededInput.value = button.dataset.needed;
                showModal(editModal, editModalContent);
            });
        });

        document.getElementById('edit-cancel').addEventListener('click', () => hideModal(editModal, editModalContent));

        // --- Delete Modal Logic ---
        const deleteModal = document.getElementById('delete-modal');
        const deleteModalContent = deleteModal.querySelector('.modal-content');
        const deleteIdInput = document.getElementById('delete-id');
        const deleteNameSpan = document.getElementById('delete-name');
        
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                deleteIdInput.value = button.dataset.id;
                deleteNameSpan.textContent = button.dataset.name;
                showModal(deleteModal, deleteModalContent);
            });
        });
        
        document.getElementById('delete-cancel').addEventListener('click', () => hideModal(deleteModal, deleteModalContent));
        
        // --- Generic Modal Functions ---
        function showModal(modal, content) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.modal-backdrop').classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        }

        function hideModal(modal, content) {
            modal.querySelector('.modal-backdrop').classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        
        // Close modals on backdrop click
        [editModal, deleteModal].forEach(modal => {
            modal.querySelector('.modal-backdrop').addEventListener('click', () => {
                hideModal(modal, modal.querySelector('.modal-content'));
            });
        });
    });
    </script>
</body>
</html>

