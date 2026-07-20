<div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;">
    {!! Form::label('web_categories', 'Web categories') !!}
    <p class="help-block" style="font-size:12px;margin-top:0;">
        Select one or more categories for this trade.
        <strong>Required when Send notification = Yes</strong> (recipients are matched by category).
        <label style="font-weight:normal;margin-left:10px;display:inline;white-space:nowrap;">
            <input type="checkbox" id="trade-web-categories-select-all" title="Select or clear all categories"> All
        </label>
    </p>
    <div id="trade-web-categories-grid" class="row" style="clear:both;max-height:260px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:10px 6px;background:#fafafa;">
        @forelse(($categoryList ?? collect()) as $cat)
            <div class="col-md-3 col-sm-4 col-xs-6" style="margin-bottom:8px;">
                <label style="font-weight:normal;margin-bottom:0;">
                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                        {{ in_array((int) $cat->id, array_map('intval', $selectedTradeCategoryIds ?? []), true) ? 'checked' : '' }}>
                    {{ $cat->category }}
                </label>
            </div>
        @empty
            <div class="col-md-12 text-muted" style="padding:8px;">No active web categories found.</div>
        @endforelse
    </div>
</div>
{{-- Select-all uses iCheck (components/scripts); JS lives in trade form @section('javascript'). --}}
