<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterOrderUpdater
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function nextOrder(string $modelClass): int
    {
        return ((int) $modelClass::query()->max('order_no')) + 1;
    }

    /**
     * Swap/move a row to $newOrder. If another row already has that order, swap them.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function swap(string $modelClass, int $id, int $newOrder): void
    {
        DB::transaction(function () use ($modelClass, $id, $newOrder) {
            /** @var Model $row */
            $row = $modelClass::query()->lockForUpdate()->findOrFail($id);
            $oldOrder = $row->order_no;

            if ((int) $oldOrder === $newOrder) {
                return;
            }

            $other = $modelClass::query()
                ->lockForUpdate()
                ->where('order_no', $newOrder)
                ->where('id', '!=', $row->id)
                ->first();

            $row->update(['order_no' => null]);
            if ($other) {
                $other->update(['order_no' => $oldOrder]);
            }
            $row->update(['order_no' => $newOrder]);
        });
    }
}
