<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPage('return-policy', 'Geri Qaytarma və Dəyişdirmə Siyasəti', $this->returnPolicyContent());
        $this->seedPage('privacy-policy', 'Məxfilik Siyasəti', $this->privacyPolicyContent());
    }

    private function seedPage(string $slug, string $title, string $content): void
    {
        $page = LegalPage::forSlug($slug);

        if ($page->translate('content', 'az') !== null) {
            return;
        }

        $page->syncTranslations([
            'az' => [
                'title' => $title,
                'content' => $content,
            ],
        ]);
    }

    private function returnPolicyContent(): string
    {
        return <<<'HTML'
<p>Zirelly olaraq məqsədimiz hər bir müştərimizə yüksək keyfiyyətli məhsullar və etibarlı alış-veriş təcrübəsi təqdim etməkdir. Müştəri məmnuniyyəti bizim üçün əsas dəyərlərdən biridir və hər bir müraciətə operativ, şəffaf və ədalətli şəkildə yanaşırıq.</p>
<p>Bu Geri Qaytarma və Dəyişdirmə Siyasəti Azərbaycan Respublikasının &ldquo;İstehlakçıların hüquqlarının müdafiəsi haqqında&rdquo; Qanununa və Azərbaycan Respublikası Nazirlər Kabinetinin 21 may 1998-ci il tarixli, 114 nömrəli Qərarı ilə təsdiq edilmiş qaydalara uyğun hazırlanmışdır.</p>
<h2>1. İstehsal qüsuru olan məhsullar</h2>
<p>Əgər əldə etdiyiniz məhsulda istehsal (zavod) qüsuru aşkar olunarsa, məhsulun dəyişdirilməsi və ya ödənilmiş məbləğin geri qaytarılması mümkündür.</p>
<p>Bunun üçün məhsulu əldə etdikdən sonra mümkün qədər qısa müddətdə bizimlə əlaqə saxlamalı, sifariş nömrəsini və ya alış sənədini təqdim etməli, həmçinin qüsuru əks etdirən foto və ya video göndərməlisiniz. Zəruri hallarda məhsul əlavə ekspertiza üçün tələb oluna bilər.</p>
<p>Qüsurun istehsal mənşəli olduğu təsdiqləndikdə, Zirelly MMC məhsulu yenisi ilə əvəz edəcək və ya ödənilmiş məbləği tam şəkildə geri qaytaracaq.</p>
<h2>2. Şəxsi istək əsasında geri qaytarma</h2>
<p>Zirelly tərəfindən satılan məhsullar kosmetik məhsullar kateqoriyasına daxildir.</p>
<p>Azərbaycan Respublikası Nazirlər Kabinetinin 21 may 1998-ci il tarixli, 114 nömrəli Qərarı ilə təsdiq edilmiş &ldquo;Azərbaycan Respublikası ərazisində pərakəndə ticarət obyektlərində dəyişdirilməli olmayan malların siyahısı&rdquo;na əsasən parfümer-kosmetika malları istehsal qüsuru olmadığı halda geri qaytarılmır və dəyişdirilmir.</p>
<p>Bu səbəbdən aşağıdakı hallar geri qaytarma üçün əsas hesab edilmir:</p>
<ul>
<li>Məhsulun bəyənilməməsi;</li>
<li>Məhsulun qoxusunun, rənginin və ya teksturasının gözləntilərə uyğun olmaması;</li>
<li>Yanlış məhsul seçilməsi;</li>
<li>Fikrin dəyişməsi;</li>
<li>Məhsul istifadə edildikdən sonra gözlənilən nəticənin əldə olunmaması.</li>
</ul>
<h2>3. Yanlış və ya zədələnmiş sifariş</h2>
<p>Əgər sifarişiniz:</p>
<ul>
<li>yanlış məhsulla göndərilibsə;</li>
<li>daşınma zamanı zədələnibsə;</li>
<li>natamam çatdırılıbsa,</li>
</ul>
<p>məhsulu istifadə etmədən bizimlə əlaqə saxlamanızı xahiş edirik.</p>
<p>Müraciət araşdırıldıqdan sonra məhsul pulsuz şəkildə dəyişdiriləcək və ya ödənilmiş məbləğ tam şəkildə geri qaytarılacaq.</p>
<h2>4. Pulun geri qaytarılması</h2>
<p>Geri qaytarılması təsdiq edilmiş sifarişlər üzrə ödəniş sifariş zamanı istifadə olunmuş ödəniş üsulu ilə həyata keçirilir.</p>
<p>Bank kartı ilə edilmiş ödənişlərin karta geri köçürülməsi bankın daxili prosedurlarından asılı olaraq bir neçə iş günü çəkə bilər.</p>
<h2>5. Bizimlə əlaqə</h2>
<p>Geri qaytarma və ya dəyişdirmə ilə bağlı hər hansı sualınız yarandıqda bizimlə əlaqə saxlaya bilərsiniz.</p>
<p><strong>Zirelly MMC</strong><br>E-poçt: <a href="mailto:info@zirelly.az">info@zirelly.az</a><br>Telefon: <a href="tel:+994512522410">+994 51 252 24 10</a><br>İş saatları: Bazar ertəsi &ndash; Cümə, 09:00&ndash;18:00</p>
<h2>Hüquqi əsas</h2>
<p>Bu siyasət aşağıdakı normativ hüquqi aktlara əsasən hazırlanmışdır:</p>
<ul>
<li>Azərbaycan Respublikasının &ldquo;İstehlakçıların hüquqlarının müdafiəsi haqqında&rdquo; Qanunu;</li>
<li>Azərbaycan Respublikası Nazirlər Kabinetinin 21 may 1998-ci il tarixli, 114 nömrəli Qərarı ilə təsdiq edilmiş &ldquo;Azərbaycan Respublikası ərazisində pərakəndə ticarət obyektlərində dəyişdirilməli olmayan malların siyahısı&rdquo;.</li>
</ul>
<p>Həmin qərara əsasən parfümer-kosmetika malları istehsal qüsuru istisna olmaqla geri qaytarılmır və dəyişdirilmir.</p>
HTML;
    }

    private function privacyPolicyContent(): string
    {
        return <<<'HTML'
<p>Zirelly MMC olaraq müştərilərimizin şəxsi məlumatlarının məxfiliyinə və təhlükəsizliyinə böyük önəm veririk. Bu Məxfilik Siyasəti www.zirelly.az internet saytından istifadə etdiyiniz zaman şəxsi məlumatlarınızın hansı məqsədlərlə toplandığını, necə istifadə edildiyini və necə qorunduğunu izah edir.</p>
<p>Veb-saytdan istifadə etməklə bu Məxfilik Siyasəti ilə razılaşdığınızı təsdiq etmiş olursunuz.</p>
<h2>1. Topladığımız məlumatlar</h2>
<p>Zirelly MMC aşağıdakı məlumatları toplaya bilər:</p>
<ul>
<li>Ad və soyad;</li>
<li>Mobil telefon nömrəsi;</li>
<li>Elektron poçt ünvanı;</li>
<li>Çatdırılma ünvanı;</li>
<li>Sifariş və ödəniş məlumatları;</li>
<li>Saytdan istifadə zamanı texniki məlumatlar (IP ünvanı, brauzer növü, cihaz məlumatları, cookie faylları və s.).</li>
</ul>
<h2>2. Məlumatlardan istifadə məqsədi</h2>
<p>Toplanan məlumatlar aşağıdakı məqsədlər üçün istifadə olunur:</p>
<ul>
<li>sifarişlərin qəbul edilməsi və icrası;</li>
<li>ödənişlərin emalı;</li>
<li>məhsulların çatdırılması;</li>
<li>müştəri dəstəyi göstərilməsi;</li>
<li>sifariş statusu barədə məlumatlandırma;</li>
<li>xidmət keyfiyyətinin yaxşılaşdırılması;</li>
<li>qanunvericiliyin tələblərinə əməl edilməsi;</li>
<li>istifadəçinin razılığı olduğu halda kampaniya, endirim və yeniliklər barədə məlumat göndərilməsi.</li>
</ul>
<h2>3. Şəxsi məlumatların qorunması</h2>
<p>Zirelly MMC təqdim etdiyiniz şəxsi məlumatların təhlükəsizliyini təmin etmək üçün müasir texniki və təşkilati təhlükəsizlik tədbirlərindən istifadə edir.</p>
<p>Şəxsi məlumatlar Azərbaycan Respublikasının qanunvericiliyinə uyğun şəkildə qorunur və yalnız bu siyasətdə göstərilən məqsədlər üçün istifadə olunur.</p>
<h2>4. Məlumatların üçüncü şəxslərə ötürülməsi</h2>
<p>Şəxsi məlumatlarınız sizin razılığınız olmadan üçüncü şəxslərə təqdim edilmir.</p>
<p>İstisna hallarda məlumatlar aşağıdakı məqsədlərlə paylaşılır:</p>
<ul>
<li>sifarişlərin çatdırılmasını həyata keçirən kuryer və logistika şirkətləri ilə;</li>
<li>ödənişlərin həyata keçirilməsi üçün ödəniş xidmətləri ilə;</li>
<li>Azərbaycan Respublikasının qanunvericiliyi ilə tələb olunduğu hallarda dövlət və hüquq-mühafizə orqanları ilə.</li>
</ul>
<h2>5. Cookie faylları</h2>
<p>Saytımız istifadəçi təcrübəsini yaxşılaşdırmaq və saytın düzgün işləməsini təmin etmək məqsədilə cookie (çərəz) fayllarından istifadə edə bilər.</p>
<p>İstifadəçi brauzer ayarlarından cookie fayllarını məhdudlaşdıra və ya deaktiv edə bilər. Lakin bu halda saytın bəzi funksiyaları düzgün işləməyə bilər.</p>
<h2>6. Marketinq məlumatları</h2>
<p>İstifadəçinin razılığı olduğu halda Zirelly MMC elektron poçt, SMS və digər elektron rabitə vasitələri ilə kampaniyalar, endirimlər, yeni məhsullar və digər marketinq məlumatları göndərə bilər.</p>
<p>İstifadəçi istənilən vaxt bu bildirişlərdən imtina etmək hüququna malikdir.</p>
<h2>7. Digər internet resursları</h2>
<p>Saytımızda digər internet resurslarına keçidlər yerləşdirilə bilər.</p>
<p>Həmin internet resurslarının məxfilik siyasətinə və məlumatların emalına görə Zirelly MMC məsuliyyət daşımır.</p>
<h2>8. İstifadəçinin hüquqları</h2>
<p>İstifadəçi aşağıdakı hüquqlara malikdir:</p>
<ul>
<li>öz şəxsi məlumatları barədə məlumat almaq;</li>
<li>məlumatların yenilənməsini və ya düzəldilməsini tələb etmək;</li>
<li>qanunvericiliklə nəzərdə tutulmuş hallarda məlumatların silinməsini tələb etmək;</li>
<li>marketinq bildirişlərindən imtina etmək.</li>
</ul>
<h2>9. Siyasətdə dəyişikliklər</h2>
<p>Zirelly MMC bu Məxfilik Siyasətinə istənilən vaxt dəyişiklik etmək hüququnu özündə saxlayır.</p>
<p>Dəyişikliklər www.zirelly.az saytında dərc edildiyi andan qüvvəyə minir.</p>
<h2>10. Hüquqi əsas</h2>
<p>Bu Məxfilik Siyasəti aşağıdakı normativ hüquqi aktlara uyğun hazırlanmışdır:</p>
<ul>
<li>Azərbaycan Respublikasının Konstitusiyası;</li>
<li>Azərbaycan Respublikasının &ldquo;Fərdi məlumatlar haqqında&rdquo; Qanunu;</li>
<li>Azərbaycan Respublikasının &ldquo;İnformasiya əldə etmək haqqında&rdquo; Qanunu;</li>
<li>Azərbaycan Respublikasının &ldquo;İstehlakçıların hüquqlarının müdafiəsi haqqında&rdquo; Qanunu;</li>
<li>Azərbaycan Respublikasının qüvvədə olan digər normativ hüquqi aktları.</li>
</ul>
<h2>11. Əlaqə</h2>
<p>Bu Məxfilik Siyasəti ilə bağlı sual, təklif və ya müraciətiniz olduqda bizimlə əlaqə saxlaya bilərsiniz.</p>
<p><strong>Zirelly MMC</strong><br>E-poçt: <a href="mailto:info@zirelly.az">info@zirelly.az</a><br>Telefon: <a href="tel:+994512522410">+994 51 252 24 10</a><br>Veb-sayt: <a href="https://www.zirelly.az">www.zirelly.az</a></p>
HTML;
    }
}
