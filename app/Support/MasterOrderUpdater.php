<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Move a row to $newOrder and shift the others between the old/new positions.
     * Finally renumbers all rows continuously as 1..N.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function move(string $modelClass, int $id, int $newOrder): void
    {
        DB::transaction(function () use ($modelClass, $id, $newOrder) {
            $rows = $modelClass::query()
                ->lockForUpdate()
                ->orderByRaw('order_no IS NULL, order_no ASC')
                ->orderBy('id')
                ->get(['id', 'order_no']);

            if ($rows->isEmpty()) {
                return;
            }

            $ids = $rows->pluck('id')->map(fn ($value) => (int) $value)->all();
            $currentIndex = array_search($id, $ids, true);
            if ($currentIndex === false) {
                throw (new ModelNotFoundException)->setModel($modelClass, [$id]);
            }

            $count = count($ids);
            $targetIndex = max(1, min((int) $newOrder, $count)) - 1;

            if ($currentIndex !== $targetIndex) {
                array_splice($ids, $currentIndex, 1);
                array_splice($ids, $targetIndex, 0, [$id]);
            }

            foreach ($ids as $index => $rowId) {
                $modelClass::query()
                    ->where('id', $rowId)
                    ->update(['order_no' => $index + 1]);
            }
        });
    }

    /**
     * Backward-compatible alias used by existing vendor-flow controllers.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function swap(string $modelClass, int $id, int $newOrder): void
    {
        self::move($modelClass, $id, $newOrder);
    }
}
