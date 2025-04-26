<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

require_once 'includes/config.php';

$message = '';

// Handle entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['entry'])) {
    $card_number = $_POST['card_number'];
    $station_id = $_POST['station_id'];
    
    try {
        // Check if card exists and is active
        $stmt = $pdo->prepare("SELECT card_id, balance, status FROM metro_cards WHERE card_number = ?");
        $stmt->execute([$card_number]);
        $card = $stmt->fetch();
        
        if (!$card) {
            $message = "Card not found!";
        } elseif ($card['status'] != 'active') {
            $message = "Card is not active!";
        } else {
            // Create entry transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (card_id, entry_station) VALUES (?, ?)");
            $stmt->execute([$card['card_id'], $station_id]);
            $message = "Entry recorded successfully!";
        }
    } catch (PDOException $e) {
        $message = "Error recording entry: " . $e->getMessage();
    }
}

// Handle exit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['exit'])) {
    $card_number = $_POST['card_number'];
    $station_id = $_POST['station_id'];
    
    try {
        // Check if card exists and is active
        $stmt = $pdo->prepare("SELECT card_id, balance, status FROM metro_cards WHERE card_number = ?");
        $stmt->execute([$card_number]);
        $card = $stmt->fetch();
        
        if (!$card) {
            $message = "Card not found!";
        } elseif ($card['status'] != 'active') {
            $message = "Card is not active!";
        } else {
            // Get the latest entry transaction without exit
            $stmt = $pdo->prepare("
                SELECT t.trans_id, t.entry_station, s1.zone as entry_zone 
                FROM transactions t 
                JOIN stations s1 ON t.entry_station = s1.station_id 
                WHERE t.card_id = ? AND t.exit_station IS NULL 
                ORDER BY t.entry_time DESC 
                LIMIT 1
            ");
            $stmt->execute([$card['card_id']]);
            $transaction = $stmt->fetch();
            
            if (!$transaction) {
                $message = "No active journey found for this card!";
            } else {
                // Get exit station zone
                $stmt = $pdo->prepare("SELECT zone FROM stations WHERE station_id = ?");
                $stmt->execute([$station_id]);
                $exit_zone = $stmt->fetchColumn();
                
                // Calculate fare
                $stmt = $pdo->prepare("
                    SELECT fare FROM fare_rules 
                    WHERE (start_zone = ? AND end_zone = ?) 
                    OR (start_zone = ? AND end_zone = ?)
                ");
                $stmt->execute([$transaction['entry_zone'], $exit_zone, $exit_zone, $transaction['entry_zone']]);
                $fare = $stmt->fetchColumn();
                
                if (!$fare) {
                    $message = "Fare not defined for this route!";
                } elseif ($card['balance'] < $fare) {
                    $message = "Insufficient balance!";
                } else {
                    // Update transaction with exit details
                    $stmt = $pdo->prepare("
                        UPDATE transactions 
                        SET exit_station = ?, fare = ?, exit_time = CURRENT_TIMESTAMP 
                        WHERE trans_id = ?
                    ");
                    $stmt->execute([$station_id, $fare, $transaction['trans_id']]);
                    
                    // Deduct fare from card balance
                    $stmt = $pdo->prepare("UPDATE metro_cards SET balance = balance - ? WHERE card_id = ?");
                    $stmt->execute([$fare, $card['card_id']]);
                    
                    $message = "Exit recorded successfully! Fare: ₹" . number_format($fare, 2);
                }
            }
        }
    } catch (PDOException $e) {
        $message = "Error recording exit: " . $e->getMessage();
    }
}

// Get all stations
$stations = $pdo->query("SELECT * FROM stations")->fetchAll();
?>

<?php include 'includes/header.php'; ?>
    <h1>Station Control</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="flex-container">
        <div class="card">
            <h2>Record Entry</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="entry_card_number">Card Number</label>
                    <input type="text" id="entry_card_number" name="card_number" required>
                </div>
                <div class="form-group">
                    <label for="entry_station_id">Station</label>
                    <select id="entry_station_id" name="station_id" required>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['station_id']; ?>">
                                <?php echo htmlspecialchars($station['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="entry" class="btn">Record Entry</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Record Exit</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="exit_card_number">Card Number</label>
                    <input type="text" id="exit_card_number" name="card_number" required>
                </div>
                <div class="form-group">
                    <label for="exit_station_id">Station</label>
                    <select id="exit_station_id" name="station_id" required>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['station_id']; ?>">
                                <?php echo htmlspecialchars($station['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="exit" class="btn">Record Exit</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <h2>Active Journeys</h2>
        <table>
            <tr>
                <th>Card Number</th>
                <th>Entry Station</th>
                <th>Entry Time</th>
                <th>Duration</th>
            </tr>
            <?php
            $active_journeys = $pdo->query("
                SELECT t.trans_id, mc.card_number, s.name as station_name, t.entry_time 
                FROM transactions t 
                JOIN metro_cards mc ON t.card_id = mc.card_id 
                JOIN stations s ON t.entry_station = s.station_id 
                WHERE t.exit_station IS NULL
            ")->fetchAll();
            
            foreach ($active_journeys as $journey):
                $duration = time() - strtotime($journey['entry_time']);
                $hours = floor($duration / 3600);
                $minutes = floor(($duration % 3600) / 60);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($journey['card_number']); ?></td>
                    <td><?php echo htmlspecialchars($journey['station_name']); ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($journey['entry_time'])); ?></td>
                    <td><?php echo "$hours hours $minutes minutes"; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>