<?php
require_once __DIR__ . '/../config/db.php';

class TrustScore {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Calculate and update user's trust score
     */
    public function calculateTrustScore($userId) {
        // Get user's bet history
        $query = "SELECT 
                    COUNT(*) as total_bets,
                    SUM(CASE WHEN p.status = 'completed' AND p.created_at <= b.updated_at THEN 1 ELSE 0 END) as on_time_payments,
                    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bets,
                    SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as total_payments
                 FROM bets b
                 LEFT JOIN payments p ON b.id = p.bet_id
                 WHERE b.creator_id = ? OR b.opponent_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate components of trust score
        $paymentReliability = $stats['total_payments'] > 0 
            ? ($stats['on_time_payments'] / $stats['total_payments']) * 100 
            : 100;
            
        $completionRate = $stats['total_bets'] > 0 
            ? ($stats['completed_bets'] / $stats['total_bets']) * 100 
            : 100;
        
        // Base score starts at 70
        $baseScore = 70;
        
        // Calculate final score
        $trustScore = $baseScore;
        
        // Add up to 15 points for payment reliability
        $trustScore += ($paymentReliability / 100) * 15;
        
        // Add up to 15 points for completion rate
        $trustScore += ($completionRate / 100) * 15;
        
        // Update user's trust score
        $updateQuery = "UPDATE users SET trust_score = ?, last_score_update = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->execute([round($trustScore), $userId]);
        
        return [
            'score' => round($trustScore),
            'payment_reliability' => round($paymentReliability),
            'completion_rate' => round($completionRate)
        ];
    }
    
    /**
     * Get user's current trust score and stats
     */
    public function getTrustScore($userId) {
        try {
            // First check if the column exists
            $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'last_score_update'");
            $columnExists = $checkColumn->rowCount() > 0;

            if (!$columnExists) {
                // If column doesn't exist, add it
                $this->db->exec("ALTER TABLE users ADD COLUMN last_score_update TIMESTAMP NULL");
                $this->db->exec("ALTER TABLE users ADD COLUMN trust_score INT DEFAULT 100");
            }

            $query = "SELECT trust_score, last_score_update FROM users WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If score is older than 24 hours or doesn't exist, recalculate
            if (!$result || !$result['last_score_update'] || strtotime($result['last_score_update']) < strtotime('-24 hours')) {
                return $this->calculateTrustScore($userId);
            }
            
            // Get payment and completion stats
            $statsQuery = "SELECT 
                            COUNT(*) as total_bets,
                            SUM(CASE WHEN p.status = 'completed' AND p.created_at <= b.updated_at THEN 1 ELSE 0 END) as on_time_payments,
                            SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bets,
                            SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as total_payments
                         FROM bets b
                         LEFT JOIN payments p ON b.id = p.bet_id
                         WHERE b.creator_id = ? OR b.opponent_id = ?";
            
            $stmt = $this->db->prepare($statsQuery);
            $stmt->execute([$userId, $userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $paymentReliability = $stats['total_payments'] > 0 
                ? ($stats['on_time_payments'] / $stats['total_payments']) * 100 
                : 100;
                
            $completionRate = $stats['total_bets'] > 0 
                ? ($stats['completed_bets'] / $stats['total_bets']) * 100 
                : 100;
            
            return [
                'score' => round($result['trust_score']),
                'payment_reliability' => round($paymentReliability),
                'completion_rate' => round($completionRate)
            ];
        } catch (PDOException $e) {
            // If there's an error, return default values
            return [
                'score' => 100,
                'payment_reliability' => 100,
                'completion_rate' => 100
            ];
        }
    }
    
    /**
     * Update trust score after bet completion
     */
    public function updateScoreAfterBet($betId) {
        $query = "SELECT creator_id, opponent_id FROM bets WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$betId]);
        $bet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bet) {
            // Update scores for both users
            $this->calculateTrustScore($bet['creator_id']);
            $this->calculateTrustScore($bet['opponent_id']);
            return true;
        }
        
        return false;
    }
}
?> 