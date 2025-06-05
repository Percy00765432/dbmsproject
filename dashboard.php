<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];

// Get metro cards and transactions
$stmt = $pdo->prepare("
    SELECT mc.*, COUNT(t.trans_id) as total_rides, SUM(t.fare) as total_spent 
    FROM metro_cards mc 
    LEFT JOIN transactions t ON mc.card_id = t.card_id 
    WHERE mc.user_id = ? 
    GROUP BY mc.card_id
");
$stmt->execute([$user_id]);
$cards = $stmt->fetchAll();

$transactions = [];
foreach ($cards as $card) {
    $stmt = $pdo->prepare("
        SELECT t.*, s1.name as entry_station_name, s2.name as exit_station_name 
        FROM transactions t 
        LEFT JOIN stations s1 ON t.entry_station = s1.station_id 
        LEFT JOIN stations s2 ON t.exit_station = s2.station_id 
        WHERE t.card_id = ? 
        ORDER BY t.entry_time DESC 
        LIMIT 5
    ");
    $stmt->execute([$card['card_id']]);
    $transactions[$card['card_id']] = $stmt->fetchAll();
}

include 'includes/header.php';
?>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>

    <div class="card">
        <h2>Your Metro Cards</h2>
        <?php if (count($cards) > 0): ?>
            <div class="flex-container">
                <?php foreach ($cards as $card): ?>
                    <div class="card">
                        <h3>Card: <?php echo htmlspecialchars($card['card_number']); ?></h3>
                        <p>Balance: ₹<?php echo number_format($card['balance'], 2); ?></p>
                        <p>Status: <?php echo ucfirst($card['status']); ?></p>
                        <p>Total Rides: <?php echo $card['total_rides']; ?></p>
                        <p>Total Spent: ₹<?php echo number_format($card['total_spent'] ?? 0, 2); ?></p>
                        
                        <h4>Recent Transactions</h4>
                        <?php if (!empty($transactions[$card['card_id']])): ?>
                            <table>
                                <tr>
                                    <th>Entry Station</th>
                                    <th>Exit Station</th>
                                    <th>Fare</th>
                                    <th>Date</th>
                                </tr>
                                <?php foreach ($transactions[$card['card_id']] as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['entry_station_name']); ?></td>
                                        <td><?php echo $transaction['exit_station_name'] ? htmlspecialchars($transaction['exit_station_name']) : 'In Transit'; ?></td>
                                        <td>₹<?php echo number_format($transaction['fare'], 2); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($transaction['entry_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>No transactions found for this card.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You don't have any metro cards registered.</p>
        <?php endif; ?>
    </div>

<?php include 'includes/footer.php'; ?>