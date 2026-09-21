<?php

class User{
    private string $id;
    private string $fullName;
    private string $email;
    private string $passwordHash;
    private string $walletTransactionPin;
    private bool $isActive;
    private bool $isVerified;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct
        (
            string $id, 
            string $fullName, 
            string $email, 
            string $passwordHash, 
            string $walletTransactionPin, 
            bool $isActive, 
            bool $isVerified, 
            DateTime $createdAt, 
            DateTime $updatedAt
        )
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->walletTransactionPin = $walletTransactionPin;
        $this->isActive = true;
        $this->isVerified = false;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string{return $this->id; }
    public function getFullName(): string{return $this->fullName; }
    public function getEmail(): string{return $this->email; }
    public function getPasswordHash(): string{return $this->passwordHash; }
    public function getWalletTransactionPin(): string{return $this->walletTransactionPin; }
    public function getIsActive(): string{return $this->isActive; }
    public function getIsVerified(): string{return $this->isVerified; }
    public function getCreatedAt(): DateTime{return $this->createdAt; }
    public function getUpdatedAt(): DateTime{return $this->updatedAt; }
    
    public function setFullName(string $fullName): void{$this->fullName = $fullName; }
    public function setEmail(string $email): void{$this->email = $email; }
    public function setPasswordHash(string $password_hash): void{$this->passwordHash = $password_hash; }
    public function setWalletTransactionPin(string $walletTransactionPin): void{$this->walletTransactionPin = $walletTransactionPin; }
    public function setIsActive(string $isActive): void{$this->isActive = $isActive; }
    public function setIsVerified(string $isVerified): void{$this->isVerified = $isVerified; }

}