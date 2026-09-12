<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource\OrderResource;
use App\Models\Order;
use App\Models\Voucher;
use App\Notifications\Order\OrderCompletedNotification;
use App\Notifications\Order\OrderDeliveredNotification;
use App\Notifications\Order\OrderExpiredOrCancelledNotification;
use App\Notifications\Order\OrderPackingNotification;
use App\Notifications\Order\OrderPaidNotification;
use App\Notifications\Order\OrderShippedNotification;
use App\Services\Order\OrderStockReductionService;
use App\Services\Payment\MidtransService;
use App\Services\Payment\OrderPaymentSyncService;
use App\Services\Payment\XenditService;
use App\Services\Point\PointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    protected OrderStockReductionService $orderStockReductionService;
    protected OrderPaymentSyncService $orderPaymentSyncService;

    public function __construct(
        OrderStockReductionService $orderStockReductionService,
        OrderPaymentSyncService $orderPaymentSyncService
    ) {
        $this->orderStockReductionService = $orderStockReductionService;
        $this->orderPaymentSyncService = $orderPaymentSyncService;
    }

    /**
     * Get user's orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');
            $search = $request->input('search');

            $this->checkAndCancelExpiredOrders($user->id);

            $query = Order::with(['orderItems.review', 'user']);

            if (!$user->hasPermissionTo('orders.read')) {
                $query->where('fk_user_id', $user->id);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if (!empty($search)) {
                $query->whereRaw(
                    'UPPER(order_number) LIKE ?',
                    ['%' . strtoupper($search) . '%']
                );
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            foreach ($orders->items() as $order) {
                $this->orderPaymentSyncService->syncMidtransOrder($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => [
                    'orders' => OrderResource::collection($orders->items()),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single order by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $query = Order::with(['orderItems.review', 'user'])
                ->where('id', $id);

            if (!$user->hasPermissionTo('orders.read')) {
                $query->where('fk_user_id', $user->id);
            }

            $order = $query->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $this->orderPaymentSyncService->syncMidtransOrder($order);
            $order->refresh();

            if (
                $order->status === 'PENDING' &&
                $order->payment_status === 'PENDING' &&
                $order->payment_snap_token &&
                $order->payment_method !== 'xendit' &&
                !$this->isXenditInvoiceUrl($order->payment_snap_token)
            ) {
                try {
                    $isExpired = MidtransService::isTransactionExpired($order->order_number);

                    if ($isExpired) {
                        $order->refresh();

                        if ($order->status === 'CANCELLED' || $order->payment_status === 'FAILED') {
                        } else {
                            $order->update([
                                'payment_status' => 'FAILED',
                                'status' => 'CANCELLED',
                            ]);

                            $order->refresh();

                            if (!$order->relationLoaded('orderItems')) {
                                $order->load('orderItems');
                            }

                            $orderItems = $order->orderItems->map(function ($item) {
                                return [
                                    'variant_id' => $item->fk_variant_id,
                                    'qty' => $item->qty,
                                    'store_id' => $item->store_id,
                                ];
                            })->toArray();

                            if (!empty($orderItems)) {
                                $this->orderStockReductionService->releaseReservedStock($orderItems);
                            }

                            if ($order->fk_voucher_id) {
                                $voucher = Voucher::find($order->fk_voucher_id);
                                if ($voucher && $voucher->voucher_used > 0) {
                                    $voucher->decrement('voucher_used');
                                }
                            }

                            // ==================== EMAIL NOTIFICATION ====================
                            $this->sendOrderNotification(
                                $order,
                                new OrderExpiredOrCancelledNotification($order, 'expired')
                            );
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order by order number
     *
     * @param string $orderNumber
     * @return JsonResponse
     */
    public function showByOrderNumber(string $orderNumber): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::with(['orderItems.review', 'user'])
                ->where('order_number', $orderNumber)
                ->where('fk_user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $this->orderPaymentSyncService->syncMidtransOrder($order);
            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order payment status
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePaymentStatus(int $id, Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('fk_user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $validated = $request->validate([
                'payment_status' => 'required|in:PENDING,PAID,FAILED,CANCELLED,REFUNDED',
                'payment_method' => 'nullable|string|max:250',
                'payment_reference_code' => 'nullable|string|max:250',
            ]);

            $updateData = [
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'] ?? $order->payment_method,
                'payment_reference_code' => $validated['payment_reference_code'] ?? $order->payment_reference_code,
            ];

            if ($validated['payment_status'] === 'PAID' && $order->status === 'PENDING') {
                $updateData['status'] = 'PACKING';
            }

            $oldPaymentStatus = $order->payment_status;
            $order->update($updateData);

            if ($validated['payment_status'] === 'PAID' && $oldPaymentStatus !== 'PAID' && $order->fk_voucher_id) {
                $voucher = Voucher::find($order->fk_voucher_id);
                if ($voucher) {
                    $voucher->increment('voucher_used');
                }
            }

            if ($oldPaymentStatus === 'PAID' && $validated['payment_status'] !== 'PAID' && $order->fk_voucher_id) {
                $voucher = Voucher::find($order->fk_voucher_id);
                if ($voucher && $voucher->voucher_used > 0) {
                    $voucher->decrement('voucher_used');
                }
            }

            if ($validated['payment_status'] === 'PAID' && $oldPaymentStatus !== 'PAID') {
                // FIX: konversi stok reserved -> actual, sebelumnya method ini
                // tidak pernah memanggil convertReservedToActual sama sekali.
                if (!$order->relationLoaded('orderItems')) {
                    $order->load('orderItems');
                }

                $orderItems = $order->orderItems->map(function ($item) {
                    return [
                        'variant_id' => $item->fk_variant_id,
                        'qty' => $item->qty,
                        'store_id' => $item->store_id,
                    ];
                })->toArray();

                if (!empty($orderItems)) {
                    $this->orderStockReductionService->convertReservedToActual($orderItems);
                }

                try {
                    $pointService = app(PointService::class);
                    $pointService->addPointsFromOrder($user->id, $order->id, $order->total_amount);
                } catch (\Exception $e) {
                }

                // ==================== EMAIL NOTIFICATION ====================
                $this->sendOrderNotification($order, new OrderPaidNotification($order));
                // FIX: kirim juga notifikasi packing karena status langsung
                // diset PACKING di atas (updateStatus() tidak akan melihat transisinya).
                $this->sendOrderNotification($order, new OrderPackingNotification($order));
            }

            if (in_array($validated['payment_status'], ['FAILED', 'CANCELLED'], true) && $oldPaymentStatus !== $validated['payment_status']) {
                $this->sendOrderNotification(
                    $order,
                    new OrderExpiredOrCancelledNotification($order, 'cancelled')
                );
            }

            $order->load(['orderItems.review', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order payment status updated successfully',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status and resi number (Admin only)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $validated = $request->validate([
                'status' => 'required|in:PENDING,PACKING,DELIVERING,DELIVERED,COMPLETED,CANCELLED',
                'courier_resi_number' => 'nullable|string|max:250',
            ]);

            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            $updateData = [
                'status' => $newStatus,
            ];

            if (isset($validated['courier_resi_number'])) {
                $updateData['courier_resi_number'] = $validated['courier_resi_number'];
            }

            $order->update($updateData);

            if ($newStatus === 'CANCELLED' && $oldStatus !== 'CANCELLED') {
                if (!$order->relationLoaded('orderItems')) {
                    $order->load('orderItems');
                }

                $orderItems = $order->orderItems->map(function ($item) {
                    return [
                        'variant_id' => $item->fk_variant_id,
                        'qty' => $item->qty,
                        'store_id' => $item->store_id,
                    ];
                })->toArray();

                if (!empty($orderItems)) {
                    $this->orderStockReductionService->releaseReservedStock($orderItems);
                }

                if ($order->fk_voucher_id && $order->payment_status === 'PAID') {
                    $voucher = Voucher::find($order->fk_voucher_id);
                    if ($voucher && $voucher->voucher_used > 0) {
                        $voucher->decrement('voucher_used');
                    }
                }
            }

            // ==================== EMAIL NOTIFICATION ====================
            // Hanya kirim kalau status benar-benar berubah, sesuaikan tiap status
            // dengan notification class yang sudah dibuat.
            // CATATAN: transisi ke PACKING lewat endpoint ini jarang terpicu di
            // alur normal karena payment (webhook/confirmPayment/updatePaymentStatus)
            // sudah men-set status PACKING duluan saat pembayaran sukses.
            if ($newStatus !== $oldStatus) {
                switch ($newStatus) {
                    case 'PACKING':
                        $this->sendOrderNotification($order, new OrderPackingNotification($order));
                        break;

                    case 'DELIVERING':
                        $this->sendOrderNotification($order, new OrderShippedNotification($order));
                        break;

                    case 'DELIVERED':
                        $this->sendOrderNotification($order, new OrderDeliveredNotification($order));
                        break;

                    case 'COMPLETED':
                        $this->sendOrderNotification($order, new OrderCompletedNotification($order));
                        break;

                    case 'CANCELLED':
                        $this->sendOrderNotification(
                            $order,
                            new OrderExpiredOrCancelledNotification($order, 'cancelled')
                        );
                        break;
                }
            }

            $order->load(['orderItems.review', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order by user (for PENDING orders only)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('fk_user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->payment_status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pesanan yang belum dibayar yang dapat dibatalkan',
                ], 422);
            }

            if ($order->status === 'CANCELLED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah dibatalkan',
                ], 422);
            }

            $order->update([
                'payment_status' => 'FAILED',
                'status' => 'CANCELLED',
            ]);

            if (!$order->relationLoaded('orderItems')) {
                $order->load(['orderItems.review']);
            }

            $orderItems = $order->orderItems->map(function ($item) {
                return [
                    'variant_id' => $item->fk_variant_id,
                    'qty' => $item->qty,
                    'store_id' => $item->store_id,
                ];
            })->toArray();

            if (!empty($orderItems)) {
                $this->orderStockReductionService->releaseReservedStock($orderItems);
            }

            // ==================== EMAIL NOTIFICATION ====================
            $this->sendOrderNotification(
                $order,
                new OrderExpiredOrCancelledNotification($order, 'cancelled')
            );

            $order->load(['orderItems.review', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete order by user (for DELIVERED orders only)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('fk_user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->status === 'COMPLETED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah selesai',
                ], 422);
            }

            if ($order->status !== 'DELIVERED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pesanan yang sudah diantar yang dapat dibatalkan',
                ], 422);
            }

            $order->update([
                'status' => 'COMPLETED',
            ]);

            // FIX: releaseReservedStock() DIHAPUS. Order COMPLETED artinya barang
            // sudah terjual dan sudah dikonversi ke stok actual saat PAID —
            // TIDAK boleh direstore ke stok tersedia.

            $order->load(['orderItems.review', 'user']);

            // ==================== EMAIL NOTIFICATION ====================
            $this->sendOrderNotification($order, new OrderCompletedNotification($order));

            return response()->json([
                'success' => true,
                'message' => 'Pesanan selesai',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPayment(int $id, Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('fk_user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->payment_status !== 'PENDING') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already confirmed',
                    'data' => [
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                    ],
                ]);
            }

            if ($this->orderPaymentSyncService->syncMidtransOrder($order)) {
                $order->refresh();

                return response()->json([
                    'success' => true,
                    'message' => $order->payment_status === 'PAID'
                        ? 'Payment confirmed'
                        : 'Payment status synced',
                    'data' => [
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                    ],
                ]);
            }

            $transactionStatus = $request->input('transaction_status');
            $transactionId = $request->input('transaction_id');
            $paymentType = $request->input('payment_type');

            if ($order->payment_method === 'xendit') {
                if (!$order->payment_reference_code) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment not yet confirmed',
                        'data' => [
                            'payment_status' => $order->payment_status,
                            'status' => $order->status,
                        ],
                    ]);
                }

                $invoice = XenditService::getInvoice($order->payment_reference_code);

                if (!$invoice || !in_array($invoice['status'] ?? null, ['PAID', 'SETTLED'], true)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment not yet confirmed',
                        'data' => [
                            'payment_status' => $order->payment_status,
                            'status' => $order->status,
                        ],
                    ]);
                }

                $transactionStatus = 'PAID';
                $transactionId = $invoice['id'] ?? $order->payment_reference_code;
                $paymentType = $invoice['payment_method'] ?? $order->payment_type;
            }

            if (!$transactionStatus) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment not yet confirmed',
                    'data' => [
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                    ],
                ]);
            }

            $normalizedStatus = strtolower((string) $transactionStatus);
            if (!in_array($normalizedStatus, ['paid', 'capture', 'settlement'], true)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment not yet confirmed',
                    'data' => [
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                    ],
                ]);
            }

            $order->update([
                'payment_status' => 'PAID',
                'payment_reference_code' => $transactionId ?? $order->payment_reference_code,
                'payment_type' => $paymentType ?? $order->payment_type,
                'status' => 'PACKING',
            ]);

            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems');
            }

            $orderItems = $order->orderItems->map(function ($item) {
                return [
                    'variant_id' => $item->fk_variant_id,
                    'qty' => $item->qty,
                    'store_id' => $item->store_id,
                ];
            })->toArray();

            if (!empty($orderItems)) {
                $this->orderStockReductionService->convertReservedToActual($orderItems);
            }

            if ($order->fk_voucher_id) {
                $voucher = Voucher::find($order->fk_voucher_id);
                if ($voucher) {
                    $voucher->increment('voucher_used');
                }
            }

            try {
                $pointService = app(PointService::class);
                $pointService->addPointsFromOrder($order->fk_user_id, $order->id, $order->total_amount);
                $pointService->deductPoints(
                    userId: $order->fk_user_id,
                    points: $order->points_used,
                    orderId: $order->id,
                    description: "Digunakan untuk pembayaran order #{$order->order_number}"
                );
            } catch (\Exception $e) {
                Log::error('CONFIRM PAYMENT: Failed to add points', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // ==================== EMAIL NOTIFICATION ====================
            $this->sendOrderNotification($order, new OrderPaidNotification($order));
            // FIX: konsisten dengan webhook & updatePaymentStatus — kirim juga
            // notifikasi packing karena status langsung PACKING di sini.
            $this->sendOrderNotification($order, new OrderPackingNotification($order));

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed',
                'data' => [
                    'payment_status' => 'PAID',
                    'status' => 'PACKING',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function checkAndCancelExpiredOrders(int $userId): void
    {
        try {
            $cutoffTime = now()->subHours(48);

            $pendingOrders = Order::where('fk_user_id', $userId)
                ->where('payment_status', 'PENDING')
                ->where('status', '!=', 'CANCELLED')
                ->where(function ($query) {
                    $query->whereNull('payment_method')
                        ->orWhere('payment_method', '!=', 'xendit');
                })
                ->whereNotNull('payment_snap_token')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', $cutoffTime)
                ->limit(20)
                ->get();

            if ($pendingOrders->isEmpty()) {
                return;
            }

            $cancelledCount = 0;

            foreach ($pendingOrders as $order) {
                try {
                    $order->refresh();

                    if ($order->status === 'CANCELLED' || $order->payment_status === 'FAILED') {
                        continue;
                    }

                    if ($this->isXenditInvoiceUrl($order->payment_snap_token)) {
                        continue;
                    }

                    if ($this->orderPaymentSyncService->syncMidtransOrder($order)) {
                        continue;
                    }

                    $isExpired = MidtransService::isTransactionExpired($order->order_number);

                    if ($isExpired) {
                        $order->update([
                            'payment_status' => 'FAILED',
                            'status' => 'CANCELLED',
                        ]);

                        $order->refresh();

                        if (!$order->relationLoaded('orderItems')) {
                            $order->load('orderItems');
                        }

                        $orderItems = $order->orderItems->map(function ($item) {
                            return [
                                'variant_id' => $item->fk_variant_id,
                                'qty' => $item->qty,
                                'store_id' => $item->store_id,
                            ];
                        })->toArray();

                        if (!empty($orderItems)) {
                            $this->orderStockReductionService->releaseReservedStock($orderItems);
                        }

                        // ==================== EMAIL NOTIFICATION ====================
                        $this->sendOrderNotification(
                            $order,
                            new OrderExpiredOrCancelledNotification($order, 'expired')
                        );

                        $cancelledCount++;
                    }
                } catch (\Exception $e) {
                }
            }
        } catch (\Exception $e) {
        }
    }

    private function isXenditInvoiceUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false && str_contains($value, 'xendit.co');
    }

    /**
     * Kirim notification email untuk order, dibungkus try-catch supaya
     * kegagalan pengiriman email tidak mengganggu alur utama request.
     *
     * @param Order $order
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    private function sendOrderNotification(Order $order, $notification): void
    {
        if (!$order->contact_email) {
            return;
        }

        try {
            Notification::route('mail', $order->contact_email)
                ->notify($notification);
        } catch (\Exception $e) {
            Log::error('ORDER: Failed to send email notification', [
                'order_id' => $order->id,
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }
}