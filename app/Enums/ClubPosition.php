<?php

namespace App\Enums;

enum ClubPosition: string
{
    case President = 'president';
    case VicePresident = 'vice_president';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Auditor = 'auditor';
    case Member = 'member';

    /**
     * Get the human-readable title of the position.
     */
    public function label(): string
    {
        return match ($this) {
            self::President => 'Club President',
            self::VicePresident => 'Club Vice President',
            self::Secretary => 'Club Secretary',
            self::Treasurer => 'Club Treasurer',
            self::Auditor => 'Club Auditor',
            self::Member => 'Member',
        };
    }

    /**
     * Determine whether the position is a club office (anything but a regular member).
     */
    public function isOfficer(): bool
    {
        return $this !== self::Member;
    }

    /**
     * Determine whether the position may record and delete dues payments.
     */
    public function handlesDues(): bool
    {
        return in_array($this, [self::President, self::Treasurer], true);
    }

    /**
     * Get the officer positions, of which a club may have only one each.
     *
     * @return list<self>
     */
    public static function offices(): array
    {
        return array_values(array_filter(self::cases(), fn (self $position): bool => $position->isOfficer()));
    }

    /**
     * Get every position as a value/label pair for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $position): array => ['value' => $position->value, 'label' => $position->label()],
            self::cases(),
        );
    }
}
