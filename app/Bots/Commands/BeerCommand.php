<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class BeerCommand extends Command
{
    // phpcs:disable Generic.Files.LineLength.TooLong
    private const BEER_OPTIONS = [
        ['Bakken', 'Cylinders', 'Knuppels', 'Halve liters', 'Pitchers', 'Blikken', 'Groene palen', 'Pretcilinders', 'Pils', 'Lager', 'Tarwe-smoothies', 'Containers', 'Goude rakkers', 'Pintekes', 'Borrels', 'Gerstenat', 'Bier', "BVO'tjes", 'Spa geel', 'Vloeibaar brood', 'Koude kletsers', 'Pijpjes', 'Natte halzen'],
        ['vouwen', 'badkuipen', 'draaikolken', 'absorberen', 'soldaat maken', 'slempen', "neem'n", 'spoelen', 'heffen', 'zuipen', 'kantelen', 'drukken', 'strepen', 'klappen', 'zuigen', 'achterover slaan', 'takelen', 'tikken', 'gieten', 'wegkolken', 'kiepen', 'wegzetten', 'kegelen', 'ontdoppen', 'harken', 'nakken', 'adten', 'rietadten', 'leegtrekken'],
        ['illustere', 'Bavarische', 'Germaanse', 'statiegeld verzamelende', 'koloniale', 'dorstige', 'uitgedroogde', '19e eeuwse', 'grootverdienende', 'prominente', 'corporale', 'Ierse', 'drooggebekte', 'gruizige', 'overwinnende', 'industriele', 'op vervroegd pensioen gestelde', 'clandestine', 'koninklijke', 'in de order van Oranje-Nassau geridderde', 'aan lager wal geraakte', 'blauwgebloede', 'royale', 'Russische', 'gelauwerde', 'welvarende'],
        ['pilsbazen', 'pikkebazen', 'zuidasridders', 'leden met woestijnkeeltjes', 'grootgrondbezitters', 'bodemloze putten', 'emballagekoningen', 'pilsrupsen', 'stuko-bazen', 'borrelaars', 'pintermannen', 'grootverdieners', 'gozergasten', 'eindbazen', 'monniken', 'heersers', 'bazen', 'megalomanen', 'drukfeuten', 'fabrieksarbeiders', 'directeuren', 'oliemagnaten', 'cowboys', 'koningen', 'tzaaren', 'eindbazen', 'monarchen', 'Iluminati', 'graaiers', 'leden', 'hockeymoeders', 'bakfietsvaders', 'labradors', 'brouwers', 'prinsen', 'landheren', 'manegehouders', 'paardjes', 'makkers', 'amices']
    ];
    // phpcs:enable

    /**
     * The name of the Telegram command.
     * @var string
     */
    protected $name = 'bier';

    /**
     * The Telegram command description.
     * @var string
     */
    protected $description = 'Bedenkt een goed excuus om bier te drinken';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     * @var array<string>
     */
    protected $aliases = ['beer'];

    /**
     * Handle the activity
     */
    public function handle()
    {
        // Get TG user
        $tgUser = $this->getTelegramUser();

        // Rate limit
        $cacheKey = sprintf('tg.beer.%s', $tgUser->id);
        if (Cache::get($cacheKey) > now()) {
            $this->replyWithMessage([
                'text' => '⏸ Rate limited (1x per min)'
            ]);
            return;
        }

        // Prep rate limit
        Cache::put($cacheKey, now()->addMinute(), now()->addMinutes(5));

        // Get user
        $user = $this->getUser();

        // Only members
        if (!$user || !$user->is_member) {
            $this->replyWithMessage([
                'text' => '🚷 Dit commando is alleen voor leden'
            ]);
            return;
        }

        // Get random lines
        $format = sprintf(
            '%s %s als %s %s!',
            Arr::random(self::BEER_OPTIONS[0]),
            Arr::random(self::BEER_OPTIONS[1]),
            Arr::random(self::BEER_OPTIONS[2]),
            Arr::random(self::BEER_OPTIONS[3])
        );

        // Send as-is
        $this->replyWithMessage([
            'text' => "🍻 $format"
        ]);
    }
}
