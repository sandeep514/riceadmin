<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorContainerParticular extends Model
{
    protected $table = 'vendor_container_particulars';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public const INPUT_TYPE_TEXT = 'text';

    public const INPUT_TYPE_NUMBER = 'number';

    public const INPUT_TYPE_TEXTAREA = 'textarea';

    public const INPUT_TYPE_SELECT = 'select';

    public const INPUT_TYPE_DATE = 'date';

    public const INPUT_TYPE_CHECKBOX = 'checkbox';

    protected $fillable = [
        'particular',
        'input_type',
        'description',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    public static function inputTypeOptions(): array
    {
        return [
            self::INPUT_TYPE_TEXT => 'Text',
            self::INPUT_TYPE_NUMBER => 'Number',
            self::INPUT_TYPE_TEXTAREA => 'Textarea',
            self::INPUT_TYPE_SELECT => 'Select',
            self::INPUT_TYPE_DATE => 'Date',
            self::INPUT_TYPE_CHECKBOX => 'Checkbox',
        ];
    }

    public static function options(?int $includeId = null): array
    {
        return self::query()
            ->where(function ($query) use ($includeId) {
                $query->where('status', self::STATUS_ACTIVE);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderBy('particular')
            ->pluck('particular', 'id')
            ->toArray();
    }
}
