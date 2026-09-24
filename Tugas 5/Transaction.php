<?php

declare(strict_types=1);

class Transaction
{

    public function __construct(
        private string $id,
        private string $type,
        private float $amount
    ) {}

    public function process(float &$balance): bool
    {
        // Menggunakan match expression untuk mengeksekusi logika berdasarkan tipe
        return match ($this->type) {
            'deposit' => $this->processDeposit($balance),
            'withdraw' => $this->processWithdraw($balance),
            default => false,
        };
    }

    private function processDeposit(float &$balance): bool
    {
        $balance += $this->amount;
        return true;
    }

    private function processWithdraw(float &$balance): bool
    {
        // Menolak penarikan jika saldo tidak mencukupi
        if ($balance < $this->amount) {
            return false; 
        }
        $balance -= $this->amount;
        return true;
    }

    // Getters untuk mengambil data private dengan aman
    public function getId(): string { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getAmount(): float { return $this->amount; }
}