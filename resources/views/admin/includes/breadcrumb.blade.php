<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold">{{session()->get('title')}}</h4>
            <ol class="breadcrumb mb-0">
                @foreach (session()->get('breadcrumbs', []) as $item)
                    <li class="breadcrumb-item">
                        @if (!empty($item['url']))
                            <a href="{{ $item['url'] }}">{{ $item['name'] ?? 'Untitled' }}</a>
                        @else
                            {{ $item['name'] ?? 'Untitled' }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
