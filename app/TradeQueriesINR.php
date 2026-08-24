<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\RiceName;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;
use App\Buyerpackinginr;
use App\PublicPacking;
use App\TradeLike;

class TradeQueriesINR extends Model
{
    protected $table = 'trade_query_milestone3';

    protected $fillable = ['tradeFor','queryId','farmingType','quality_type','quality','qualityForm' ,'qualityFormLinkWithLivePrice','stateLinkWithLivePrice' ,'grade','packing','quantity','offerPrice','validDays','packing_file','video_file','packingStreamType','uncooked_file','uncooked_file1','uncooked_file2','uncooked_file3','cooked_file','cooked_file1','cooked_file2','cooked_file3','additioanlInfo','personal_remarks','location','crop','hotdeal','is_new','valid_datetime_for_is_new','tradeType','moisture','kett','broken','dd','admixture','elongation','riceSize','sntcLotNo','sold_at','status','role_id','category_id'];

    public static $tradeStatus = [ 
        3 => "sold", 
        2 => 'expired' , 
        1 => 'Pending',
        6 => 'Active',
        4 => 'In-Process',
        5 => 'De-active',
        11 => 'close', 
        12 => 'hold'
    ];



    public static $tradeType = [ 
        1 => 'Buy', 
        2 => 'Sell' , 
        3 => 'Future Buying',
        4 => 'Future Selling'
    ];
    
    public static $riceSize = [ 
        1 => 'Full Grain',
        2 => 'Broken',
        3 => 'Sizer',
        4 => 'Resort',
    ];

    public static $farmingType = [
        1 => 'Conventional',
        2 => 'compliance/organic',
    ];

    /** Web portal / trade filter farming types. */
    public static $farmingTypeWeb = [
        1 => 'Conventional',
        3 => 'EU Standards',
        4 => 'US Standards',
        2 => 'Organic',
    ];

    /**
     * Human-readable farming type for API responses (web labels, then legacy admin labels).
     */
    public static function resolveFarmingName($farmingType): ?string
    {
        $key = static::resolveFarmingId($farmingType);
        if ($key === null) {
            return null;
        }

        return static::$farmingTypeWeb[$key] ?? static::$farmingType[$key] ?? null;
    }

    /**
     * Normalize stored farming value (id or legacy label) to a farming type id.
     */
    public static function resolveFarmingId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $id = (int) $value;

            return $id > 0 ? $id : null;
        }

        $normalized = strtolower(trim((string) $value));
        foreach (array_merge(static::$farmingTypeWeb, static::$farmingType) as $id => $name) {
            if (strtolower((string) $name) === $normalized) {
                return (int) $id;
            }
        }

        return null;
    }

    public function RiceFormMilestone3()
    {
        return $this->belongsTo(RiceFormMilestone3::class , 'qualityForm', 'id');
    }

    public function RiceQualityMaster()
    {
        return $this->belongsTo(QualityMaster::class , 'quality', 'id');
    }
    public function RiceNameData()
    {
        return $this->belongsTo(RiceName::class , 'quality', 'id');
    }
    public function RiceFormData()
    {
        return $this->belongsTo(RiceForm::class , 'qualityFormLinkWithLivePrice', 'id');
    }

    public function riceGrade()
    {
        return $this->belongsTo(WandModel::class , 'grade', 'id');
    }

    public function RicePacking()
    {
        return $this->belongsTo(SellerPackingINR::class , 'packing', 'id');
    }

    public static function packingLabel($row): string
    {
        $packing = trim((string) ($row->packing ?? ''));
        $description = trim((string) ($row->description ?? ''));
        $size = trim((string) ($row->size ?? ''));
        if ($size !== '' && $packing !== '') {
            return trim($size.' '.$packing);
        }
        if ($packing !== '' && $description !== '' && strcasecmp($packing, $description) !== 0) {
            return trim($packing.' '.$description);
        }

        return $description !== '' ? $description : ($packing !== '' ? $packing : $size);
    }

    public static function packingOptionsForTradeType($tradeType)
    {
        $rows = (int) $tradeType === 2
            ? Buyerpackinginr::query()->orderBy('id')->get()
            : SellerPackingINR::query()->orderBy('id')->get();

        return $rows->map(function ($row) {
            $row->label = self::packingLabel($row);

            return $row;
        });
    }

    public static function publicPackingOptions()
    {
        return PublicPacking::query()
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id')
            ->get()
            ->map(function ($row) {
                $row->label = self::packingLabel($row);

                return $row;
            });
    }

    public static function packingOptionsForTrade($tradeType, $packingStreamType = 1)
    {
        if ((int) $packingStreamType === 2) {
            return self::publicPackingOptions();
        }

        return self::packingOptionsForTradeType($tradeType);
    }

    public static function packingListsForJs(): array
    {
        $toJs = function ($rows) {
            return $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'label' => $row->label ?? self::packingLabel($row),
                ];
            })->values()->all();
        };

        $buy = $toJs(self::packingOptionsForTradeType(1));
        $sell = $toJs(self::packingOptionsForTradeType(2));
        $branded = $toJs(self::publicPackingOptions());

        return [
            'bulk' => [
                '1' => $buy,
                '2' => $sell,
                '3' => $buy,
                '4' => $buy,
            ],
            'branded' => $branded,
        ];
    }
    public function RicePackingBuyer()
    {
        return $this->belongsTo(Buyerpackinginr::class , 'packing', 'id');
    }
    public function RicePackingSeller()
    {
        return $this->belongsTo(SellerPackingINR::class , 'packing', 'id');
    }

    public function RicePackingPublic()
    {
        return $this->belongsTo(PublicPacking::class, 'packing', 'id');
    }

    public function TradeLike()
    {
        return $this->belongsTo(TradeLike::class, 'id' , 'tradeId');
    }

    public function TradeLikeAll()
    {
        return $this->hasMany(TradeLike::class, 'tradeId', 'id' );
    }

    public function TradeInterest()
    {
        return $this->belongsTo(TradeIntrested::class, 'id' , 'tradeId');
    }

    public function tradeCategoryMaps()
    {
        return $this->hasMany(TradeCategoryMap::class, 'trade_id', 'id');
    }
}
