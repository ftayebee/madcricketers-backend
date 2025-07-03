<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Dashboard</h5>
        </div>
        <ul class="breadcrumb">
            @foreach (session()->get('breadcrumbs', 'default') as $item)
                @if ($item['url'])
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                @else
                    <li class="breadcrumb-item">Dashboard</li>
                @endif
            @endforeach
        </ul>
    </div>
    <div class="page-header-right ms-auto">

    </div>
</div>
