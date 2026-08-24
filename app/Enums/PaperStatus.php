<?php

namespace App\Enums;

enum PaperStatus: string
{
    case Submitted = 'submitted';
    case ForReview = 'for_review';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::ForReview => 'For review',
            self::NeedsRevision => 'Needs revision',
            self::Approved => 'Approved',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Submitted => 'Waiting in queue',
            self::ForReview => 'Sir is checking this',
            self::NeedsRevision => 'Student must resubmit',
            self::Approved => 'Cleared by adviser',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Submitted => 'bg-[#ece7de] text-[#3b372f]',
            self::ForReview => 'bg-[#d7ecf6] text-[#16384a]',
            self::NeedsRevision => 'bg-[#ffe1cc] text-[#6a3212]',
            self::Approved => 'bg-[#d8f0d8] text-[#1b3d24]',
        };
    }
}
