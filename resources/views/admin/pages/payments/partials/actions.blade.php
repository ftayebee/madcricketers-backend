<button class="btn btn-sm btn-soft-warning edit-payment-btn"
        data-id="{{ $p->id }}"
        data-player="{{ $p->player_id }}"
        data-type="{{ $p->type }}"
        data-amount="{{ $p->amount }}"
        data-status="{{ $p->status }}"
        data-date="{{ $p->payment_date }}">
    <i class="fa fa-edit"></i>
</button>
<button class="btn btn-sm btn-soft-danger delete-payment-btn" data-id="{{ $p->id }}">
    <i class="fa fa-trash"></i>
</button>
