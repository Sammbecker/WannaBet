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
                    SUM(CASE WHEN payment_status = 'completed' AND payment_date <= deadline THEN 1 ELSE 0 END) as on_time_payments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bets,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as total_payments
                 FROM Bets 
                 WHERE creator_id = ? OR opponent_id = ?";
        
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
        $updateQuery = "UPDATE users SET trust_score = ?, last_score_update = NOW() WHERE user_id = ?";
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
        $query = "SELECT trust_score, last_score_update FROM users WHERE user_id = ?";
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
                        SUM(CASE WHEN payment_status = 'completed' AND payment_date <= deadline THEN 1 ELSE 0 END) as on_time_payments,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bets,
                        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as total_payments
                     FROM Bets 
                     WHERE creator_id = ? OR opponent_id = ?";
        
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
    }
    
    /**
     * Update trust score after bet completion
     */
    public function updateScoreAfterBet($betId) {
        $query = "SELECT creator_id, opponent_id FROM Bets WHERE bet_id = ?";
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