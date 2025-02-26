<?php

namespace App\Enums\ZalimKasaba;

enum PlayerRole: string
{
    case GODFATHER = 'godfather';
    case MAFIOSO = 'mafioso';
    case JANITOR = 'janitor';
    case DOCTOR = 'doctor';
    case LOOKOUT = 'lookout';
    case HUNTER = 'hunter';
    case GUARD = 'guard';
    case JESTER = 'jester';

    public function getMafiaRoles(): array
    {
        return [
            self::GODFATHER,
            self::MAFIOSO,
            self::JANITOR,
        ];
    }

    public function getTownRoles(): array
    {
        return [
            self::DOCTOR,
            self::LOOKOUT,
            self::HUNTER,
            self::GUARD,
        ];
    }

    public function getNeutralRoles(): array
    {
        return [
            self::JESTER,
        ];
    }

    public function getFaction(): string
    {
        if (in_array($this, $this->getMafiaRoles())) {
            return 'Mafya';
        } elseif (in_array($this, $this->getTownRoles())) {
            return 'Kasaba';
        } elseif (in_array($this, $this->getNeutralRoles())) {
            return 'Tarafsız';
        }
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GODFATHER => 'Mafyanın liderisin. Her gece birinin öldürülmesi için emir ver.',
            self::MAFIOSO => 'Polat tarafından sana verilen emirleri yerine getir.',
            self::DOCTOR => 'Kendini ya da başkasını koru. Kendini sadece bir kez koruyabilirsin.',
            self::LOOKOUT => 'Birinin evini dikizle ve kimlerin onu ziyaret ettiğini öğren.',
            self::HUNTER => 'Her gece birini vurabilirsin. Eğer vurduğun kişi masumsa, intihar edersin.',
            self::GUARD => 'Birine saatlerce GBT sorgusu yap ve o gece yeteneğini kullanmasına engel ol.',
            self::JESTER => 'Tek amacın asılmak. Asıldığında, gece bir oyuncuyu lanetleyebilirsin.',
        };
    }

    public function getGoal(): string
    {
        if (in_array($this, $this->getMafiaRoles())) {
            return 'Mafyaya biat etmeyen herkesi ortadan kaldır.';
        } elseif (in_array($this, $this->getTownRoles())) {
            return 'Kasabadaki bütün kötüleri yok et.';
        } elseif (in_array($this, $this->getNeutralRoles())) {
            return 'Sadece kendi hedefinize ulaşın.';
        }
    }
}
