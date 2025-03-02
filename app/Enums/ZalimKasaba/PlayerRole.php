<?php

namespace App\Enums\ZalimKasaba;

use App\Models\ZalimKasaba\Player;

enum PlayerRole: string
{
    case GODFATHER = 'godfather';
    case MAFIOSO = 'mafioso';
    case JANITOR = 'janitor';
    case DOCTOR = 'doctor';
    case LOOKOUT = 'lookout';
    case ANGEL = 'angel';
    case HUNTER = 'hunter';
    case WITCH = 'witch';
    case GUARD = 'guard';
    case JESTER = 'jester';

    public static function getMafiaRoles(): array
    {
        return [
            self::GODFATHER,
            self::MAFIOSO,
            self::JANITOR,
        ];
    }

    public static function getTownRoles(): array
    {
        return [
            self::DOCTOR,
            self::LOOKOUT,
            self::GUARD,
            self::HUNTER
        ];
    }

    public static function getChaosRoles(): array
    {
        return [
            self::WITCH,
        ];
    }

    public static function getNeutralRoles(): array
    {
        return [
            self::JESTER,
            self::ANGEL,
        ];
    }

    public function getFaction(): string
    {
        if (in_array($this, PlayerRole::getMafiaRoles())) {
            return 'Mafya 🌹';
        } elseif (in_array($this, PlayerRole::getTownRoles())) {
            return 'Kasaba 🏘️';
        } elseif (in_array($this, PlayerRole::getChaosRoles())) {
            return 'Kaos 🌀';
        } elseif (in_array($this, PlayerRole::getNeutralRoles())) {
            return 'Tarafsız 🕊️';
        } else {
            return 'Bilinmiyor';
        }
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GODFATHER => 'Mafyanın liderisin. Her gece birinin öldürülmesi için emir ver.',
            self::MAFIOSO => 'Baron tarafından sana verilen emirleri yerine getir. Baron yoksa, kendi kararını ver.',
            self::JANITOR => 'Mafya tarafından öldürülen kişinin rolünü temizle.',
            self::DOCTOR => 'Kendini ya da başkasını koru. Kendini sadece bir kez koruyabilirsin.',
            self::LOOKOUT => 'Birinin evini dikizle ve kimlerin onu ziyaret ettiğini öğren.',
            self::GUARD => 'Birine saatlerce GBT sorgusu yap ve hedefinin o gece yeteneğini kullanmasına engel ol.',
            self::HUNTER => 'Geceleri silahını kullanarak birini vurabilirsin. Vurduğun kişi masum biriyse, intihar edersin.',
            self::WITCH => 'Geceleri birini zehirleyebilirsin. Zehirlenen kişi, ertesi gece ölür, panzehiri yoktur.',
            self::JESTER => 'Tek amacın idam edilmek. Eğer idam edilirsen, gece bir oyuncuyu lanetleyebilirsin.',
            self::ANGEL => 'Geceleri güzelliğini kullanarak gelen saldırıları engelle. Oyunun sonuna kadar hayatta kalmalısın.',
        };
    }

    public function getGoal(): string
    {
        if (in_array($this, PlayerRole::getMafiaRoles())) {
            return 'Mafyaya boyun eğmeyen herkesi yok et.';
        } elseif (in_array($this, PlayerRole::getTownRoles())) {
            return 'Kasabadaki bütün kötüleri yok et.';
        } elseif (in_array($this, PlayerRole::getChaosRoles())) {
            return 'Kasaba halkının yok olma hayali ile yanıp tutuşuyorsun.';
        } elseif (in_array($this, PlayerRole::getNeutralRoles())) {
            return 'Sadece kendi hedefinize ulaşın.';
        } else {
            return 'Bilinmiyor';
        }
    }
}
