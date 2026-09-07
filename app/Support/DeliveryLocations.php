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
