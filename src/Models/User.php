<?php
namespace src\Models;

use DateTimeImmutable;

class User{
    private string $id;
    private string $fullName;
    private string $email;
    private string $passwordHash;
    private string $walletTransactionPinHash;
    private bool $isActive;
    private bool $emailVerified;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct
        (
            string $id, 
            string $fullName, 
            string $email, 
            string $passwordHash, 
            string $walletTransactionPinHash, 
            bool $isActive, 
            bool $emailVerified, 
            DateTimeImmutable $createdAt, 
            DateTimeImmutable $updatedAt
        )
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->walletTransactionPinHash = $walletTransactionPinHash;
        $this->isActive = $isActive;
        $this->emailVerified = $emailVerified;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getID(): string{return $this->id; }
    public function getFullName(): string{return $this->fullName; }
    public function getEmail(): string{return $this->email; }
    public function getPasswordHash(): string{return $this->passwordHash; }
    public function getWalletTransactionPinHash(): string{return $this->walletTransactionPinHash; }
    public function getIsActive(): bool{return $this->isActive; }
    public function getEmailVerified(): bool{return $this->emailVerified; }
    public function getCreatedAt(){return $this->createdAt; }
    public function getUpdatedAt(){return $this->updatedAt; }
    
    public function setFullName(string $fullName): void{$this->fullName = $fullName; }
    public function setEmail(string $email): void{$this->email = $email; }
    public function setPasswordHash(string $passwordHash): void{$this->passwordHash = $passwordHash; }
    public function setWalletTransactionPinHash(string $walletTransactionPin): void{$this->walletTransactionPin = $walletTransactionPin; }
    public function setIsActive(string $isActive): void{$this->isActive = $isActive; }
    public function setEmailVerified(string $emailVerified): void{$this->emailVerified = $emailVerified; }

    public static function mapToUserRow(array $row): User{
        $id = $row['id'];
        $fullName = $row['full_name'];
        $email = $row['email'];
        $passwordHash = $row['password_hash'];
        $walletTransactionPinHash = $row['wallet_transaction_pin_hash'];
        $isActive = (bool) $row['is_active'];
        $emailVerified = (bool) $row['email_verified'];
        $createdAt = new DateTimeImmutable($row['created_at']);
        $updatedAt = new DateTimeImmutable($row['updated_at']);

        $user = new User(
            $id, 
            $fullName, 
            $email, 
            $passwordHash, 
            $walletTransactionPinHash, 
            $isActive, 
            $emailVerified, 
            $createdAt,
            $updatedAt
        );
        return $user;
    }

    public static  function publicArrayRow(User $user): array{
        $fullName = $user->getFullName();
        $email = $user->getEmail();
        $user = [$fullName, $email];

        return $user;
    }

}