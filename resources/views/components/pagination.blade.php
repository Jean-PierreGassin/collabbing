<div class="row justify-content-center mb-3">
    @if (isset($container))
        {{ $data->fragment($container)->links() }}
    @else
        {{ $data->links() }}
    @endif
</div>