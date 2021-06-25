<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Payments\PayableModel;
use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Jobs\Shop\UpdateOrderJob;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MollieController extends Controller
{
    /**
     * Handle Mollie webhooks for the activities.
     */
    public function activity(Request $request): Response
    {
        $this->handleMollieResponse($request, Enrollment::class);

        return ResponseFacade::noContent(Response::HTTP_OK);
    }

    /**
     * Handle Mollie webhooks for the shop.
     */
    public function shop(Request $request): Response
    {
        $this->handleMollieResponse($request, Order::class);

        return ResponseFacade::noContent(Response::HTTP_OK);
    }

    /**
     * Process the Mollie reply for the given model type.
     *
     * @throws BadRequestHttpException if $request is invalid
     * @param Model|PayableModel $model
     */
    private function handleMollieResponse(Request $request, $model): void
    {
        $id = $request->post('id');
        if (! $id || Str::len($id) > 40) {
            throw new BadRequestHttpException();
        }

        $subject = $model::query()->where('payment_id', $id)->first();
        if (! $subject) {
            return;
        }

        UpdateOrderJob::dispatchAfterResponse($subject);
    }
}
