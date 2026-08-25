<?php

namespace App\Support;

final class DurableStorage
{
    /**
     * True when paper files are stored on durable object storage (S3/R2),
     * not ephemeral local disk (Render Free restarts wipe local files).
     */
    public static function papersAreDurable(): bool
    {
        return config('filesystems.disks.papers.driver') === 's3';
    }

    public static function requiresDriveInProduction(): bool
    {
        return app()->environment('production') && ! self::papersAreDurable();
    }
}
