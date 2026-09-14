{{--
  Required: $product, $route (named route for toggle status), $kind (catalog kind)
--}}
@if((int) $product->status !== 1)
    <form method="POST"
          action="{{ route($route, $product->id) }}"
          style="display:inline-block;"
          onsubmit="return confirm('Verify and show this product on front?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">Verify</button>
    </form>
@else
    <button type="button"
            class="btn btn-sm btn-danger js-vendor-product-deactivate"
            data-action="{{ route($route, $product->id) }}"
            data-product-id="{{ $product->id }}">
        De-activate
    </button>
@endif
@if(!empty($kind))
    <button type="button"
            class="btn btn-sm btn-warning js-vendor-product-ask-vendor"
            data-action="{{ route('ask.vendor.product', ['kind' => $kind, 'id' => $product->id]) }}"
            data-product-id="{{ $product->id }}">
        Ask vendor
    </button>
@endif
