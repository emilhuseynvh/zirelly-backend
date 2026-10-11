<?php

namespace App\Support;

class DeliveryLocations
{
    public const BAKU = 'Bakı';

    /** Şəhərlər və rayonlar — çatdırılma ünvanı üçün rəsmi siyahı */
    public const CITIES = [
        'Bakı',
        'Sumqayıt',
        'Gəncə',
        'Mingəçevir',
        'Naxçıvan',
        'Şəki',
        'Şirvan',
        'Lənkəran',
        'Yevlax',
        'Naftalan',
        'Xankəndi',
        'Abşeron',
        'Ağcabədi',
        'Ağdam',
        'Ağdaş',
        'Ağstafa',
        'Ağsu',
        'Astara',
        'Balakən',
        'Beyləqan',
        'Bərdə',
        'Biləsuvar',
        'Cəbrayıl',
        'Cəlilabad',
        'Culfa',
        'Daşkəsən',
        'Füzuli',
        'Gədəbəy',
        'Goranboy',
        'Göyçay',
        'Göygöl',
        'Hacıqabul',
        'İmişli',
        'İsmayıllı',
        'Kəlbəcər',
        'Kürdəmir',
        'Qax',
        'Qazax',
        'Qəbələ',
        'Qobustan',
        'Quba',
        'Qubadlı',
        'Qusar',
        'Laçın',
        'Lerik',
        'Masallı',
        'Neftçala',
        'Oğuz',
        'Ordubad',
        'Saatlı',
        'Sabirabad',
        'Salyan',
        'Samux',
        'Siyəzən',
        'Şabran',
        'Şamaxı',
        'Şəmkir',
        'Şərur',
        'Şuşa',
        'Tərtər',
        'Tovuz',
        'Ucar',
        'Xaçmaz',
        'Xızı',
        'Xocalı',
        'Xocavənd',
        'Yardımlı',
        'Zaqatala',
        'Zəngilan',
        'Zərdab',
    ];

    /** Naxçıvan MR əraziləri (çatdırılma zonası üçün) */
    public const NAKHCHIVAN = ['Naxçıvan', 'Culfa', 'Ordubad', 'Şərur'];

    /** Qarabağ əraziləri (çatdırılma zonası üçün) */
    public const KARABAKH = [
        'Xankəndi', 'Şuşa', 'Xocalı', 'Xocavənd', 'Ağdam', 'Füzuli',
        'Cəbrayıl', 'Qubadlı', 'Zəngilan', 'Laçın', 'Kəlbəcər',
    ];

    /** Bakının inzibati rayonları */
    public const BAKU_DISTRICTS = [
        'Binəqədi',
        'Nərimanov',
        'Nəsimi',
        'Nizami',
        'Pirallahı',
        'Qaradağ',
        'Sabunçu',
        'Səbail',
        'Suraxanı',
        'Xətai',
        'Xəzər',
        'Yasamal',
    ];

    public static function zoneFor(string $city): string
    {
        return match (true) {
            $city === self::BAKU => 'baku',
            in_array($city, self::NAKHCHIVAN, true) => 'nakhchivan',
            in_array($city, self::KARABAKH, true) => 'karabakh',
            default => 'region',
        };
    }

    /**
     * Ünvana və malların yekununa (endirimdən sonra) görə çatdırılma haqqı.
     */
    public static function feeFor(string $city, float $goodsTotal, \App\Models\DeliverySetting $settings): float
    {
        return match (self::zoneFor($city)) {
            'baku' => $goodsTotal >= (float) $settings->baku_free_threshold
                ? 0.0
                : (float) $settings->baku_fee,
            'nakhchivan' => (float) $settings->nakhchivan_fee,
            'karabakh' => (float) $settings->karabakh_fee,
            default => (float) $settings->region_fee,
        };
    }

    /**
     * Struktur sahələrdən vahid ünvan sətri qurur:
     * "Bakı, Nərimanov rayonu, Qaçaq Nəbi küçəsi 9A, mənzil 24"
     */
    public static function compose(
        string $city,
        ?string $district,
        string $street,
        string $building,
        ?string $apartment,
    ): string {
        $parts = [$city];

        if (filled($district)) {
            $parts[] = $district.' rayonu';
        }

        $parts[] = trim($street.' '.$building);

        if (filled($apartment)) {
            $parts[] = 'mənzil '.$apartment;
        }

        return implode(', ', $parts);
    }
}
