<?php

namespace App\Services;

use App\Models\JournalEntry;

class AccountingResult
{
    public bool $success;
    public ?string $error;
    public ?JournalEntry $journalEntry;

    public function __construct(bool $success, ?string $error = null, ?JournalEntry $journalEntry = null)
    {
        $this->success = $success;
        $this->error = $error;
        $this->journalEntry = $journalEntry;
    }

    public static function success(?JournalEntry $journalEntry = null): self
    {
        return new self(true, null, $journalEntry);
    }

    public static function failed(string $error): self
    {
        return new self(false, $error, null);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
