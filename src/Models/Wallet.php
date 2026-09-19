<?php

class Wallet{
    private string $id;
    private string $userId;
    private float $balance;
    private string $walletTag;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct
        (
            string $id, 
            string $userId,
            float $balance,
            string $walletTag,
            DateTime $createdAt,
            DateTime $updatedAt
        )
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->balance = $balance;
        $this->walletTag = $walletTag;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getID(){ return $this->id; }
    public function getUserId(){ return $this->userId; }
    public function getBalance(){ return $this->balance; }
    public function getWalletTag(){ return $this->walletTag; }
    public function getCreatedAt(){ return $this->createdAt;}
    public function getUpdatedAt(){ return $this->updatedAt; }

    public function setBalance(float $balance): void{$this->balance = $balance; }
    public function setWalletTag(string $walletTag): void{$this->walletTag = $walletTag; }
    public function setCreatedAt(DateTime $createdAt): void{$this->createdAt = $createdAt; }
    public function setUpdatedAt(DateTime $updatedAt): void{$this->updatedAt = $updatedAt; }
    
}