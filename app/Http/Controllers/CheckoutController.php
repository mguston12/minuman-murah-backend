<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest\CreateCheckoutRequest;
use App\Http\Resources\OrderResource\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Voucher;
use App\Notifications\Order\OrderCreatedNotification;
use App\Services\Cart\CartCalculationService;
use App\Services\Order\OrderCalculationService;
use App\Services\Order\OrderStockReductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected CartCalculationService $cartCalculationService;
    protected OrderCalculationService $orderCalculationService;
    protected OrderStockReductionService $orderStockReductionService;

    public function __construct(
        CartCalculationService $cartCalculationService,
        OrderCalculationService $orderCalculationService,
        OrderStockReductionService $orderStockReductionService
    ) {
        $this->cartCalculationService = $cartCalculationService;
        $this->orderCalculationService = $orderCalculationService;
        $this->orderStockReductionService = $orderStockReductionService;
    }

    /**
     * Create order from checkout
     *
     * @param CreateCheckoutRequest $request
     * @return JsonResponse
     */
    public function create(CreateCheckoutRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->input('data');

            $products = $data['products'] ?? [];
            $shipping = $data['shipping'] ?? [];
            $billing = $data['billing'] ?? [];
            $courier = $data['courier'] ?? [];
            $sameAsShipping = $billing['same_as_shipping'] ?? true;

            $voucherId = $data['voucher_id'] ?? null;
            $usePoints = $data['use_points'] ?? false;

            $user = auth('sanctum')->user();

            $cartResult = $this->cartCalculationService->calculateCart($products);

            if (!empty($cartResult['out_of_stock'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Some items are out of stock',
                    'data' => [
                        'out_of_stock' => $cartResult['out_of_stock'],
                    ],
                ], 422);
            }

            if (empty($cartResult['cart'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty.',
                ], 422);
            }

            $subTotal = collect($cartResult['cart'])
                ->sum(fn($i) => $i['sub_total']);

            $shippingCost = $courier['cost'] ?? 0;

            $voucherDiscount = 0;
            $voucher = null;

            if ($voucherId) {
                $voucher = Voucher::where('id', $voucherId)
                    ->whereNotIn('status', ['DRAFT', 'INACTIVE'])
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak ditemukan atau tidak aktif',
                    ], 422);
                }

                if ($voucher->discount_type === 'PERCENTAGE') {
                    $voucherDiscount = (int) floor(
                        $subTotal * ($voucher->discount_value / 100)
                    );
                } else {
                    $voucherDiscount = (int) $voucher->discount_value;
                }

                $voucherDiscount = max(0, min($voucherDiscount, $subTotal));

                $pendingOrder = Order::where('fk_user_id', $user->id)
                    ->where('fk_voucher_id', $voucherId)
                    ->where('payment_status', 'PENDING')
                    ->where('status', '!=', 'CANCELLED')
                    ->whereNull('deleted_at')
                    ->first();

                if ($pendingOrder) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher sedang digunakan pada transaksi yang belum diproses',
                        'error_code' => 'VOUCHER_PENDING_ORDER',
                    ], 422);
                }
            }

            $pointDiscount = 0;

            if ($usePoints) {

                $userPoint = $user->userPoint()
                    ->lockForUpdate()
                    ->first();

                $availablePoints = $userPoint?->points ?? 0;

                $baseAmount = max(0, $subTotal - $voucherDiscount);

                $maxPointAllowed = (int) floor($baseAmount * 0.10);

                $pointDiscount = min($availablePoints, $maxPointAllowed);
            }


            $orderCalculation = $this->orderCalculationService->calculateOrder(
                $products,
                $shippingCost,
                $voucherDiscount
            );

            $productProtectionPrice = collect($orderCalculation['cart'])
                ->sum(fn($item) => $item['product_protection_amount'] ?? 0);

            $totalAmount = max(
                0,
                $subTotal
                    + $productProtectionPrice
                    + $shippingCost
                    - $voucherDiscount
                    - $pointDiscount
            );

            $queueNumber = Order::getLatestQueueNumberForMonth() + 1;
            $orderNumber = Order::generateOrderNumber();
            $billingData = $sameAsShipping ? $shipping : $billing;

            $discountAmount = $voucherDiscount + $pointDiscount;

            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'queue_number' => $queueNumber,
                'order_number' => $orderNumber,

                'contact_email' => $shipping['email'] ?? null,
                'contact_phone' => $shipping['phone'] ?? null,

                // Shipping
                'shipping_country' => 'Indonesia',
                'shipping_first_name' => $shipping['first_name'],
                'shipping_last_name' => $shipping['last_name'] ?? null,
                'shipping_address' => $shipping['address'],
                'shipping_city' => $shipping['city'],
                'shipping_province' => $shipping['province'],
                'shipping_postal_code' => $shipping['postal_code'],

                // Billing
                'billing_country' => $billingData['country'] ?? 'Indonesia',
                'billing_first_name' => $billingData['first_name'],
                'billing_last_name' => $billingData['last_name'] ?? null,
                'billing_address' => $billingData['address'],
                'billing_city' => $billingData['city'],
                'billing_province' => $billingData['province'],
                'billing_postal_code' => $billingData['postal_code'],

                // Courier
                'courier_agent' => $courier['agent'] ?? null,
                'courier_agent_service' => $courier['service'] ?? null,
                'courier_cost' => $shippingCost,

                // Payment
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => 'PENDING',

                // Totals
                'subtotal' => $subTotal,
                'discount_amount' => $discountAmount,
                'points_used' => $pointDiscount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,

                // FK
                'fk_user_id' => $user->id,
                'fk_voucher_id' => $voucherId,

                'status' => 'PENDING',
            ]);

            foreach ($orderCalculation['cart'] as $cartItem) {
                OrderItem::create([
                    'fk_order_id' => $order->id,
                    'fk_product_id' => $cartItem['product_id'] ?? null,
                    'fk_variant_id' => $cartItem['variant_id'],
                    'store_id' => $cartItem['store']['id'] ?? null,
                    'product_name' => $cartItem['product_name'],
                    'product_image' => $cartItem['image'] ?? null,
                    'sku' => $cartItem['sku'],
                    'variant_description' => $cartItem['variant_description'] ?? null,
                    'qty' => $cartItem['qty'],
                    'actual_price' => $cartItem['actual_price'],
                    'discount_price' => $cartItem['discount_price'],
                    'purchase_price' => $cartItem['purchase_price'],
                    'product_protection_percent' => $cartItem['product_protection_percent'] ?? null,
                    'product_protection_amount' => $cartItem['product_protection_amount'] ?? 0,
                    'subtotal' => $cartItem['sub_total'],
                    'note' => $cartItem['note'] ?? null,
                ]);
            }

            $this->orderStockReductionService->reserveStock($orderCalculation['cart']);

            DB::commit();

            $order->load(['orderItems', 'user']);

            // ==================== EMAIL NOTIFICATION ====================
            // Dikirim setelah commit berhasil, di luar transaksi DB, supaya
            // kegagalan kirim email tidak sampai mem-rollback order yang sudah dibuat.
            if ($order->contact_email) {
                try {
                    Notification::route('mail', $order->contact_email)
                        ->notify(new OrderCreatedNotification($order));
                } catch (\Exception $e) {
                    Log::error('CHECKOUT: Failed to send OrderCreatedNotification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => new OrderResource($order),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Check if phone number is valid (flexible, payment-safe)
     *
     * @param string|null $phone
     * @return bool
     */
    private function isValidPhoneNumber(?string $phone): bool
    {
        if ($phone === null) {
            return false;
        }

        $phone = trim($phone);

        if ($phone === '') {
            return false;
        }

        $cleaned = preg_replace('/[\s\-()]/', '', $phone);

        return preg_match('/^\+?[0-9]{2,15}$/', $cleaned) === 1;
    }
}