<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

require_once 'includes/config.php';

$message = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    try {
        // Validate inputs
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("Name, email, and password are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email already registered");
        }

        // Create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, phone, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $hashed_password, $phone, $role]);
        $message = "User created successfully!";
    } catch (Exception $e) {
        $message = "Error creating user: " . $e->getMessage();
    }
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, email = ?, phone = ?, role = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$name, $email, $phone, $role, $user_id]);
        $message = "User updated successfully!";
    } catch (PDOException $e) {
        $message = "Error updating user: " . $e->getMessage();
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        $message = "Password reset successfully!";
    } catch (PDOException $e) {
        $message = "Error resetting password: " . $e->getMessage();
    }
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $message = "User deleted successfully!";
    } catch (PDOException $e) {
        $message = "Error deleting user: " . $e->getMessage();
    }
}

// Handle card creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_card'])) {
    $user_id = $_POST['user_id'];
    $card_number = $_POST['card_number'];
    $balance = $_POST['balance'];
    $expiry_date = $_POST['expiry_date'] ?? null;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO metro_cards 
            (user_id, card_number, balance, expiry_date) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $card_number, $balance, $expiry_date]);
        
        // Record initial recharge
        $card_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("
            INSERT INTO recharges 
            (card_id, amount) 
            VALUES (?, ?)
        ");
        $stmt->execute([$card_id, $balance]);
        
        $message = "Card created and initial balance added successfully!";
    } catch (PDOException $e) {
        $message = "Error creating card: " . $e->getMessage();
    }
}

// Handle card update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_card'])) {
    $card_id = $_POST['card_id'];
    $status = $_POST['status'];
    $expiry_date = $_POST['expiry_date'] ?? null;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE metro_cards 
            SET status = ?, expiry_date = ? 
            WHERE card_id = ?
        ");
        $stmt->execute([$status, $expiry_date, $card_id]);
        $message = "Card updated successfully!";
    } catch (PDOException $e) {
        $message = "Error updating card: " . $e->getMessage();
    }
}

// Handle card recharge
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recharge_card'])) {
    $card_id = $_POST['card_id'];
    $amount = $_POST['amount'];
    
    try {
        // Update card balance
        $stmt = $pdo->prepare("
            UPDATE metro_cards 
            SET balance = balance + ? 
            WHERE card_id = ?
        ");
        $stmt->execute([$amount, $card_id]);
        
        // Record recharge transaction
        $stmt = $pdo->prepare("
            INSERT INTO recharges 
            (card_id, amount) 
            VALUES (?, ?)
        ");
        $stmt->execute([$card_id, $amount]);
        
        $message = "Card recharged successfully with ₹" . number_format($amount, 2);
    } catch (PDOException $e) {
        $message = "Error recharging card: " . $e->getMessage();
    }
}

// Handle card status toggle
if (isset($_GET['change_card_status'])) {
    $card_id = $_GET['card_id'];
    $action = $_GET['change_card_status'];
    
    try {
        $new_status = $action === 'block' ? 'blocked' : 'active';
        $stmt = $pdo->prepare("
            UPDATE metro_cards 
            SET status = ? 
            WHERE card_id = ?
        ");
        $stmt->execute([$new_status, $card_id]);
        $message = "Card status updated to " . $new_status . "!";
    } catch (PDOException $e) {
        $message = "Error updating card status: " . $e->getMessage();
    }
}

// Get all users
$users = $pdo->query("SELECT * FROM users ORDER BY role, name")->fetchAll();

// Get all metro cards with user info
$cards = $pdo->query("
    SELECT mc.*, u.name as user_name, u.email 
    FROM metro_cards mc 
    JOIN users u ON mc.user_id = u.user_id
")->fetchAll();
?>

<?php include 'includes/header.php'; ?>
    <h1>Admin Panel</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab('users')">User Management</button>
        <button class="tab-btn" onclick="openTab('cards')">Card Management</button>
    </div>
    
    <div id="users" class="tab-content active">
        <div class="card">
            <h2>Create New User</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="passenger">Passenger</option>
                        <option value="operator">Operator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="create_user" class="btn">Create User</button>
            </form>
        </div>
        
        <div class="card">
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td class="actions">
                                <a href="#" onclick="showEditForm(<?php echo $user['user_id']; ?>)" class="btn">Edit</a>
                                <a href="#" onclick="showPasswordForm(<?php echo $user['user_id']; ?>)" class="btn">Reset Password</a>
                                <a href="?delete_user=<?php echo $user['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                
                                <div id="edit-form-<?php echo $user['user_id']; ?>" class="popup-form" style="display:none;">
                                    <h3>Edit User</h3>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role">
                                                <option value="passenger" <?php echo $user['role'] == 'passenger' ? 'selected' : ''; ?>>Passenger</option>
                                                <option value="operator" <?php echo $user['role'] == 'operator' ? 'selected' : ''; ?>>Operator</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_user" class="btn">Update</button>
                                        <button type="button" onclick="hideEditForm(<?php echo $user['user_id']; ?>)" class="btn btn-danger">Cancel</button>
                                    </form>
                                </div>
                                
                                <div id="password-form-<?php echo $user['user_id']; ?>" class="popup-form" style="display:none;">
                                    <h3>Reset Password</h3>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" required>
                                        </div>
                                        <button type="submit" name="reset_password" class="btn">Reset Password</button>
                                        <button type="button" onclick="hidePasswordForm(<?php echo $user['user_id']; ?>)" class="btn btn-danger">Cancel</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="cards" class="tab-content">
        <div class="card">
            <h2>Create New Metro Card</h2>
            <form method="POST" id="createCardForm">
                <div class="form-group">
                    <label for="card_user_id">User</label>
                    <select id="card_user_id" name="user_id" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" required 
                           pattern="[A-Za-z0-9]{16}" title="16-digit alphanumeric card number">
                    <small>Format: 16 alphanumeric characters</small>
                </div>
                <div class="form-group">
                    <label for="initial_balance">Initial Balance (₹)</label>
                    <input type="number" id="initial_balance" name="balance" step="0.01" min="50" value="100" required>
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" name="create_card" class="btn">Create Card</button>
            </form>
        </div>

        <div class="card">
            <h2>All Metro Cards</h2>
            <div class="search-filter">
                <input type="text" id="cardSearch" placeholder="Search cards...">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>
            
            <div class="table-responsive">
                <table id="cardsTable">
                    <thead>
                        <tr>
                            <th>Card Number</th>
                            <th>User</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cards as $card): 
                            $expiry_date = $card['expiry_date'] ? date('d M Y', strtotime($card['expiry_date'])) : 'N/A';
                            $is_expired = $card['expiry_date'] && strtotime($card['expiry_date']) < time();
                        ?>
                            <tr data-status="<?php echo $card['status']; ?>" 
                                <?php echo $is_expired ? 'class="expired-card"' : ''; ?>>
                                <td><?php echo htmlspecialchars($card['card_number']); ?></td>
                                <td>
                                    <a href="#" onclick="showUserCards(<?php echo $card['user_id']; ?>)">
                                        <?php echo htmlspecialchars($card['user_name']); ?>
                                    </a>
                                </td>
                                <td>₹<?php echo number_format($card['balance'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $card['status']; ?>">
                                        <?php echo ucfirst($card['status']); ?>
                                        <?php echo $is_expired ? '(Expired)' : ''; ?>
                                    </span>
                                </td>
                                <td><?php echo $expiry_date; ?></td>
                                <td class="actions">
                                    <button onclick="showEditCardModal(<?php echo $card['card_id']; ?>)" 
                                            class="btn btn-sm">Edit</button>
                                    <button onclick="showRechargeModal(<?php echo $card['card_id']; ?>)" 
                                            class="btn btn-sm btn-success">Recharge</button>
                                    <button onclick="confirmCardAction(<?php echo $card['card_id']; ?>, 
                                        '<?php echo $card['status'] === 'active' ? 'block' : 'activate'; ?>')"
                                            class="btn btn-sm <?php echo $card['status'] === 'active' ? 'btn-warning' : 'btn-info'; ?>">
                                        <?php echo $card['status'] === 'active' ? 'Block' : 'Activate'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Card Modal -->
    <div id="editCardModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editCardModal')">&times;</span>
            <h3>Edit Metro Card</h3>
            <form id="editCardForm" method="POST">
                <input type="hidden" name="card_id" id="edit_card_id">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" id="edit_card_number" readonly>
                </div>
                <div class="form-group">
                    <label>Current Balance</label>
                    <input type="text" id="edit_current_balance" readonly>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_card_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" id="edit_expiry_date">
                </div>
                <button type="submit" name="update_card" class="btn">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Recharge Card Modal -->
    <div id="rechargeCardModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rechargeCardModal')">&times;</span>
            <h3>Recharge Metro Card</h3>
            <form id="rechargeCardForm" method="POST">
                <input type="hidden" name="card_id" id="recharge_card_id">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" id="recharge_card_number" readonly>
                </div>
                <div class="form-group">
                    <label>Current Balance</label>
                    <input type="text" id="recharge_current_balance" readonly>
                </div>
                <div class="form-group">
                    <label>Recharge Amount (₹)</label>
                    <input type="number" name="amount" min="50" max="5000" step="50" value="100" required>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="net_banking">Net Banking</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>
                <button type="submit" name="recharge_card" class="btn btn-success">Recharge Now</button>
            </form>
        </div>
    </div>

    <script>
    // Tab functionality
    function openTab(tabName) {
        const tabContents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }
        
        const tabButtons = document.getElementsByClassName('tab-btn');
        for (let i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }
        
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // User management forms
    function showEditForm(userId) {
        document.getElementById('edit-form-' + userId).style.display = 'block';
    }

    function hideEditForm(userId) {
        document.getElementById('edit-form-' + userId).style.display = 'none';
    }

    function showPasswordForm(userId) {
        document.getElementById('password-form-' + userId).style.display = 'block';
    }

    function hidePasswordForm(userId) {
        document.getElementById('password-form-' + userId).style.display = 'none';
    }

    // Card management functions
    function showEditCardModal(cardId) {
        fetch(`get_card_details.php?card_id=${cardId}`)
            .then(response => response.json())
            .then(card => {
                document.getElementById('edit_card_id').value = card.card_id;
                document.getElementById('edit_card_number').value = card.card_number;
                document.getElementById('edit_current_balance').value = '₹' + card.balance.toFixed(2);
                document.getElementById('edit_card_status').value = card.status;
                document.getElementById('edit_expiry_date').value = card.expiry_date;
                document.getElementById('editCardModal').style.display = 'block';
            })
            .catch(error => {
                alert('Error loading card details: ' + error);
            });
    }

    function showRechargeModal(cardId) {
        fetch(`get_card_details.php?card_id=${cardId}`)
            .then(response => response.json())
            .then(card => {
                document.getElementById('recharge_card_id').value = card.card_id;
                document.getElementById('recharge_card_number').value = card.card_number;
                document.getElementById('recharge_current_balance').value = '₹' + card.balance.toFixed(2);
                document.getElementById('rechargeCardModal').style.display = 'block';
            })
            .catch(error => {
                alert('Error loading card details: ' + error);
            });
    }

    function confirmCardAction(cardId, action) {
        if (confirm(`Are you sure you want to ${action} this card?`)) {
            window.location.href = `?change_card_status=${action}&card_id=${cardId}`;
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const cardSearch = document.getElementById('cardSearch');
        const statusFilter = document.getElementById('statusFilter');
        const cardsTable = document.getElementById('cardsTable');
        
        if (cardSearch && statusFilter && cardsTable) {
            cardSearch.addEventListener('input', filterCards);
            statusFilter.addEventListener('change', filterCards);
        }
        
        function filterCards() {
            const searchTerm = cardSearch.value.toLowerCase();
            const statusValue = statusFilter.value;
            
            Array.from(cardsTable.querySelectorAll('tbody tr')).forEach(row => {
                const cardNumber = row.cells[0].textContent.toLowerCase();
                const userName = row.cells[1].textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                
                const matchesSearch = cardNumber.includes(searchTerm) || userName.includes(searchTerm);
                const matchesStatus = statusValue === '' || status === statusValue;
                
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }
    });

    // Form validation
    document.getElementById('createCardForm')?.addEventListener('submit', function(e) {
        const cardNumber = document.getElementById('card_number').value;
        if (!/^[A-Za-z0-9]{16}$/.test(cardNumber)) {
            alert('Card number must be exactly 16 alphanumeric characters');
            e.preventDefault();
        }
    });
    </script>

<?php include 'includes/footer.php'; ?>