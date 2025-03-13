<?php
require_once __DIR__ . '/../config/db.php';

class Bet {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Create a new bet
     */
    public function createBet($data) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Debug log
            error_log("Starting bet creation with data: " . json_encode($data));
            
            // Insert bet
            $sql = "INSERT INTO Bets (
                user_id, 
                description, 
                stake_type,
                stake_amount,
                stake_description,
                deadline,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $this->db->prepare($sql);
            
            // Debug log
            error_log("Executing SQL: " . $sql);
            
            $params = [
                $data['user_id'],
                $data['description'],
                $data['stake_type'],
                $data['stake_amount'],
                $data['stake_description'],
                $data['deadline']
            ];
            
            // Debug log
            error_log("With parameters: " . json_encode($params));
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Database error: " . json_encode($error));
                throw new Exception("Failed to insert bet: " . $error[2]);
            }
            
            $betId = $this->db->lastInsertId();
            error_log("Bet created with ID: " . $betId);
            
            // Commit transaction
            $this->db->commit();
            
            return $betId;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error creating bet: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get bet by ID
     */
    public function getBetById($betId) {
        $sql = "SELECT b.*, u.username as creator_username 
                FROM Bets b 
                JOIN Users u ON b.user_id = u.user_id 
                WHERE b.bet_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$betId]);
        
        return $stmt->fetch();
    }

    /**
     * Get all bets
     */
    public function getAllBets() {
        $sql = "SELECT b.*, u.username as creator_username 
                FROM Bets b 
                JOIN Users u ON b.user_id = u.user_id 
                ORDER BY b.created_at DESC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll();
    }

    /**
     * Get bets created by a user
     */
    public function getBetsByUser($userId) {
        $sql = "SELECT b.*, 
                u.username as creator_username,
                u2.username as opponent_username,
                COALESCE(pi.status, 'pending') as payment_status
                FROM Bets b
                JOIN Users u ON b.user_id = u.user_id
                LEFT JOIN Notifications n ON b.bet_id = n.bet_id
                LEFT JOIN Users u2 ON n.user_id = u2.user_id
                LEFT JOIN PaymentIntents pi ON b.bet_id = pi.bet_id AND pi.status = 'pending'
                WHERE b.user_id = ? 
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get bets where a user is participating (excluding their own bets)
     */
    public function getBetsForUser($userId) {
        $sql = "SELECT b.*, 
                u.username as creator_username,
                u2.username as opponent_username,
                COALESCE(pi.status, 'pending') as payment_status,
                CASE 
                    WHEN b.stake_type = 'money' AND pi.status = 'pending' THEN true
                    ELSE false
                END as payment_required
                FROM Bets b
                JOIN Users u ON b.user_id = u.user_id
                LEFT JOIN Notifications n ON b.bet_id = n.bet_id
                LEFT JOIN Users u2 ON n.user_id = u2.user_id
                LEFT JOIN PaymentIntents pi ON b.bet_id = pi.bet_id AND pi.status = 'pending'
                WHERE b.user_id = ? 
                ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Update bet status
     */
    public function updateBetStatus($betId, $status) {
        $sql = "UPDATE Bets SET status = ? WHERE bet_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$status, $betId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Send bet invitation to another user
     */
    public function inviteUserToBet($betId, $userId) {
        $sql = "INSERT INTO Notifications (bet_id, user_id, type, status) VALUES (?, ?, 'bet_invitation', 'pending')";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$betId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Accept or reject a bet
     */
    public function respondToBet($notificationId, $response) {
        $sql = "UPDATE Notifications SET status = ? WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$response, $notificationId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete a bet
     */
    public function deleteBet($betId) {
        $sql = "DELETE FROM Bets WHERE bet_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$betId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update a bet
     */
    public function updateBet($betId, $data) {
        $updateFields = [];
        $params = [];
        
        // Build update fields dynamically
        foreach ($data as $field => $value) {
            $updateFields[] = "$field = ?";
            $params[] = $value;
        }
        
        // Add bet_id to params
        $params[] = $betId;
        
        $sql = "UPDATE Bets SET " . implode(', ', $updateFields) . " WHERE bet_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Check if all participants have paid for a group bet
     */
    public function checkAllParticipantsPaid($betId) {
        $sql = "SELECT COUNT(*) as total_participants,
                SUM(CASE WHEN pi.status = 'completed' THEN 1 ELSE 0 END) as paid_participants
                FROM BetParticipants bp
                LEFT JOIN PaymentIntents pi ON bp.bet_id = pi.bet_id AND bp.user_id = pi.user_id
                WHERE bp.bet_id = ? AND bp.status = 'accepted'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$betId]);
        $result = $stmt->fetch();
        
        return $result['total_participants'] > 0 && $result['total_participants'] == $result['paid_participants'];
    }

    /**
     * Check if all invited participants have responded
     */
    public function checkAllParticipantsResponded($betId) {
        $sql = "SELECT COUNT(*) as total_invites,
                SUM(CASE WHEN status IN ('accepted', 'rejected') THEN 1 ELSE 0 END) as responded
                FROM BetParticipants
                WHERE bet_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$betId]);
        $result = $stmt->fetch();
        
        return $result['total_invites'] > 0 && $result['total_invites'] == $result['responded'];
    }

    /**
     * Update bet status based on group participation
     */
    public function updateGroupBetStatus($betId) {
        try {
            $this->db->beginTransaction();

            // Get bet details
            $sql = "SELECT stake_type FROM Bets WHERE bet_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$betId]);
            $bet = $stmt->fetch();

            // For money bets, check if all accepted participants have paid
            if ($bet['stake_type'] === 'money') {
                $allPaid = $this->checkAllParticipantsPaid($betId);
                $allResponded = $this->checkAllParticipantsResponded($betId);
                
                if ($allResponded && $allPaid) {
                    // All participants have accepted and paid - activate the bet
                    $this->updateBetStatus($betId, 'active');
                } elseif ($allResponded && !$allPaid) {
                    // All have responded but not all paid - keep as pending
                    $this->updateBetStatus($betId, 'pending');
                }
            } else {
                // For non-money bets, activate if all have responded
                if ($this->checkAllParticipantsResponded($betId)) {
                    $this->updateBetStatus($betId, 'active');
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating group bet status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process group bet payout
     */
    public function processGroupBetPayout($betId, $winnerId) {
        try {
            $this->db->beginTransaction();

            // Get total pot amount
            $sql = "SELECT SUM(amount) as total_pot 
                    FROM PaymentIntents 
                    WHERE bet_id = ? AND status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$betId]);
            $result = $stmt->fetch();

            if ($result && $result['total_pot'] > 0) {
                // Transfer total pot to winner
                $paymentProcessor = new PaymentProcessor();
                $payoutResult = $paymentProcessor->processPayout($betId, $winnerId, $result['total_pot']);
                
                if (!$payoutResult['success']) {
                    throw new Exception('Failed to process payout');
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error processing group bet payout: " . $e->getMessage());
            return false;
        }
    }
}
?> 