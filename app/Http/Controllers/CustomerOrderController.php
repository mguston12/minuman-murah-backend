namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderPaymentSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    protected $orderPaymentSyncService;

    public function __construct(OrderPaymentSyncService $orderPaymentSyncService)
    {
        $this->orderPaymentSyncService = $orderPaymentSyncService;
    }

    /**
     * Get authenticated customer's orders
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

            // Opsional: Jika fungsi checkAndCancelExpiredOrders ada di model/service Anda
            // $this->checkAndCancelExpiredOrders($user->id);

            // Selalu paksa filter berdasarkan fk_user_id milik user yang sedang login
            $query = Order::with(['orderItems.review', 'user'])
                ->where('fk_user_id', $user->id);

            // Filter berdasarkan status jika ada
            if ($status) {
                $query->where('status', $status);
            }

            // Cari berdasarkan nomor pesanan
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
                'message' => 'Customer orders retrieved successfully',
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
}s