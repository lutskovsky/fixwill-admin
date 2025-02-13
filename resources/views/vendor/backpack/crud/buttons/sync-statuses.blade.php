@if ($crud->hasAccess('list'))
    <form action="{{ route('sync.statuses') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="la la-sync"></i>&nbsp;Синхронизировать с Ремонлайн
        </button>
    </form>
@endif
