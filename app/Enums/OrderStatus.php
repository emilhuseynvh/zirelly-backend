<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public static function paidLike(): array
    {
        return [self::Paid, self::Preparing, self::Shipped, self::Delivered];
    }

    public function labelAz(): string
    {
        return match ($this) {
            self::Pending => 'Gözləyir',
            self::Paid => 'Ödənilib',
            self::Preparing => 'Çatdırılmaya hazırlanır',
            self::Shipped => 'Çatdırılmaya verildi',
            self::Delivered => 'Çatdırıldı',
            self::Returned => 'Qaytarıldı',
            self::Cancelled => 'Ləğv edilib',
        };
    }
}
