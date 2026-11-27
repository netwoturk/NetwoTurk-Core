# Influencer Ajans – Yayıncı Ödül Sistemi 1.1.0

PrestaShop 8.2.x için aylık TikTok XLSX raporundan yayıncı puanı hesaplayan bağımsız modül.

Gereksinimler: PHP 8.1–8.4, `bcmath`, `zip`, `SimpleXML`, `openssl` ve `curl` uzantıları.

## Kurulum

1. `ntcreatorrewards-1.0.0.zip` dosyasını Modül Yöneticisi üzerinden yükleyin.
2. **Yayıncı Ödülleri → Lisans** alanında NetwoTürk lisans anahtarını etkinleştirin.
3. Müşteriler **Hesabım → Ödüllerim** içinden TikTok kullanıcı adlarını iletir. Yönetici **Yayıncılar** ekranında talebi ve Creator ID'yi doğrular.
4. Tamamlanmış aya ait TikTok XLSX raporunu **Rapor Aktarımı** ekranına yükleyin. Sistem başlıklara göre alanları bulur; yalnızca `Estimated bonus - Activeness incentive` alanını kullanır.
5. Kaynak/hariç/eşleşen toplamları kontrol edin, pozitif USD/TRY dönem kurunu girin ve dağıtımı onaylayın.

Hesap: `floor(aktiflik teşviki × %2 × USD/TRY × 1000)`. Ara yuvarlama yapılmaz. Aynı dönem/yayıncı için sonraki rapor yalnızca hedef tam puan farkını işler. Kullanılmayan puanlar devreder.

## Güvenlik ve işletim

- Creator ID metin olarak tutulur; `0` geçerli kimlik sayılmaz.
- Kullanıcının yazdığı handle doğrudan bağlanmaz; yönetici onayı gerekir.
- Başka domainli profil URL'leri ve kullanıcı adı içermeyen kısa bağlantılar reddedilir.
- XLSX formülleri çalıştırılmaz; formül içeren sayfa incelemeye alınır. Yükleme sınırı 20 MB, açılmış sayfa sınırı 128 MB ve satır sınırı 100.000'dir.
- Dağıtım transaction ve idempotency anahtarlarıyla korunur. Modülü kaldırmak finansal tabloları silmez.
- Lisans anahtarı düz metin loglanmaz. Lisans merkezi yalnızca HTTPS üzerinden kullanılır.

## Ödül ve talep akışları

- Yönetici hediye ve keşfet paketlerini katalogdan tanımlar. Hediye talebi puanı ve stoğu transaction içinde ayırır.
- Keşfet talebi İstanbul saatinde alınır, UTC saklanır; minimum ön bildirim ve slot kapasitesi sunucuda doğrulanır. Harici TikTok entegrasyonu olmadığı için zamanı gelen işler otomatik tamamlanmaz, manuel inceleme kuyruğuna düşer.
- Nakit çekim yayıncı bazında açılır. Asgari 100 TL, `1.000 puan = 1 TL`; Türk IBAN formatı ve MOD-97 checksum doğrulanır.
- Talep durum geçişleri sunucuda sınırlandırılır. Rezerv kesin harcamaya dönüşürken ikinci kesinti yapılmaz; ret/iptal rezervi tek sefer çözer.
- Bildirimler outbox tablosunda idempotent tutulur. `php modules/ntcreatorrewards/cron/process.php` komutu e-posta kuyruğunu ve zamanı gelen manuel keşfet işlerini küçük partilerle işler.
- Yönetici tarih aralığına göre Türkçe PDF talep raporu indirebilir.

## Sınırlar

Bu paket TikTok'a bağlanmaz ve keşfet hizmetini otomatik başlatmaz. Keşfet desteğinin gerçek uygulanması yönetici görev kuyruğunda manuel olarak doğrulanmalıdır. Banka transferi otomatik yapılmaz; `Ödendi` durumu yalnızca gerçek ödemenin yönetici kaydıdır.

## Test

`php tests/run.php path/to/reference.xlsx`

PrestaShop test mağazasında kurulumdan önce veritabanı yedeği alın. Üretime doğrudan yüklemek yerine staging ortamında gerçek tema ve çalışan yetkileriyle kabul testi yapın.
