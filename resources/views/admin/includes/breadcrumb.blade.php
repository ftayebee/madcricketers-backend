<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold">{{session()->get('title')}}</h4>
            <ol class="breadcrumb mb-0">
                @foreach (session()->get('breadcrumbs', 'default') as $item)
                    @if ($item['url'])
                        <li class="breadcrumb-item"><a href="{{$item['url']}}">{{$item['name']}}</a></li>
                    @else
                        <li class="breadcrumb-item">{{$item['name']}}</li>
                    @endif
                @endforeach
            </ol>
        </div>
    </div>
</div>
