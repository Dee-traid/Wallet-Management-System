<?php 

namespace src\Repository;

use src\Models\User;
use src\Config\Database;
use PDO;
Use PDOException;
use DateTimeImmutable;

class UserRepository{
    private PDO $db;

    public function __construct(){
        $this->db = Database::getDatabaseConnection();
    }

    public function findUserByID(string $id): ?User
    {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? User::mapToUserRow($user): null;
    }

    public function findUserByEmail(string $email): ?User
    {
        $query = "SELECT * FROM users WHERE LOWER(email) = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? User::mapToUserRow($user): null;
    }

    public function createUser(User $user): ?User
    {
        $id = $user->getID();
        $fullName = $user->getFullName();
        $email = strtolower($user->getEmail());
        $passwordHash = $user->getPasswordHash();
        $walletTransactionPinHash = $user->getWalletTransactionPinHash();
        $isActive = $user->getIsActive();
        $emailVerified = $user->getEmailVerified();
        $createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');
        $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
        try{
            $query = "INSERT INTO users (id, full_name, email, password_hash, wallet_transaction_pin_hash, is_active, email_verified, created_at, updated_at) VALUES (:id, :fullName, :email, :passwordHash, :walletTransactionPinHash, :isActive, :emailVerified, :createdAt, :updatedAt)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fullName', $fullName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':passwordHash', $passwordHash);
            $stmt->bindParam(':walletTransactionPinHash', $walletTransactionPinHash);
            $stmt->bindParam(':isActive', $isActive);
            $stmt->bindParam(':emailVerified', $emailVerified);
            $stmt->bindParam(':createdAt', $createdAt);
            $stmt->bindParam(':updatedAt', $updatedAt);
            $stmt->execute();
            
            return $user;

        }catch(PDOException $e){
            throw new PDOException("Error creating user" . $e->getMessage());
        }
        
    }

    public function findPublicUserByID(string $id): ?array {
        $query = "SELECT full_name, email, is_active FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'is_active' => (bool) $row['is_active'],
        ];
    }



    public function updateUser(User $user): ?User
    {
        $id = $user->getId();
        $fullName = $user->getFullName();
        $email = $user->getEmail();
        $updatedAt = $user->getUpdatedAt();
        
        try {
            $query = "UPDATE users SET full_name = :fullName, email = :email, updated_at = :updatedAt WHERE id = :id ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fullName', $fullName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':updatedAt', $updatedAt);
            $stmt->execute();

            return $this->user->findByUserID($user->getID());
        } catch (PDOException $e) {
            throw new PDOException("Error updating user" . $e->getMessage());
        }
        
    }

    public function deleteUser(string $id): ?User
    {
        $id = $user->getId();
        try {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            throw new PDOException("Error deleting user: " . $e->getMessage());
        }
    }

    public function saveVerificationToken(string $userId, string $email, string $tokenHash,DateTimeImmutable $createdAt, DateTimeImmutable $expiresAt): void{
        $deleteQuery = "DELETE FROM email_verification WHERE user_id = :userId";
        $saveQuery = "INSERT INTO email_verification(user_id, email, token_hash, created_at, expires_at) VALUES (:userId, :email, :tokenHash, :createdAt, :expiresAt)";
        try{
            $stmt = $this->db->prepare($deleteQuery);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
        }  catch (PDOException $e) {
            throw new PDOException("Error truancating past records: " . $e->getMessage());
        }

        $id = uniqid('ver-', true);
        $createdAt = $createdAt->format('Y-m-d H:i:s');
        $expiresAt = $expiresAt->format('Y-m-d H:i:s');
        try {
            $stmt = $this->db->prepare($saveQuery);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':tokenHash', $tokenHash);
            $stmt->bindParam(':createdAt', $createdAt);
            $stmt->bindParam(':expiresAt', $expiresAt);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Unable to save record: " . $e->getMessage());
        }
    }

    public function findTokenVerification(string $tokenHash): ?array
    {
        $query = "SELECT user_id, expires_at FROM email_verification WHERE token_hash = :tokenHash";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':tokenHash', $tokenHash);
            $stmt->execute();
    
            $token = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            return $token;
        } catch (PDOException $e) {
            throw new PDOException("Error finding record: " . $e->getMessage());
        }
       
    }

    public function markEmailVerified(string $userId){
        $query = "UPDATE users SET email_verified = 1, updated_at = :updatedAt WHERE id = :userId";
        $updatedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        try{
            $this->db->beginTransaction();
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':updatedAt', $updatedAt);
            $stmt->execute();

            $tokenRemoval = $this->db->prepare("DELETE FROM email_verification WHERE user_id = :userId");
            $tokenRemoval->bindParam(':userId', $userId);
            $tokenRemoval->execute();

            $this->db->commit();
        }catch(PDOException $e){
            $this->db->rollback();
            throw new PDOException("Error updating record status" . $e->getMessage());
        }
    }

    public function updateLastLogin(string $userId){
        $query = "UPDATE users SET last_login_at = :now WHERE id = :id";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Error updating record status: " . $e->getMessage());
        }
    }


}