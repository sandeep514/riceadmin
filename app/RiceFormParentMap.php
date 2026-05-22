<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceFormParentMap extends Model
{
    protected $table = 'rice_form_parent_maps';

    protected $fillable = [
        'parent_form_id',
        'child_form_ids',
        'status',
    ];

    protected $casts = [
        'child_form_ids' => 'array',
        'status' => 'integer',
    ];

    public function parentForm()
    {
        return $this->belongsTo(RiceFormMilestone3::class, 'parent_form_id');
    }

    public function getChildFormNamesAttribute(): string
    {
        if (! $this->child_form_ids || $this->child_form_ids === []) {
            return '-';
        }

        return RiceFormMilestone3::whereIn('id', $this->child_form_ids)
            ->orderBy('order')
            ->pluck('name')
            ->implode(', ');
    }
}
