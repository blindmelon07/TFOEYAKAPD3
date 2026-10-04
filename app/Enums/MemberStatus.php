<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Deceased = 'deceased';

    /**
     * Get the human-readable name of the status.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Determine whether a member with this status may sign in.
     */
    public function canSignIn(): bool
    {
        return in_array($this, [self::Active, self::Inactive], true);
    }

    /**
     * Get every status as a value/label pair for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
