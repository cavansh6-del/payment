<div class="container mt-4">
    <h1 class="mb-4 mt-6">Order Payment Details</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary">
            <h2>Order Information</h2>
        </div>
        <div class="card-body">
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Order Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
            <p><strong>Order Status:</strong> {{ $order->status }}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h2>Payment Information</h2>
        </div>
        <div class="card-body">
            @if ($orderPayment)
                <p><strong>Payer Name:</strong> {{ $orderPayment->payer_name }}</p>
                <p><strong>Bank Info:</strong> {{ $orderPayment->payer_bank_info }}</p>
                <p><strong>Payment Receipt:</strong>
                    <a href="{{ Storage::url($orderPayment->receipt_path) }}" class="btn btn-success" target="_blank">
                        <img src="{{ Storage::url($orderPayment->receipt_path) }}" />
                    </a>
                </p>
            @else
                <p>No payment information found for this order.</p>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between">

        <x-filament::button wire:click="updateOrderStatus('cancelled')" class="mt-4" color="danger">
            Reject Order
        </x-filament::button>

        <x-filament::button wire:click="updateOrderStatus('completed')" class="mt-4" color="gray">
            Confirm Order
        </x-filament::button>

    </div>
</div>
